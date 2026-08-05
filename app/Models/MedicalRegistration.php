<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'national_id',
        'mobile_number',
        'date_of_birth',
        'university_id',
        'academic_level',
        'gpa',
        'governorate',
        'housing_type',
        'personal_photo_path',
        'national_id_image_path',
        'enrollment_cert_path',
        'is_father_martyr',
        'father_death_cert_path',
        'has_disability',
        'medical_report_path',
        'has_sibling_student',
        'sibling_name',
        'sibling_university',
        'sibling_enrollment_cert_path',
        'reference_number',
    ];
}
