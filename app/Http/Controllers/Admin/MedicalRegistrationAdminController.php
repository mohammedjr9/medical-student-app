<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalRegistration;
use App\Support\StudentExcelExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicalRegistrationAdminController extends Controller
{
    public function index(Request $request)
    {
        $allowedUniversities = ['IUG', 'AUG', 'ISRAA', 'UPAL'];
        $query = $this->applyFilters(MedicalRegistration::query(), $request, $allowedUniversities);
        $students = $this->applyOrdering($query, $request->input('sort'))->paginate(15)->withQueryString();

        // High Level Analytics & Counters. When a university is selected, these
        // cards describe that university instead of continuing to show global totals.
        $analyticsQuery = MedicalRegistration::query();
        if ($request->filled('university') && in_array($request->input('university'), $allowedUniversities, true)) {
            $analyticsQuery->where('university_id', $request->input('university'));
        }

        $totalStudents = (clone $analyticsQuery)->count();
        $fatherMartyrsCount = (clone $analyticsQuery)->where('is_father_martyr', 'yes')->count();
        $disabilitiesCount = (clone $analyticsQuery)->where('has_disability', 'yes')->count();
        $siblingsCount = (clone $analyticsQuery)->where('has_sibling_student', 'yes')->count();
        $highGpaCount = (clone $analyticsQuery)->where('gpa', '>=', 85)->count();
        $universityCounts = MedicalRegistration::query()
            ->whereIn('university_id', $allowedUniversities)
            ->selectRaw('university_id, COUNT(*) as total')
            ->groupBy('university_id')
            ->pluck('total', 'university_id');

        $universityLogos = [
            'IUG' => 'images/universities/iug.png',
            'AUG' => 'images/universities/aug.svg',
            'ISRAA' => 'images/universities/israa.png',
            'UPAL' => 'images/universities/upal.svg',
        ];

        // Reference dictionaries
        $universities = [
            'IUG' => 'الجامعة الإسلامية بغزة',
            'AUG' => 'جامعة الأزهر بغزة',
            'ISRAA' => 'جامعة الإسراء',
            'UPAL' => 'جامعة فلسطين',
            'OTHER' => 'جامعة أخرى'
        ];

        $academicLevels = [
            'level_1' => 'السنة الأولى',
            'level_2' => 'السنة الثانية',
            'level_3' => 'السنة الثالثة',
            'level_4' => 'السنة الرابعة',
            'level_5' => 'السنة الخامسة',
            'level_6' => 'السنة السادسة',
            'internship' => 'سنة الامتياز'
        ];

        // Counts make the academic-level filter easier to scan. When a university
        // is active, show the distribution for that university only.
        $academicLevelCountsQuery = MedicalRegistration::query();
        if ($request->filled('university') && in_array($request->input('university'), $allowedUniversities, true)) {
            $academicLevelCountsQuery->where('university_id', $request->input('university'));
        }
        $academicLevelCounts = $academicLevelCountsQuery
            ->selectRaw('academic_level, COUNT(*) as total')
            ->groupBy('academic_level')
            ->pluck('total', 'academic_level');

        $housingTypes = [
            'house' => 'منزل',
            'tent' => 'خيمة',
            'apartment' => 'شقة',
            'relatives' => 'منزل أقارب',
            'shelter' => 'مركز إيواء',
            'other' => 'أخرى'
        ];

        return view('admin.dashboard', compact(
            'students',
            'totalStudents',
            'fatherMartyrsCount',
            'disabilitiesCount',
            'siblingsCount',
            'highGpaCount',
            'universityCounts',
            'universityLogos',
            'universities',
            'academicLevels',
            'academicLevelCounts',
            'housingTypes'
        ));
    }

    public function export(Request $request, StudentExcelExporter $exporter)
    {
        $validated = $request->validate([
            'university' => ['nullable', 'in:IUG,AUG,ISRAA,UPAL'],
            'academic_level' => ['nullable', 'in:level_1,level_2,level_3,level_4,level_5,level_6,internship'],
            'excluded_academic_levels' => ['nullable', 'array'],
            'excluded_academic_levels.*' => ['in:level_1,level_2,level_3,level_4,level_5,level_6,internship'],
            'housing_type' => ['nullable', 'in:house,tent,apartment,relatives,shelter,other'],
            'gpa_min' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'gpa_max' => ['nullable', 'numeric', 'min:0', 'max:100', 'gte:gpa_min'],
            'martyr' => ['nullable', 'in:yes,no'],
            'disability' => ['nullable', 'in:yes,no'],
            'sibling' => ['nullable', 'in:yes,no'],
            'sort' => ['nullable', 'in:priority,gpa_desc,gpa_asc,latest'],
            'export_limit' => ['nullable', 'integer', 'min:1', 'max:5000'],
        ]);

        $query = $this->applyFilters(MedicalRegistration::query(), $request, ['IUG', 'AUG', 'ISRAA', 'UPAL']);
        $query = $this->applyOrdering($query, $validated['sort'] ?? null);
        if (! empty($validated['export_limit'])) {
            $query->limit((int) $validated['export_limit']);
        }

        $students = $query->get();
        if ($students->isEmpty()) {
            return back()->withErrors(['export' => 'لا توجد نتائج مطابقة لتصديرها.']);
        }

        return $exporter->download($students);
    }

    private function applyFilters(Builder $query, Request $request, array $allowedUniversities): Builder
    {
        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('university') && in_array($request->input('university'), $allowedUniversities, true)) {
            $query->where('university_id', $request->input('university'));
        }
        foreach (['academic_level', 'housing_type'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }
        if (! $request->filled('academic_level') && $request->filled('excluded_academic_levels')) {
            $allowedLevels = ['level_1', 'level_2', 'level_3', 'level_4', 'level_5', 'level_6', 'internship'];
            $excludedLevels = array_values(array_intersect(
                (array) $request->input('excluded_academic_levels', []),
                $allowedLevels
            ));
            if ($excludedLevels !== []) {
                $query->whereNotIn('academic_level', $excludedLevels);
            }
        }
        if ($request->filled('gpa_min') && is_numeric($request->input('gpa_min'))) {
            $query->where('gpa', '>=', (float) $request->input('gpa_min'));
        }
        if ($request->filled('gpa_max') && is_numeric($request->input('gpa_max'))) {
            $query->where('gpa', '<=', (float) $request->input('gpa_max'));
        }

        foreach (['martyr' => 'is_father_martyr', 'disability' => 'has_disability', 'sibling' => 'has_sibling_student'] as $input => $column) {
            if (in_array($request->input($input), ['yes', 'no'], true)) {
                $query->where($column, $request->input($input));
            }
        }

        // Keep old dashboard links/bookmarks working.
        $legacyConditions = [
            'father_martyr' => 'is_father_martyr',
            'disability' => 'has_disability',
            'sibling' => 'has_sibling_student',
        ];
        if (isset($legacyConditions[$request->input('special_condition')])) {
            $query->where($legacyConditions[$request->input('special_condition')], 'yes');
        }

        return $query;
    }

    private function applyOrdering(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'priority' => $query
                ->orderByRaw("CASE is_father_martyr WHEN 'yes' THEN 0 ELSE 1 END")
                ->orderByRaw("CASE has_sibling_student WHEN 'yes' THEN 0 ELSE 1 END")
                ->orderByRaw("CASE has_disability WHEN 'yes' THEN 0 ELSE 1 END")
                ->orderByDesc('gpa')->orderBy('id'),
            'gpa_desc' => $query->orderByDesc('gpa')->orderBy('id'),
            'gpa_asc' => $query->orderBy('gpa')->orderBy('id'),
            default => $query->orderByDesc('id'),
        };
    }

    public function show($id)
    {
        $student = MedicalRegistration::findOrFail($id);

        $universities = [
            'IUG' => 'الجامعة الإسلامية بغزة',
            'AUG' => 'جامعة الأزهر بغزة',
            'ISRAA' => 'جامعة الإسراء',
            'UPAL' => 'جامعة فلسطين',
            'OTHER' => 'جامعة أخرى'
        ];

        $academicLevels = [
            'level_1' => 'السنة الأولى',
            'level_2' => 'السنة الثانية',
            'level_3' => 'السنة الثالثة',
            'level_4' => 'السنة الرابعة',
            'level_5' => 'السنة الخامسة',
            'level_6' => 'السنة السادسة',
            'internship' => 'سنة الامتياز'
        ];

        $housingTypes = [
            'house' => 'منزل',
            'tent' => 'خيمة',
            'apartment' => 'شقة',
            'relatives' => 'منزل أقارب',
            'shelter' => 'مركز إيواء',
            'other' => 'أخرى'
        ];

        return view('admin.show', compact('student', 'universities', 'academicLevels', 'housingTypes'));
    }

    public function editFiles($id)
    {
        return view('admin.edit-files', ['student' => MedicalRegistration::findOrFail($id)]);
    }

    public function updateFiles(Request $request, $id)
    {
        $student = MedicalRegistration::findOrFail($id);
        $request->validate([
            'personal_photo' => ['nullable', 'image', 'max:5120'],
            'national_id_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'enrollment_cert' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'father_death_cert' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'medical_report' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'sibling_enrollment_cert' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $files = [
            'personal_photo' => ['personal_photo_path', 'medical_student_docs/photos'],
            'national_id_image' => ['national_id_image_path', 'medical_student_docs/ids'],
            'enrollment_cert' => ['enrollment_cert_path', 'medical_student_docs/enrollments'],
            'father_death_cert' => ['father_death_cert_path', 'medical_student_docs/special_conditions'],
            'medical_report' => ['medical_report_path', 'medical_student_docs/special_conditions'],
            'sibling_enrollment_cert' => ['sibling_enrollment_cert_path', 'medical_student_docs/special_conditions'],
        ];
        $updates = [];
        $oldPaths = [];

        foreach ($files as $input => [$column, $directory]) {
            if ($request->hasFile($input)) {
                $updates[$column] = $request->file($input)->store($directory, 'public');
                if ($student->{$column}) {
                    $oldPaths[] = $student->{$column};
                }
            }
        }

        if ($updates === []) {
            return back()->withErrors(['files' => 'اختر ملفًا واحدًا على الأقل لاستبداله.']);
        }

        $student->update($updates);
        foreach ($oldPaths as $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('admin.students.show', $student->id)
            ->with('success', 'تم تحديث ملفات الطالب بنجاح.');
    }

    public function destroy($id)
    {
        $student = MedicalRegistration::findOrFail($id);
        $student->delete();

        return redirect()->back()->with('success', 'تم حذف طلب تسجيل الطالب بنجاح.');
    }
}
