<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LandingControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_renders_the_landing_page_with_correct_props()
    {
        // Seed basic settings
        Setting::create(['key' => 'web_nama', 'value' => 'SMAN 2 Perbaungan']);

        $response = $this->get('/');

        $response->assertStatus(200);
        // Pastikan response adalah Inertia response (JSON atau HTML dengan X-Inertia)
        $response->assertSee('SMAN 2 Perbaungan');
    }

    #[Test]
    public function it_returns_200_on_home_route()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
    }
}
