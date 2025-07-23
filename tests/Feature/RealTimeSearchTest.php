<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RealTimeSearchTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Contact $contact1;
    protected Contact $contact2;
    protected Contact $contact3;
    protected Tag $tag1;
    protected Tag $tag2;
    protected Tag $tag3;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        // Create test contacts with diverse data for testing search criteria
        $this->contact1 = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan.kowalski@abc.com',
            'company' => 'ABC Corporation',
            'position' => 'Senior Developer',
            'phone' => '123456789'
        ]);
        
        $this->contact2 = Contact::factory()->create([
            'first_name' => 'Anna',
            'last_name' => 'Nowak',
            'email' => 'anna.nowak@xyz.com',
            'company' => 'XYZ Limited',
            'position' => 'Project Manager',
            'phone' => '987654321'
        ]);
        
        $this->contact3 = Contact::factory()->create([
            'first_name' => 'Piotr',
            'last_name' => 'Kowalczyk',
            'email' => 'piotr.kowalczyk@abc.com',
            'company' => 'ABC Corporation',
            'position' => 'Junior Developer',
            'phone' => '555666777'
        ]);
        
        // Create additional contact with same position but different company
        Contact::factory()->create([
            'first_name' => 'Tomasz',
            'last_name' => 'Wiśniewski',
            'email' => 'tomasz.wisniewski@def.com',
            'company' => 'DEF Solutions',
            'position' => 'Project Manager',
            'phone' => '111222333'
        ]);
        
        // Create test tags
        $this->tag1 = Tag::factory()->create(['name' => 'VIP', 'color' => '#FF0000']);
        $this->tag2 = Tag::factory()->create(['name' => 'Client', 'color' => '#00FF00']);
        $this->tag3 = Tag::factory()->create(['name' => 'Partner', 'color' => '#0000FF']);
        
        // Assign tags to contacts
        $this->contact1->tags()->attach([$this->tag1->id, $this->tag2->id]);
        $this->contact2->tags()->attach([$this->tag1->id, $this->tag3->id]);
        $this->contact3->tags()->attach([$this->tag2->id]);
    }

    /**
     * Test real-time search with partial name match
     */
    public function test_real_time_search_with_partial_name()
    {
        // Test with partial first name
        $response = $this->getJson(route('search', ['query' => 'Ja']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        $contacts = $response->json('contacts');
        $this->assertEquals('Jan Kowalski', $contacts[0]['full_name']);
        
        // Test with partial last name
        $response = $this->getJson(route('search', ['query' => 'Kow']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        // Verify both Kowalski and Kowalczyk are found
        $lastNames = array_column($response->json('contacts'), 'last_name');
        $this->assertContains('Kowalski', $lastNames);
        $this->assertContains('Kowalczyk', $lastNames);
    }

    /**
     * Test real-time search with full name match
     */
    public function test_real_time_search_with_full_name()
    {
        $response = $this->getJson(route('search', ['query' => 'Jan Kowalski']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        $contacts = $response->json('contacts');
        $this->assertEquals('Jan Kowalski', $contacts[0]['full_name']);
    }

    /**
     * Test real-time search with company name
     */
    public function test_real_time_search_with_company_name()
    {
        // Test with full company name
        $response = $this->getJson(route('search', ['query' => 'ABC Corporation']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        // Test with partial company name
        $response = $this->getJson(route('search', ['query' => 'XYZ']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        $contacts = $response->json('contacts');
        $this->assertEquals('XYZ Limited', $contacts[0]['company']);
    }

    /**
     * Test real-time search with position
     */
    public function test_real_time_search_with_position()
    {
        // Test with full position
        $response = $this->getJson(route('search', ['query' => 'Project Manager']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        // Test with partial position
        $response = $this->getJson(route('search', ['query' => 'Developer']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        // Verify both Senior and Junior Developer are found
        $positions = array_column($response->json('contacts'), 'position');
        $this->assertContains('Senior Developer', $positions);
        $this->assertContains('Junior Developer', $positions);
    }

    /**
     * Test real-time search with email
     */
    public function test_real_time_search_with_email()
    {
        // Test with full email
        $response = $this->getJson(route('search', ['query' => 'jan.kowalski@abc.com']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        
        // Test with partial email domain
        $response = $this->getJson(route('search', ['query' => '@abc.com']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
    }

    /**
     * Test real-time search with tag name
     */
    public function test_real_time_search_with_tag_name()
    {
        // Test with tag name
        $response = $this->getJson(route('search', ['query' => 'VIP']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        // Verify contacts with VIP tag are found
        $contactIds = array_column($response->json('contacts'), 'id');
        $this->assertContains($this->contact1->id, $contactIds);
        $this->assertContains($this->contact2->id, $contactIds);
    }

    /**
     * Test real-time search with multiple criteria
     */
    public function test_real_time_search_with_multiple_criteria()
    {
        // Test with name and company
        $response = $this->getJson(route('search', [
            'query' => 'Jan',
            'company' => 'ABC Corporation'
        ]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        
        // Test with position and company
        $response = $this->getJson(route('search', [
            'query' => 'Developer',
            'company' => 'ABC Corporation'
        ]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
    }

    /**
     * Test real-time search with tag filters
     */
    public function test_real_time_search_with_tag_filters()
    {
        // Test with single tag filter
        $response = $this->getJson(route('search', [
            'query' => '',
            'tags' => [$this->tag1->id]
        ]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        // Test with multiple tags - ANY mode (default)
        $response = $this->getJson(route('search', [
            'query' => '',
            'tags' => [$this->tag1->id, $this->tag2->id]
        ]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 3
        ]);
        
        // Test with multiple tags - ALL mode
        $response = $this->getJson(route('search', [
            'query' => '',
            'tags' => [$this->tag1->id, $this->tag2->id],
            'tag_search_mode' => 'all'
        ]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        
        // Verify only contact with both tags is found
        $contacts = $response->json('contacts');
        $this->assertEquals($this->contact1->id, $contacts[0]['id']);
    }

    /**
     * Test real-time search with combined text and tag filters
     */
    public function test_real_time_search_with_combined_text_and_tag_filters()
    {
        // Test with text query and tag filter
        $response = $this->getJson(route('search', [
            'query' => 'Developer',
            'tags' => [$this->tag2->id]
        ]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        // Test with more specific query and tag filter
        $response = $this->getJson(route('search', [
            'query' => 'Senior',
            'tags' => [$this->tag2->id]
        ]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        
        // Verify only Senior Developer with Client tag is found
        $contacts = $response->json('contacts');
        $this->assertEquals($this->contact1->id, $contacts[0]['id']);
    }
}