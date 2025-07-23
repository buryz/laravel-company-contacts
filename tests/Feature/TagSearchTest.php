<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagSearchTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected array $tags = [];
    protected array $contacts = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        // Create a set of tags with different colors
        $this->tags['vip'] = Tag::factory()->create([
            'name' => 'VIP',
            'color' => '#FF0000'
        ]);
        
        $this->tags['client'] = Tag::factory()->create([
            'name' => 'Client',
            'color' => '#00FF00'
        ]);
        
        $this->tags['partner'] = Tag::factory()->create([
            'name' => 'Partner',
            'color' => '#0000FF'
        ]);
        
        $this->tags['supplier'] = Tag::factory()->create([
            'name' => 'Supplier',
            'color' => '#FFFF00'
        ]);
        
        $this->tags['prospect'] = Tag::factory()->create([
            'name' => 'Prospect',
            'color' => '#FF00FF'
        ]);
        
        // Create contacts with different tag combinations
        
        // Contact with all tags
        $this->contacts['all_tags'] = Contact::factory()->create([
            'first_name' => 'Adam',
            'last_name' => 'Nowak',
            'company' => 'Full Tags Corp'
        ]);
        $this->contacts['all_tags']->tags()->attach(array_column($this->tags, 'id'));
        
        // Contact with VIP and Client tags
        $this->contacts['vip_client'] = Contact::factory()->create([
            'first_name' => 'Barbara',
            'last_name' => 'Kowalska',
            'company' => 'ABC Ltd'
        ]);
        $this->contacts['vip_client']->tags()->attach([
            $this->tags['vip']->id,
            $this->tags['client']->id
        ]);
        
        // Contact with Partner and Supplier tags
        $this->contacts['partner_supplier'] = Contact::factory()->create([
            'first_name' => 'Cezary',
            'last_name' => 'Wiśniewski',
            'company' => 'XYZ Inc'
        ]);
        $this->contacts['partner_supplier']->tags()->attach([
            $this->tags['partner']->id,
            $this->tags['supplier']->id
        ]);
        
        // Contact with only VIP tag
        $this->contacts['vip_only'] = Contact::factory()->create([
            'first_name' => 'Dorota',
            'last_name' => 'Zielińska',
            'company' => 'VIP Services'
        ]);
        $this->contacts['vip_only']->tags()->attach([
            $this->tags['vip']->id
        ]);
        
        // Contact with only Prospect tag
        $this->contacts['prospect_only'] = Contact::factory()->create([
            'first_name' => 'Edward',
            'last_name' => 'Malinowski',
            'company' => 'New Ventures'
        ]);
        $this->contacts['prospect_only']->tags()->attach([
            $this->tags['prospect']->id
        ]);
        
        // Contact with no tags
        $this->contacts['no_tags'] = Contact::factory()->create([
            'first_name' => 'Franciszek',
            'last_name' => 'Lewandowski',
            'company' => 'No Tags Ltd'
        ]);
    }

    /**
     * Test searching contacts by a single tag
     */
    public function test_search_by_single_tag()
    {
        $response = $this->getJson(route('search.by-tags', [
            'tag_ids' => [$this->tags['vip']->id]
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 3 // all_tags, vip_client, vip_only
        ]);
        
        // Verify the correct contacts are returned
        $contactIds = array_column($response->json('contacts'), 'id');
        $this->assertContains($this->contacts['all_tags']->id, $contactIds);
        $this->assertContains($this->contacts['vip_client']->id, $contactIds);
        $this->assertContains($this->contacts['vip_only']->id, $contactIds);
        $this->assertNotContains($this->contacts['partner_supplier']->id, $contactIds);
        $this->assertNotContains($this->contacts['prospect_only']->id, $contactIds);
        $this->assertNotContains($this->contacts['no_tags']->id, $contactIds);
    }

    /**
     * Test searching contacts by multiple tags with ANY mode
     */
    public function test_search_by_multiple_tags_any_mode()
    {
        $response = $this->getJson(route('search.by-tags', [
            'tag_ids' => [$this->tags['vip']->id, $this->tags['partner']->id],
            'search_mode' => 'any'
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 4, // all_tags, vip_client, vip_only, partner_supplier
            'search_mode' => 'any'
        ]);
        
        // Verify the correct contacts are returned
        $contactIds = array_column($response->json('contacts'), 'id');
        $this->assertContains($this->contacts['all_tags']->id, $contactIds);
        $this->assertContains($this->contacts['vip_client']->id, $contactIds);
        $this->assertContains($this->contacts['vip_only']->id, $contactIds);
        $this->assertContains($this->contacts['partner_supplier']->id, $contactIds);
        $this->assertNotContains($this->contacts['prospect_only']->id, $contactIds);
        $this->assertNotContains($this->contacts['no_tags']->id, $contactIds);
    }

    /**
     * Test searching contacts by multiple tags with ALL mode
     */
    public function test_search_by_multiple_tags_all_mode()
    {
        $response = $this->getJson(route('search.by-tags', [
            'tag_ids' => [$this->tags['vip']->id, $this->tags['partner']->id],
            'search_mode' => 'all'
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1, // only all_tags has both VIP and Partner
            'search_mode' => 'all'
        ]);
        
        // Verify only the contact with all tags is returned
        $contactIds = array_column($response->json('contacts'), 'id');
        $this->assertContains($this->contacts['all_tags']->id, $contactIds);
        $this->assertNotContains($this->contacts['vip_client']->id, $contactIds);
        $this->assertNotContains($this->contacts['vip_only']->id, $contactIds);
        $this->assertNotContains($this->contacts['partner_supplier']->id, $contactIds);
    }

    /**
     * Test searching contacts by all five tags with ALL mode
     */
    public function test_search_by_all_five_tags()
    {
        $response = $this->getJson(route('search.by-tags', [
            'tag_ids' => array_column($this->tags, 'id'),
            'search_mode' => 'all'
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1, // only all_tags has all five tags
            'search_mode' => 'all'
        ]);
        
        // Verify only the contact with all tags is returned
        $contactIds = array_column($response->json('contacts'), 'id');
        $this->assertEquals([$this->contacts['all_tags']->id], $contactIds);
    }

    /**
     * Test searching contacts by non-existent tag
     */
    public function test_search_by_non_existent_tag()
    {
        $response = $this->getJson(route('search.by-tags', [
            'tag_ids' => [999999] // Non-existent tag ID
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'contacts' => [],
            'total' => 0
        ]);
    }

    /**
     * Test searching contacts by empty tag array
     */
    public function test_search_by_empty_tag_array()
    {
        $response = $this->getJson(route('search.by-tags', [
            'tag_ids' => []
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'contacts' => [],
            'total' => 0
        ]);
    }

    /**
     * Test getting available tags
     */
    public function test_get_available_tags()
    {
        $response = $this->getJson(route('search.available-tags'));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $tags = $response->json('tags');
        $this->assertCount(5, $tags);
        
        // Verify all tags are returned
        $tagNames = array_column($tags, 'name');
        $this->assertContains('VIP', $tagNames);
        $this->assertContains('Client', $tagNames);
        $this->assertContains('Partner', $tagNames);
        $this->assertContains('Supplier', $tagNames);
        $this->assertContains('Prospect', $tagNames);
        
        // Verify tag colors are returned
        $vipTag = collect($tags)->firstWhere('name', 'VIP');
        $this->assertEquals('#FF0000', $vipTag['color']);
        
        // Verify contact counts are returned
        $this->assertEquals(3, $vipTag['contacts_count']); // all_tags, vip_client, vip_only
    }

    /**
     * Test combined search with text query and tag filter
     */
    public function test_combined_search_with_text_and_tag()
    {
        $response = $this->getJson(route('search', [
            'query' => 'ABC', // Company name
            'tags' => [$this->tags['vip']->id]
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1 // Only vip_client matches both criteria
        ]);
        
        $contacts = $response->json('contacts');
        $this->assertEquals($this->contacts['vip_client']->id, $contacts[0]['id']);
    }

    /**
     * Test tag search with company filter
     */
    public function test_tag_search_with_company_filter()
    {
        $response = $this->getJson(route('search', [
            'tags' => [$this->tags['vip']->id],
            'company' => 'VIP Services'
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1 // Only vip_only matches both criteria
        ]);
        
        $contacts = $response->json('contacts');
        $this->assertEquals($this->contacts['vip_only']->id, $contacts[0]['id']);
    }

    /**
     * Test tag search with multiple filters
     */
    public function test_tag_search_with_multiple_filters()
    {
        // Create a contact with specific criteria for this test
        $specialContact = Contact::factory()->create([
            'first_name' => 'Grzegorz',
            'last_name' => 'Brzęczyszczykiewicz',
            'company' => 'ABC Ltd',
            'position' => 'CEO'
        ]);
        $specialContact->tags()->attach([
            $this->tags['vip']->id,
            $this->tags['client']->id
        ]);
        
        $response = $this->getJson(route('search', [
            'tags' => [$this->tags['vip']->id, $this->tags['client']->id],
            'company' => 'ABC Ltd',
            'position' => 'CEO',
            'tag_search_mode' => 'all'
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1 // Only the special contact matches all criteria
        ]);
        
        $contacts = $response->json('contacts');
        $this->assertEquals($specialContact->id, $contacts[0]['id']);
    }
}