<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicalRegistrationAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicalRegistration::query();
        $allowedUniversities = ['IUG', 'AUG', 'ISRAA', 'UPAL'];

        // Search Keyword
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        // Filter by University
        if ($request->filled('university') && in_array($request->input('university'), $allowedUniversities, true)) {
            $query->where('university_id', $request->input('university'));
        }

        // Filter by Academic Level
        if ($request->filled('academic_level')) {
            $query->where('academic_level', $request->input('academic_level'));
        }

        // Filter by Housing Type
        if ($request->filled('housing_type')) {
            $query->where('housing_type', $request->input('housing_type'));
        }

        // Filter by Special Condition
        if ($request->filled('special_condition')) {
            $cond = $request->input('special_condition');
            if ($cond === 'father_martyr') {
                $query->where('is_father_martyr', 'yes');
            } elseif ($cond === 'disability') {
                $query->where('has_disability', 'yes');
            } elseif ($cond === 'sibling') {
                $query->where('has_sibling_student', 'yes');
            }
        }

        $students = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

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
            'housingTypes'
        ));
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
