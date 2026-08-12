<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_islamic_registration_is_closed(): void
    {
        $this->get('/islamic-university')
            ->assertOk()
            ->assertSee('انتهت مدة التسجيل')
            ->assertDontSee('name="university_id"', false)
            ->assertSee('images/universities/iug.png', false);

        $this->post('/medical-registration', ['university_id' => 'IUG'])
            ->assertForbidden()
            ->assertSee('انتهت مدة التسجيل');
    }

    public function test_al_azhar_registration_is_closed(): void
    {
        $this->get('/al-azhar-university')
            ->assertOk()
            ->assertSee('انتهت مدة التسجيل')
            ->assertDontSee('name="university_id"', false)
            ->assertSee('images/universities/aug.svg', false);

        $this->post('/medical-registration', ['university_id' => 'AUG'])
            ->assertForbidden()
            ->assertSee('انتهت مدة التسجيل');
    }

    public function test_palestine_university_registration_is_closed(): void
    {
        $this->get('/palestine-university')
            ->assertOk()
            ->assertDontSee('name="university_id"', false)
            ->assertSee('images/universities/upal.svg', false);

        $this->post('/medical-registration', ['university_id' => 'UPAL'])
            ->assertForbidden()
            ->assertSee('images/universities/upal.svg', false);
    }

    public function test_israa_registration_is_closed(): void
    {
        $this->get('/israa-university')
            ->assertOk()
            ->assertDontSee('name="university_id"', false)
            ->assertSee('images/universities/israa.png', false);

        $this->post('/medical-registration', ['university_id' => 'ISRAA'])
            ->assertForbidden()
            ->assertSee('images/universities/israa.png', false);
    }
}
