<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test ContactPolicy viewAny ability.
     */
    public function test_contact_policy_view_any()
    {
        // Guests can view contacts list
        $this->assertTrue(Gate::allows('viewAny', Contact::class));
        
        // Authenticated users can view contacts list
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('viewAny', Contact::class));
    }

    /**
     * Test ContactPolicy view ability.
     */
    public function test_contact_policy_view()
    {
        $contact = Contact::factory()->create();
        
        // Guests can view individual contacts
        $this->assertTrue(Gate::allows('view', $contact));
        
        // Authenticated users can view individual contacts
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('view', $contact));
    }

    /**
     * Test ContactPolicy create ability.
     */
    public function test_contact_policy_create()
    {
        // Guests cannot create contacts
        $this->assertFalse(Gate::allows('create', Contact::class));
        
        // Authenticated users can create contacts
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('create', Contact::class));
    }

    /**
     * Test ContactPolicy update ability.
     */
    public function test_contact_policy_update()
    {
        $contact = Contact::factory()->create();
        
        // Guests cannot update contacts
        $this->assertFalse(Gate::allows('update', $contact));
        
        // Authenticated users can update contacts
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('update', $contact));
    }

    /**
     * Test ContactPolicy delete ability.
     */
    public function test_contact_policy_delete()
    {
        $contact = Contact::factory()->create();
        
        // Guests cannot delete contacts
        $this->assertFalse(Gate::allows('delete', $contact));
        
        // Authenticated users can delete contacts
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('delete', $contact));
    }

    /**
     * Test ContactPolicy export ability.
     */
    public function test_contact_policy_export()
    {
        // Guests cannot export contacts
        $this->assertFalse(Gate::allows('export', Contact::class));
        
        // Authenticated users can export contacts
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('export', Contact::class));
    }

    /**
     * Test ContactPolicy generateQR ability.
     */
    public function test_contact_policy_generate_qr()
    {
        $contact = Contact::factory()->create();
        
        // Guests can generate QR codes
        $this->assertTrue(Gate::allows('generateQR', $contact));
        
        // Authenticated users can generate QR codes
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('generateQR', $contact));
    }

    /**
     * Test TagPolicy viewAny ability.
     */
    public function test_tag_policy_view_any()
    {
        // Guests cannot view tags management
        $this->assertFalse(Gate::allows('viewAny', Tag::class));
        
        // Authenticated users can view tags management
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('viewAny', Tag::class));
    }

    /**
     * Test TagPolicy view ability.
     */
    public function test_tag_policy_view()
    {
        $tag = Tag::factory()->create();
        
        // Guests cannot view individual tags
        $this->assertFalse(Gate::allows('view', $tag));
        
        // Authenticated users can view individual tags
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('view', $tag));
    }

    /**
     * Test TagPolicy create ability.
     */
    public function test_tag_policy_create()
    {
        // Guests cannot create tags
        $this->assertFalse(Gate::allows('create', Tag::class));
        
        // Authenticated users can create tags
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('create', Tag::class));
    }

    /**
     * Test TagPolicy update ability.
     */
    public function test_tag_policy_update()
    {
        $tag = Tag::factory()->create();
        
        // Guests cannot update tags
        $this->assertFalse(Gate::allows('update', $tag));
        
        // Authenticated users can update tags
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('update', $tag));
    }

    /**
     * Test TagPolicy delete ability.
     */
    public function test_tag_policy_delete()
    {
        $tag = Tag::factory()->create();
        
        // Guests cannot delete tags
        $this->assertFalse(Gate::allows('delete', $tag));
        
        // Authenticated users can delete tags
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('delete', $tag));
    }

    /**
     * Test TagPolicy api ability.
     */
    public function test_tag_policy_api()
    {
        // Guests cannot access tags API
        $this->assertFalse(Gate::allows('api', Tag::class));
        
        // Authenticated users can access tags API
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('api', Tag::class));
    }

    /**
     * Test policy integration with controllers for authenticated users.
     */
    public function test_policy_integration_with_controllers_for_authenticated_users()
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create();
        
        // Test authenticated access to protected routes
        $this->actingAs($user)->get(route('contacts.create'))->assertSuccessful();
        $this->actingAs($user)->get(route('contacts.edit', $contact))->assertSuccessful();
        $this->actingAs($user)->get(route('contacts.export'))->assertSuccessful();
        $this->actingAs($user)->get(route('tags.index'))->assertSuccessful();
        $this->actingAs($user)->get(route('tags.create'))->assertSuccessful();
        $this->actingAs($user)->get(route('tags.edit', $tag))->assertSuccessful();
    }
    
    /**
     * Test policy integration with controllers for guests.
     */
    public function test_policy_integration_with_controllers_for_guests()
    {
        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create();
        
        // Test guest access to protected routes (should redirect to login)
        $response = $this->get(route('contacts.create'));
        $this->assertTrue($response->isRedirect(route('login')));
        
        $response = $this->get(route('tags.index'));
        $this->assertTrue($response->isRedirect(route('login')));
        
        $response = $this->get(route('tags.create'));
        $this->assertTrue($response->isRedirect(route('login')));
        
        $response = $this->get(route('tags.edit', $tag));
        $this->assertTrue($response->isRedirect(route('login')));
    }
}