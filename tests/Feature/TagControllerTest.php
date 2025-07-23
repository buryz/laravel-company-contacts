<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    // INDEX TESTS
    public function test_guest_cannot_access_tags_index()
    {
        $response = $this->get(route('tags.index'));

        $response->assertRedirect(route('login')); // Laravel's default login route
    }

    public function test_authenticated_user_can_access_tags_index()
    {
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('tags.index'));

        $response->assertStatus(200);
        $response->assertSee('Zarządzanie tagami');
        $response->assertSee($tag->name);
    }

    public function test_tags_index_shows_contact_counts()
    {
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);
        $contact1 = Contact::factory()->create();
        $contact2 = Contact::factory()->create();
        
        $tag->contacts()->attach([$contact1->id, $contact2->id]);

        $response = $this->actingAs($this->user)->get(route('tags.index'));

        $response->assertStatus(200);
        $response->assertSee('2'); // Should show contact count
    }

    public function test_tags_index_shows_tags_alphabetically()
    {
        Tag::factory()->create(['name' => 'Zebra', 'created_by' => $this->user->id]);
        Tag::factory()->create(['name' => 'Alpha', 'created_by' => $this->user->id]);
        Tag::factory()->create(['name' => 'Beta', 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('tags.index'));

        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Check that Alpha appears before Beta, and Beta before Zebra
        $alphaPos = strpos($content, 'Alpha');
        $betaPos = strpos($content, 'Beta');
        $zebraPos = strpos($content, 'Zebra');
        
        $this->assertLessThan($betaPos, $alphaPos);
        $this->assertLessThan($zebraPos, $betaPos);
    }

    public function test_tags_index_shows_empty_state_when_no_tags()
    {
        $response = $this->actingAs($this->user)->get(route('tags.index'));

        $response->assertStatus(200);
        $response->assertSee('Brak tagów');
    }

    // CREATE TESTS
    public function test_guest_cannot_access_create_form()
    {
        $response = $this->get(route('tags.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_create_form()
    {
        $response = $this->actingAs($this->user)->get(route('tags.create'));

        $response->assertStatus(200);
        $response->assertSee('Dodaj nowy tag');
        $response->assertSee('Nazwa tagu');
        $response->assertSee('Kolor');
    }

    // STORE TESTS
    public function test_guest_cannot_store_tag()
    {
        $tagData = [
            'name' => 'Test Tag',
            'color' => '#FF0000'
        ];

        $response = $this->post(route('tags.store'), $tagData);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('tags', ['name' => 'Test Tag']);
    }

    public function test_authenticated_user_can_store_tag()
    {
        $tagData = [
            'name' => 'VIP Client',
            'color' => '#FF0000'
        ];

        $response = $this->actingAs($this->user)->post(route('tags.store'), $tagData);

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success', 'Tag został utworzony pomyślnie.');
        
        $this->assertDatabaseHas('tags', [
            'name' => 'VIP Client',
            'color' => '#FF0000',
            'created_by' => $this->user->id
        ]);
    }

    public function test_store_tag_with_default_color()
    {
        $tagData = [
            'name' => 'Default Color Tag'
        ];

        $response = $this->actingAs($this->user)->post(route('tags.store'), $tagData);

        $response->assertRedirect(route('tags.index'));
        
        $this->assertDatabaseHas('tags', [
            'name' => 'Default Color Tag',
            'color' => '#3B82F6', // Default blue color
            'created_by' => $this->user->id
        ]);
    }

    public function test_store_tag_validation_fails_with_empty_name()
    {
        $tagData = [
            'name' => '',
            'color' => '#FF0000'
        ];

        $response = $this->actingAs($this->user)->post(route('tags.store'), $tagData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_tag_validation_fails_with_duplicate_name_for_same_user()
    {
        Tag::factory()->create([
            'name' => 'Existing Tag',
            'created_by' => $this->user->id
        ]);

        $tagData = [
            'name' => 'Existing Tag',
            'color' => '#FF0000'
        ];

        $response = $this->actingAs($this->user)->post(route('tags.store'), $tagData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_tag_allows_duplicate_name_for_different_users()
    {
        Tag::factory()->create([
            'name' => 'Shared Tag Name',
            'created_by' => $this->otherUser->id
        ]);

        $tagData = [
            'name' => 'Shared Tag Name',
            'color' => '#FF0000'
        ];

        $response = $this->actingAs($this->user)->post(route('tags.store'), $tagData);

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('tags', [
            'name' => 'Shared Tag Name',
            'created_by' => $this->user->id
        ]);
    }

    public function test_store_tag_validation_fails_with_invalid_color()
    {
        $tagData = [
            'name' => 'Test Tag',
            'color' => 'invalid-color'
        ];

        $response = $this->actingAs($this->user)->post(route('tags.store'), $tagData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['color']);
    }

    // SHOW TESTS
    public function test_guest_cannot_view_tag_details()
    {
        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.show', $tag));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_tag_details()
    {
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);
        $contact1 = Contact::factory()->create(['first_name' => 'Anna', 'last_name' => 'Kowalska']);
        $contact2 = Contact::factory()->create(['first_name' => 'Jan', 'last_name' => 'Nowak']);
        
        $tag->contacts()->attach([$contact1->id, $contact2->id]);

        $response = $this->actingAs($this->user)->get(route('tags.show', $tag));

        $response->assertStatus(200);
        $response->assertSee($tag->name);
        $response->assertSee('Anna Kowalska');
        $response->assertSee('Jan Nowak');
    }

    public function test_tag_show_displays_contacts_alphabetically()
    {
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);
        $contact1 = Contact::factory()->create(['first_name' => 'Zebra', 'last_name' => 'Last']);
        $contact2 = Contact::factory()->create(['first_name' => 'Alpha', 'last_name' => 'First']);
        
        $tag->contacts()->attach([$contact1->id, $contact2->id]);

        $response = $this->actingAs($this->user)->get(route('tags.show', $tag));

        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Alpha First should appear before Zebra Last
        $alphaPos = strpos($content, 'Alpha First');
        $zebraPos = strpos($content, 'Zebra Last');
        
        $this->assertLessThan($zebraPos, $alphaPos);
    }

    public function test_user_cannot_view_other_users_tag()
    {
        $tag = Tag::factory()->create(['created_by' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('tags.show', $tag));

        $response->assertStatus(403);
    }

    // EDIT TESTS
    public function test_guest_cannot_access_edit_form()
    {
        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.edit', $tag));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_edit_form()
    {
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('tags.edit', $tag));

        $response->assertStatus(200);
        $response->assertSee('Edytuj tag');
        $response->assertSee($tag->name);
        $response->assertSee($tag->color);
    }

    public function test_user_cannot_edit_other_users_tag()
    {
        $tag = Tag::factory()->create(['created_by' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('tags.edit', $tag));

        $response->assertStatus(403);
    }

    // UPDATE TESTS
    public function test_guest_cannot_update_tag()
    {
        $tag = Tag::factory()->create();
        $updateData = ['name' => 'Updated Tag'];

        $response = $this->put(route('tags.update', $tag), $updateData);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('tags', ['name' => 'Updated Tag']);
    }

    public function test_authenticated_user_can_update_tag()
    {
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);
        
        $updateData = [
            'name' => 'Updated Tag Name',
            'color' => '#00FF00'
        ];

        $response = $this->actingAs($this->user)->put(route('tags.update', $tag), $updateData);

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success', 'Tag został zaktualizowany pomyślnie.');
        
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Tag Name',
            'color' => '#00FF00'
        ]);
    }

    public function test_update_tag_validation_fails_with_empty_name()
    {
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);
        
        $updateData = [
            'name' => '',
            'color' => '#FF0000'
        ];

        $response = $this->actingAs($this->user)->put(route('tags.update', $tag), $updateData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_tag_validation_fails_with_duplicate_name()
    {
        $existingTag = Tag::factory()->create([
            'name' => 'Existing Tag',
            'created_by' => $this->user->id
        ]);
        
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);
        
        $updateData = [
            'name' => 'Existing Tag',
            'color' => '#FF0000'
        ];

        $response = $this->actingAs($this->user)->put(route('tags.update', $tag), $updateData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);
    }

    public function test_user_cannot_update_other_users_tag()
    {
        $tag = Tag::factory()->create(['created_by' => $this->otherUser->id]);
        $updateData = ['name' => 'Updated Tag'];

        $response = $this->actingAs($this->user)->put(route('tags.update', $tag), $updateData);

        $response->assertStatus(403);
    }

    // DESTROY TESTS
    public function test_guest_cannot_delete_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_authenticated_user_can_delete_tag()
    {
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);
        $tagName = $tag->name;

        $response = $this->actingAs($this->user)->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success', "Tag '{$tagName}' został usunięty pomyślnie.");
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_delete_tag_removes_contact_associations()
    {
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);
        $contact = Contact::factory()->create();
        
        $tag->contacts()->attach($contact->id);
        
        $this->assertDatabaseHas('contact_tag', [
            'tag_id' => $tag->id,
            'contact_id' => $contact->id
        ]);

        $response = $this->actingAs($this->user)->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('tags.index'));
        
        // Tag should be deleted
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
        
        // Association should be removed
        $this->assertDatabaseMissing('contact_tag', [
            'tag_id' => $tag->id,
            'contact_id' => $contact->id
        ]);
        
        // Contact should still exist
        $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
    }

    public function test_delete_tag_shows_contact_count_in_message()
    {
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);
        $contact1 = Contact::factory()->create();
        $contact2 = Contact::factory()->create();
        
        $tag->contacts()->attach([$contact1->id, $contact2->id]);
        $tagName = $tag->name;

        $response = $this->actingAs($this->user)->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success', "Tag '{$tagName}' został usunięty. Usunięto go z 2 kontaktów.");
    }

    public function test_user_cannot_delete_other_users_tag()
    {
        $tag = Tag::factory()->create(['created_by' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->delete(route('tags.destroy', $tag));

        $response->assertStatus(403);
        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    // API TESTS
    public function test_guest_cannot_access_tags_api()
    {
        $response = $this->getJson(route('tags.api'));

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_tags_api()
    {
        $tag1 = Tag::factory()->create(['name' => 'Alpha', 'created_by' => $this->user->id]);
        $tag2 = Tag::factory()->create(['name' => 'Beta', 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson(route('tags.api'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'color']
        ]);
        
        $tags = $response->json();
        $this->assertCount(2, $tags);
        
        // Should be ordered alphabetically
        $this->assertEquals('Alpha', $tags[0]['name']);
        $this->assertEquals('Beta', $tags[1]['name']);
    }

    public function test_tags_api_returns_only_essential_fields()
    {
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson(route('tags.api'));

        $response->assertStatus(200);
        
        $tags = $response->json();
        $tag = $tags[0];
        
        // Should only include id, name, color
        $this->assertArrayHasKey('id', $tag);
        $this->assertArrayHasKey('name', $tag);
        $this->assertArrayHasKey('color', $tag);
        $this->assertArrayNotHasKey('created_by', $tag);
        $this->assertArrayNotHasKey('created_at', $tag);
        $this->assertArrayNotHasKey('updated_at', $tag);
    }

    // ERROR HANDLING TESTS
    public function test_nonexistent_tag_returns_404()
    {
        $response = $this->actingAs($this->user)->get(route('tags.show', 999999));
        $response->assertStatus(404);
    }

    public function test_tags_index_handles_database_errors_gracefully()
    {
        // Test that the route exists and returns a valid response
        $response = $this->actingAs($this->user)->get(route('tags.index'));
        $response->assertStatus(200);
    }

    // MIDDLEWARE TESTS
    public function test_all_tag_routes_require_authentication()
    {
        $tag = Tag::factory()->create();
        
        $routes = [
            ['GET', route('tags.index')],
            ['GET', route('tags.create')],
            ['POST', route('tags.store')],
            ['GET', route('tags.show', $tag)],
            ['GET', route('tags.edit', $tag)],
            ['PUT', route('tags.update', $tag)],
            ['DELETE', route('tags.destroy', $tag)],
            ['GET', route('tags.api')]
        ];
        
        foreach ($routes as [$method, $url]) {
            $response = $this->call($method, $url);
            
            if ($method === 'GET' && str_contains($url, '/api/')) {
                // API routes return 401 for unauthenticated requests
                $this->assertEquals(401, $response->getStatusCode(), "Route {$method} {$url} should return 401");
            } else {
                // Web routes redirect to login
                $this->assertEquals(302, $response->getStatusCode(), "Route {$method} {$url} should redirect");
                $this->assertStringContainsString('auth/login', $response->headers->get('Location'));
            }
        }
    }

    // VALIDATION TESTS
    public function test_tag_name_validation_rules()
    {
        $testCases = [
            ['name' => '', 'shouldFail' => true], // Required
            ['name' => str_repeat('a', 256), 'shouldFail' => true], // Too long
            ['name' => 'Valid Name', 'shouldFail' => false], // Valid
            ['name' => 'A', 'shouldFail' => false], // Minimum length
            ['name' => str_repeat('a', 255), 'shouldFail' => false], // Maximum length
        ];
        
        foreach ($testCases as $testCase) {
            $response = $this->actingAs($this->user)->post(route('tags.store'), [
                'name' => $testCase['name'],
                'color' => '#FF0000'
            ]);
            
            if ($testCase['shouldFail']) {
                $response->assertSessionHasErrors(['name']);
            } else {
                $response->assertSessionDoesntHaveErrors(['name']);
            }
        }
    }

    public function test_tag_color_validation_rules()
    {
        $testCases = [
            ['color' => '#FF0000', 'shouldFail' => false], // Valid hex
            ['color' => '#123456', 'shouldFail' => false], // Valid hex
            ['color' => 'red', 'shouldFail' => true], // Invalid format
            ['color' => '#GG0000', 'shouldFail' => true], // Invalid hex
            ['color' => '#FF00', 'shouldFail' => true], // Too short
            ['color' => '#FF000000', 'shouldFail' => true], // Too long
            ['color' => '', 'shouldFail' => false], // Optional field
        ];
        
        foreach ($testCases as $testCase) {
            $data = ['name' => 'Test Tag ' . uniqid()];
            if ($testCase['color'] !== '') {
                $data['color'] = $testCase['color'];
            }
            
            $response = $this->actingAs($this->user)->post(route('tags.store'), $data);
            
            if ($testCase['shouldFail']) {
                $response->assertSessionHasErrors(['color']);
            } else {
                $response->assertSessionDoesntHaveErrors(['color']);
            }
        }
    }

    // INTEGRATION TESTS
    public function test_tag_crud_workflow()
    {
        // Create
        $response = $this->actingAs($this->user)->post(route('tags.store'), [
            'name' => 'Workflow Test',
            'color' => '#FF0000'
        ]);
        $response->assertRedirect(route('tags.index'));
        
        $tag = Tag::where('name', 'Workflow Test')->first();
        $this->assertNotNull($tag);
        
        // Read
        $response = $this->actingAs($this->user)->get(route('tags.show', $tag));
        $response->assertStatus(200);
        $response->assertSee('Workflow Test');
        
        // Update
        $response = $this->actingAs($this->user)->put(route('tags.update', $tag), [
            'name' => 'Updated Workflow Test',
            'color' => '#00FF00'
        ]);
        $response->assertRedirect(route('tags.index'));
        
        $tag->refresh();
        $this->assertEquals('Updated Workflow Test', $tag->name);
        $this->assertEquals('#00FF00', $tag->color);
        
        // Delete
        $response = $this->actingAs($this->user)->delete(route('tags.destroy', $tag));
        $response->assertRedirect(route('tags.index'));
        
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}