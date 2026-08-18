<?php

namespace Tests\Feature;

use App\Models\MedicalRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class AdminStudentExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_filters_by_gpa_and_individual_conditions(): void
    {
        $this->actingAs(User::factory()->create());
        $this->student(['full_name' => 'مطابق', 'gpa' => 91, 'is_father_martyr' => 'yes']);
        $this->student(['full_name' => 'غير مطابق', 'gpa' => 79, 'is_father_martyr' => 'no', 'national_id' => '222222222', 'reference_number' => 'REF-2']);

        $this->get(route('admin.dashboard', ['gpa_min' => 85, 'martyr' => 'yes']))
            ->assertOk()
            ->assertSee('مطابق')
            ->assertDontSee('غير مطابق');
    }

    public function test_admin_can_download_filtered_students_as_a_real_xlsx_file(): void
    {
        $this->actingAs(User::factory()->create());
        $this->student(['full_name' => 'طالب للتصدير', 'gpa' => 92, 'university_id' => 'IUG']);
        $this->student(['full_name' => 'طالب مستبعد', 'gpa' => 70, 'university_id' => 'AUG', 'national_id' => '333333333', 'reference_number' => 'REF-3']);

        $response = $this->get(route('admin.students.export', [
            'university' => 'IUG',
            'gpa_min' => 85,
            'sort' => 'priority',
        ]));

        $response->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        $this->assertStringContainsString('طالب للتصدير', $sheet);
        $this->assertStringNotContainsString('طالب مستبعد', $sheet);
    }

    private function student(array $overrides = []): MedicalRegistration
    {
        return MedicalRegistration::create(array_merge([
            'full_name' => 'طالب', 'national_id' => '111111111', 'mobile_number' => '0590000000',
            'date_of_birth' => '2000-01-01', 'university_id' => 'IUG', 'academic_level' => 'level_3',
            'gpa' => 80, 'governorate' => 'gaza', 'housing_type' => 'house',
            'is_father_martyr' => 'no', 'has_disability' => 'no', 'has_sibling_student' => 'no',
            'reference_number' => 'REF-1',
        ], $overrides));
    }
}
