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

    public function test_each_university_has_its_own_registration_page(): void
    {
        $pages = [
            '/islamic-university' => ['IUG', 'images/universities/iug.png'],
            '/israa-university' => ['ISRAA', 'images/universities/israa.png'],
            '/palestine-university' => ['UPAL', 'images/universities/upal.svg'],
        ];

        foreach ($pages as $url => [$key, $logo]) {
            $this->get($url)
                ->assertOk()
                ->assertSee('name="university_id" value="'.$key.'"', false)
                ->assertSee($logo, false);
        }
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
}
