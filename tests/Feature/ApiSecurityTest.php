<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_search_endpoints()
    {
        // Search endpoint should be accessible to guests
        $response = $this->getJson(route('search'));
        $response->assertStatus(200);

        // Search suggestions should be accessible to guests
        $response = $this->getJson(route('search.suggestions'));
        $response->assertStatus(200);

        // Group by company should be accessible to guests
        $response = $this->getJson(route('search.group-by-company'));
        $response->assertStatus(200);

        // Group by position should be accessible to guests
        $response = $this->getJson(route('search.group-by-position'));
        $response->assertStatus(200);

        // Search by tags should be accessible to guests
        $response = $this->getJson(route('search.by-tags'));
        $response->assertStatus(200);

        // Available tags should be accessible to guests (for filtering)
        $response = $this->getJson(route('search.available-tags'));
        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_tags_api()
    {
        $response = $this->getJson(route('tags.api'));
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_tags_api()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->getJson(route('tags.api'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'color']
        ]);
    }

    public function test_guest_cannot_access_tag_management_routes()
    {
        // Create a tag for testing
        $tag = Tag::factory()->create();

        // Test all tag management routes
        $this->get(route('tags.index'))->assertRedirect(route('login'));
        $this->get(route('tags.create'))->assertRedirect(route('login'));
        $this->get(route('tags.show', $tag))->assertRedirect(route('login'));
        $this->get(route('tags.edit', $tag))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_tag_management_routes()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        // Test all tag management routes
        $this->actingAs($user)->get(route('tags.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('tags.create'))->assertStatus(200);
        $this->actingAs($user)->get(route('tags.show', $tag))->assertStatus(200);
        $this->actingAs($user)->get(route('tags.edit', $tag))->assertStatus(200);
    }
}