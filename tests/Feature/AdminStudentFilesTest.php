<?php

namespace Tests\Feature;

use App\Models\MedicalRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminStudentFilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_replace_one_file_without_changing_the_others(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $student = MedicalRegistration::create([
            'full_name' => 'Test Student', 'national_id' => '123456789',
            'mobile_number' => '0590000000', 'date_of_birth' => '2000-01-01',
            'university_id' => 'IUG', 'academic_level' => 'level_6', 'gpa' => 80,
            'governorate' => 'gaza', 'housing_type' => 'house',
            'personal_photo_path' => 'medical_student_docs/photos/old.jpg',
            'national_id_image_path' => 'medical_student_docs/ids/keep.pdf',
            'enrollment_cert_path' => 'medical_student_docs/enrollments/keep.pdf',
            'is_father_martyr' => 'no', 'has_disability' => 'no',
            'has_sibling_student' => 'no', 'reference_number' => 'REF-FILES',
        ]);
        Storage::disk('public')->put($student->personal_photo_path, 'old');

        $this->put(route('admin.students.files.update', $student), [
            'personal_photo' => UploadedFile::fake()->image('new.jpg'),
        ])->assertRedirect(route('admin.students.show', $student));

        $student->refresh();
        $this->assertNotSame('medical_student_docs/photos/old.jpg', $student->personal_photo_path);
        $this->assertSame('medical_student_docs/ids/keep.pdf', $student->national_id_image_path);
        Storage::disk('public')->assertMissing('medical_student_docs/photos/old.jpg');
        Storage::disk('public')->assertExists($student->personal_photo_path);
    }
}
