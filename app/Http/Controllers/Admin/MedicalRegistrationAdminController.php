<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalRegistration;
use Illuminate\Http\Request;

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

        // High Level Analytics & Counters
        $totalStudents = MedicalRegistration::count();
        $fatherMartyrsCount = MedicalRegistration::where('is_father_martyr', 'yes')->count();
        $disabilitiesCount = MedicalRegistration::where('has_disability', 'yes')->count();
        $siblingsCount = MedicalRegistration::where('has_sibling_student', 'yes')->count();
        $highGpaCount = MedicalRegistration::where('gpa', '>=', 85)->count();
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

    public function destroy($id)
    {
        $student = MedicalRegistration::findOrFail($id);
        $student->delete();

        return redirect()->back()->with('success', 'تم حذف طلب تسجيل الطالب بنجاح.');
    }
}
