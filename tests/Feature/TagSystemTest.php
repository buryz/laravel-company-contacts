<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_authenticated_user_can_view_tags_index()
    {
        $response = $this->actingAs($this->user)->get('/tags');
        
        $response->assertStatus(200);
        $response->assertViewIs('tags.index');
    }

    public function test_guest_cannot_view_tags_index()
    {
        $response = $this->get('/tags');
        
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_tag()
    {
        $tagData = [
            'name' => 'VIP Client',
            'color' => '#FF0000',
        ];

        $response = $this->actingAs($this->user)
                         ->withSession(['_token' => 'test-token'])
                         ->post('/tags', array_merge($tagData, ['_token' => 'test-token']));
        
        $response->assertRedirect('/tags');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('tags', [
            'name' => 'VIP Client',
            'color' => '#FF0000',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_authenticated_user_can_update_tag()
    {
        $tag = Tag::factory()->create([
            'name' => 'Old Name',
            'color' => '#000000',
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'name' => 'New Name',
            'color' => '#FF0000',
        ];

        $response = $this->actingAs($this->user)
                         ->withSession(['_token' => 'test-token'])
                         ->put("/tags/{$tag->id}", array_merge($updateData, ['_token' => 'test-token']));
        
        $response->assertRedirect('/tags');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'New Name',
            'color' => '#FF0000',
        ]);
    }

    public function test_authenticated_user_can_delete_tag()
    {
        $tag = Tag::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
                         ->withSession(['_token' => 'test-token'])
                         ->delete("/tags/{$tag->id}", ['_token' => 'test-token']);
        
        $response->assertRedirect('/tags');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_contact_can_have_tags_assigned()
    {
        $contact = Contact::factory()->create([
            'created_by' => $this->user->id,
        ]);
        
        $tag1 = Tag::factory()->create(['created_by' => $this->user->id]);
        $tag2 = Tag::factory()->create(['created_by' => $this->user->id]);

        $contact->tags()->attach([$tag1->id, $tag2->id]);

        $this->assertEquals(2, $contact->tags()->count());
        $this->assertTrue($contact->tags->contains($tag1));
        $this->assertTrue($contact->tags->contains($tag2));
    }

    public function test_deleting_tag_removes_it_from_contacts()
    {
        $contact = Contact::factory()->create([
            'created_by' => $this->user->id,
        ]);
        
        $tag = Tag::factory()->create(['created_by' => $this->user->id]);
        $contact->tags()->attach($tag->id);

        $this->assertEquals(1, $contact->tags()->count());

        $response = $this->actingAs($this->user)
                         ->withSession(['_token' => 'test-token'])
                         ->delete("/tags/{$tag->id}", ['_token' => 'test-token']);
        
        $response->assertRedirect('/tags');
        
        $contact->refresh();
        $this->assertEquals(0, $contact->tags()->count());
    }

    public function test_tag_validation_requires_name()
    {
        $response = $this->actingAs($this->user)
                         ->withSession(['_token' => 'test-token'])
                         ->post('/tags', [
                             'color' => '#FF0000',
                             '_token' => 'test-token'
                         ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_tag_validation_requires_valid_color_format()
    {
        $response = $this->actingAs($this->user)
                         ->withSession(['_token' => 'test-token'])
                         ->post('/tags', [
                             'name' => 'Test Tag',
                             'color' => 'invalid-color',
                             '_token' => 'test-token'
                         ]);

        $response->assertSessionHasErrors('color');
    }
}