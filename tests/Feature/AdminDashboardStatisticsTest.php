<?php

namespace Tests\Feature;

use App\Models\MedicalRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistics_cards_are_scoped_to_the_selected_university(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createRegistration('IUG', [
            'gpa' => 91,
            'is_father_martyr' => 'yes',
            'has_sibling_student' => 'yes',
        ]);
        $this->createRegistration('IUG', [
            'gpa' => 72,
            'has_disability' => 'yes',
        ]);
        $this->createRegistration('AUG', [
            'gpa' => 95,
            'is_father_martyr' => 'yes',
            'has_disability' => 'yes',
            'has_sibling_student' => 'yes',
        ]);

        $this->get(route('admin.dashboard', ['university' => 'IUG']))
            ->assertOk()
            ->assertViewHas('totalStudents', 2)
            ->assertViewHas('highGpaCount', 1)
            ->assertViewHas('fatherMartyrsCount', 1)
            ->assertViewHas('disabilitiesCount', 1)
            ->assertViewHas('siblingsCount', 1);
    }

    private function createRegistration(string $university, array $overrides = []): MedicalRegistration
    {
        static $sequence = 0;
        $sequence++;

        return MedicalRegistration::create(array_merge([
            'full_name' => 'Student '.$sequence,
            'national_id' => str_pad((string) $sequence, 9, '0', STR_PAD_LEFT),
            'mobile_number' => '059000000'.$sequence,
            'date_of_birth' => '2000-01-01',
            'university_id' => $university,
            'academic_level' => 'level_1',
            'gpa' => 70,
            'housing_type' => 'house',
            'personal_photo_path' => 'files/photo.jpg',
            'national_id_image_path' => 'files/id.jpg',
            'enrollment_cert_path' => 'files/cert.pdf',
            'is_father_martyr' => 'no',
            'has_disability' => 'no',
            'has_sibling_student' => 'no',
            'reference_number' => 'REF-'.$sequence,
        ], $overrides));
    }
}
