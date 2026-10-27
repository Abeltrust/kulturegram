<?php

namespace Tests\Feature;

use App\Models\Culture;
use App\Models\Event;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoverTabsSmoothSwitchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_discover_page_loads_normally()
    {
        $response = $this->get(route('discover.index'));
        $response->assertStatus(200);
        $response->assertSee('Stories');
        $response->assertSee('Cultures');
        $response->assertSee('People');
        $response->assertSee('Places');
        $response->assertSee('discover-grid-container');
    }

    public function test_discover_ajax_returns_json_partial_for_stories()
    {
        $response = $this->getJson(route('discover.index', ['tab' => 'stories']));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'tab',
            'html',
        ]);
        $this->assertEquals('stories', $response->json('tab'));
        $this->assertTrue($response->json('success'));
    }

    public function test_discover_ajax_returns_json_partial_for_cultures()
    {
        $response = $this->getJson(route('discover.index', ['tab' => 'cultures']));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'tab',
            'html',
        ]);
        $this->assertEquals('cultures', $response->json('tab'));
        $this->assertTrue($response->json('success'));
    }

    public function test_discover_ajax_returns_json_partial_for_people()
    {
        $response = $this->getJson(route('discover.index', ['tab' => 'people']));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'tab',
            'html',
        ]);
        $this->assertEquals('people', $response->json('tab'));
        $this->assertTrue($response->json('success'));
    }

    public function test_discover_ajax_returns_json_partial_for_places()
    {
        $response = $this->getJson(route('discover.index', ['tab' => 'places']));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'tab',
            'html',
        ]);
        $this->assertEquals('places', $response->json('tab'));
        $this->assertTrue($response->json('success'));
    }
}
