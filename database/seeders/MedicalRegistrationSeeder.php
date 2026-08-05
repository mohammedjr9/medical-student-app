<?php

namespace Database\Seeders;

use App\Models\MedicalRegistration;
use Illuminate\Database\Seeder;

class MedicalRegistrationSeeder extends Seeder
{
    public function run(): void
    {
        MedicalRegistration::truncate();

        $students = [
            [
                'full_name' => 'أحمد محمد محمود الأحمد',
                'national_id' => '402981745',
                'mobile_number' => '0599123456',
                'date_of_birth' => '2002-04-15',
                'university_id' => 'IUG',
                'academic_level' => 'level_4',
                'gpa' => 91.50,
                'housing_type' => 'house',
                'personal_photo_path' => 'medical_student_docs/photos/demo1.jpg',
                'national_id_image_path' => 'medical_student_docs/ids/demo1.pdf',
                'enrollment_cert_path' => 'medical_student_docs/enrollments/demo1.pdf',
                'is_father_martyr' => 'yes',
                'father_death_cert_path' => 'medical_student_docs/special/death1.pdf',
                'has_disability' => 'no',
                'medical_report_path' => null,
                'has_sibling_student' => 'yes',
                'sibling_enrollment_cert_path' => 'medical_student_docs/special/sibling1.pdf',
                'reference_number' => 'MED-2026-88192A',
            ],
            [
                'full_name' => 'سارة يوسف إبراهيم مصطفى',
                'national_id' => '405119283',
                'mobile_number' => '0568987654',
                'date_of_birth' => '2003-09-22',
                'university_id' => 'AUG',
                'academic_level' => 'level_3',
                'gpa' => 87.20,
                'housing_type' => 'apartment',
                'personal_photo_path' => 'medical_student_docs/photos/demo2.jpg',
                'national_id_image_path' => 'medical_student_docs/ids/demo2.pdf',
                'enrollment_cert_path' => 'medical_student_docs/enrollments/demo2.pdf',
                'is_father_martyr' => 'no',
                'father_death_cert_path' => null,
                'has_disability' => 'yes',
                'medical_report_path' => 'medical_student_docs/special/medical2.pdf',
                'has_sibling_student' => 'no',
                'sibling_enrollment_cert_path' => null,
                'reference_number' => 'MED-2026-74910B',
            ],
            [
                'full_name' => 'خالد عبد الرحمن حسن القاسم',
                'national_id' => '401772639',
                'mobile_number' => '0597334455',
                'date_of_birth' => '2001-11-05',
                'university_id' => 'ISRAA',
                'academic_level' => 'level_5',
                'gpa' => 84.60,
                'housing_type' => 'house',
                'personal_photo_path' => 'medical_student_docs/photos/demo3.jpg',
                'national_id_image_path' => 'medical_student_docs/ids/demo3.pdf',
                'enrollment_cert_path' => 'medical_student_docs/enrollments/demo3.pdf',
                'is_father_martyr' => 'no',
                'father_death_cert_path' => null,
                'has_disability' => 'no',
                'medical_report_path' => null,
                'has_sibling_student' => 'yes',
                'sibling_enrollment_cert_path' => 'medical_student_docs/special/sibling3.pdf',
                'reference_number' => 'MED-2026-61029C',
            ],
            [
                'full_name' => 'مريم خليل إسماعيل النجار',
                'national_id' => '409228104',
                'mobile_number' => '0592441122',
                'date_of_birth' => '2004-01-30',
                'university_id' => 'UPAL',
                'academic_level' => 'level_2',
                'gpa' => 94.80,
                'housing_type' => 'tent',
                'personal_photo_path' => 'medical_student_docs/photos/demo4.jpg',
                'national_id_image_path' => 'medical_student_docs/ids/demo4.pdf',
                'enrollment_cert_path' => 'medical_student_docs/enrollments/demo4.pdf',
                'is_father_martyr' => 'yes',
                'father_death_cert_path' => 'medical_student_docs/special/death4.pdf',
                'has_disability' => 'no',
                'medical_report_path' => null,
                'has_sibling_student' => 'no',
                'sibling_enrollment_cert_path' => null,
                'reference_number' => 'MED-2026-33918D',
            ],
            [
                'full_name' => 'عمر طارق زياد الشوا',
                'national_id' => '403889120',
                'mobile_number' => '0569778899',
                'date_of_birth' => '2000-06-18',
                'university_id' => 'IUG',
                'academic_level' => 'internship',
                'gpa' => 89.00,
                'housing_type' => 'relatives',
                'personal_photo_path' => 'medical_student_docs/photos/demo5.jpg',
                'national_id_image_path' => 'medical_student_docs/ids/demo5.pdf',
                'enrollment_cert_path' => 'medical_student_docs/enrollments/demo5.pdf',
                'is_father_martyr' => 'no',
                'father_death_cert_path' => null,
                'has_disability' => 'no',
                'medical_report_path' => null,
                'has_sibling_student' => 'no',
                'sibling_enrollment_cert_path' => null,
                'reference_number' => 'MED-2026-90514E',
            ],
        ];

        foreach ($students as $student) {
            MedicalRegistration::create($student);
        }
    }
}
