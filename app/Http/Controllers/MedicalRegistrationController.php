<?php

namespace App\Http\Controllers;

use App\Models\MedicalRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MedicalRegistrationController extends Controller
{
    public function create(string $university = 'islamic-university')
    {
        // Database Constants (Key => Value)
        $universities = [
            'IUG' => 'الجامعة الإسلامية بغزة',
            'AUG' => 'جامعة الأزهر بغزة',
            'ISRAA' => 'جامعة الإسراء',
            'UPAL' => 'جامعة فلسطين',
            'OTHER' => 'جامعة أخرى'
        ];

        $universityPages = [
            'islamic-university' => [
                'key' => 'IUG',
                'name' => 'الجامعة الإسلامية بغزة',
                'logo' => 'images/universities/iug.png',
            ],
            'al-azhar-university' => [
                'key' => 'AUG',
                'name' => 'جامعة الأزهر بغزة',
                'logo' => 'images/universities/aug.svg',
            ],
            'israa-university' => [
                'key' => 'ISRAA',
                'name' => 'جامعة الإسراء',
                'logo' => 'images/universities/israa.png',
            ],
            'palestine-university' => [
                'key' => 'UPAL',
                'name' => 'جامعة فلسطين',
                'logo' => 'images/universities/upal.svg',
            ],
        ];

        abort_unless(isset($universityPages[$university]), 404);
        $selectedUniversity = $universityPages[$university];

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

        $governorates = [
            'north_gaza' => 'شمال غزة',
            'gaza' => 'غزة',
            'deir_al_balah' => 'الوسطى',
            'khan_yunis' => 'خان يونس',
            'rafah' => 'رفح',
        ];

        return view('medical-registration', compact('universities', 'academicLevels', 'housingTypes', 'governorates', 'selectedUniversity'));
    }

    public function store(Request $request)
    {
        $startedAt = microtime(true);
        $submissionId = (string) Str::uuid();
        $phase = 'request_received';

        Log::info('Medical registration submission received.', [
            'submission_id' => $submissionId,
            'content_length' => $request->server('CONTENT_LENGTH'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'file_sizes' => collect($request->allFiles())->map(
                fn ($file) => is_array($file)
                    ? collect($file)->map(fn ($item) => $item?->getSize())->all()
                    : $file?->getSize()
            )->all(),
        ]);

        try {
        $phase = 'validation';
        $validated = $request->validate([
            // Section 1: Personal Info
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|digits:9',
            'mobile_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date',

            // Section 2: Academic Info
            'university_id' => 'required|string',
            'academic_level' => 'required|string',
            'gpa' => 'required|numeric|between:0,100',

            // Section 3: Housing Info
            'governorate' => 'required|in:north_gaza,gaza,deir_al_balah,khan_yunis,rafah',
            'housing_type' => 'required|in:house,tent,apartment,relatives,shelter,other',

            // Section 4: Required Uploads
            'personal_photo' => 'required|image|max:5120',
            'national_id_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'enrollment_cert' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',

            // Section 5: Conditionals
            'is_father_martyr' => 'required|in:yes,no',
            'father_death_cert' => 'required_if:is_father_martyr,yes|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'has_disability' => 'required|in:yes,no',
            'medical_report' => 'required_if:has_disability,yes|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'has_sibling_student' => 'required|in:yes,no',
            'sibling_name' => 'required_if:has_sibling_student,yes|nullable|string|max:255',
            'sibling_university' => 'required_if:has_sibling_student,yes|nullable|string|max:255',
            'sibling_enrollment_cert' => 'required_if:has_sibling_student,yes|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        Log::info('Medical registration validation passed.', [
            'submission_id' => $submissionId,
            'elapsed_ms' => round((microtime(true) - $startedAt) * 1000),
        ]);

        // File Storage Logic
        $phase = 'store_personal_photo';
        $photoPath = $request->file('personal_photo')->store('medical_student_docs/photos', 'public');
        $phase = 'store_national_id';
        $nationalIdPath = $request->file('national_id_image')->store('medical_student_docs/ids', 'public');
        $phase = 'store_enrollment_certificate';
        $enrollmentCertPath = $request->file('enrollment_cert')->store('medical_student_docs/enrollments', 'public');

        $phase = 'store_optional_files';
        $fatherDeathCertPath = $request->hasFile('father_death_cert') 
            ? $request->file('father_death_cert')->store('medical_student_docs/special_conditions', 'public') 
            : null;

        $medicalReportPath = $request->hasFile('medical_report') 
            ? $request->file('medical_report')->store('medical_student_docs/special_conditions', 'public') 
            : null;

        $siblingEnrollmentCertPath = $request->hasFile('sibling_enrollment_cert') 
            ? $request->file('sibling_enrollment_cert')->store('medical_student_docs/special_conditions', 'public') 
            : null;

        $refNumber = 'MED-' . date('Y') . '-' . strtoupper(Str::random(6));

        // Save to Database
        $phase = 'database_insert';
        MedicalRegistration::create([
            'full_name' => $validated['full_name'],
            'national_id' => $validated['national_id'],
            'mobile_number' => $validated['mobile_number'],
            'date_of_birth' => $validated['date_of_birth'],
            'university_id' => $validated['university_id'],
            'academic_level' => $validated['academic_level'],
            'gpa' => $validated['gpa'],
            'governorate' => $validated['governorate'],
            'housing_type' => $validated['housing_type'],
            'personal_photo_path' => $photoPath,
            'national_id_image_path' => $nationalIdPath,
            'enrollment_cert_path' => $enrollmentCertPath,
            'is_father_martyr' => $validated['is_father_martyr'],
            'father_death_cert_path' => $fatherDeathCertPath,
            'has_disability' => $validated['has_disability'],
            'medical_report_path' => $medicalReportPath,
            'has_sibling_student' => $validated['has_sibling_student'],
            'sibling_name' => $validated['sibling_name'] ?? null,
            'sibling_university' => $validated['sibling_university'] ?? null,
            'sibling_enrollment_cert_path' => $siblingEnrollmentCertPath,
            'reference_number' => $refNumber,
        ]);

        Log::info('Medical registration submission completed.', [
            'submission_id' => $submissionId,
            'reference_number' => $refNumber,
            'elapsed_ms' => round((microtime(true) - $startedAt) * 1000),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم تقديم الطلب بنجاح.',
                'reference_number' => $refNumber,
            ], 201);
        }

        return redirect()->back()->with('success', 'تم تقديم طلب تسجيل الطالب بنجاح برقم مرجعي: ' . $refNumber);
        } catch (Throwable $exception) {
            Log::error('Medical registration submission failed.', [
                'submission_id' => $submissionId,
                'phase' => $phase,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'elapsed_ms' => round((microtime(true) - $startedAt) * 1000),
            ]);

            throw $exception;
        }
    }
}
