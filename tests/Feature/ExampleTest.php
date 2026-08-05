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
            '/al-azhar-university' => ['AUG', 'images/universities/aug.svg'],
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
}
