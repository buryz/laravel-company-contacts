<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Contact $contact1;
    protected Contact $contact2;
    protected Contact $contact3;
    protected Tag $tag1;
    protected Tag $tag2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        // Create test contacts
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
        
        // Create test tags
        $this->tag1 = Tag::factory()->create(['name' => 'VIP']);
        $this->tag2 = Tag::factory()->create(['name' => 'Client']);
        
        // Assign tags to contacts
        $this->contact1->tags()->attach([$this->tag1->id, $this->tag2->id]);
        $this->contact2->tags()->attach([$this->tag1->id]);
        $this->contact3->tags()->attach([$this->tag2->id]);
    }

    // SEARCH TESTS
    public function test_search_returns_json_response()
    {
        $response = $this->getJson(route('search', ['query' => 'Jan']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'contacts' => [
                '*' => [
                    'id', 'full_name', 'first_name', 'last_name', 'email', 
                    'phone', 'company', 'position', 'tags', 'initials'
                ]
            ],
            'total'
        ]);
    }

    public function test_search_by_first_name()
    {
        $response = $this->getJson(route('search', ['query' => 'Jan']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        
        $contacts = $response->json('contacts');
        $this->assertEquals('Jan Kowalski', $contacts[0]['full_name']);
    }

    public function test_search_by_last_name()
    {
        $response = $this->getJson(route('search', ['query' => 'Kowalski']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        
        $contacts = $response->json('contacts');
        $this->assertEquals('Jan Kowalski', $contacts[0]['full_name']);
    }

    public function test_search_by_email()
    {
        $response = $this->getJson(route('search', ['query' => 'jan.kowalski@abc.com']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        
        $contacts = $response->json('contacts');
        $this->assertEquals('jan.kowalski@abc.com', $contacts[0]['email']);
    }

    public function test_search_by_company()
    {
        $response = $this->getJson(route('search', ['query' => 'ABC Corporation']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        $contacts = $response->json('contacts');
        $this->assertCount(2, $contacts);
        
        foreach ($contacts as $contact) {
            $this->assertEquals('ABC Corporation', $contact['company']);
        }
    }

    public function test_search_by_position()
    {
        $response = $this->getJson(route('search', ['query' => 'Developer']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        $contacts = $response->json('contacts');
        $this->assertCount(2, $contacts);
        
        foreach ($contacts as $contact) {
            $this->assertStringContainsString('Developer', $contact['position']);
        }
    }

    public function test_search_with_company_filter()
    {
        $response = $this->getJson(route('search', [
            'query' => '',
            'company' => 'ABC Corporation'
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        $contacts = $response->json('contacts');
        foreach ($contacts as $contact) {
            $this->assertEquals('ABC Corporation', $contact['company']);
        }
    }

    public function test_search_with_position_filter()
    {
        $response = $this->getJson(route('search', [
            'query' => '',
            'position' => 'Senior Developer'
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        
        $contacts = $response->json('contacts');
        $this->assertEquals('Senior Developer', $contacts[0]['position']);
    }

    public function test_search_with_tags_filter()
    {
        $response = $this->getJson(route('search', [
            'query' => '',
            'tags' => [$this->tag1->id]
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        $contacts = $response->json('contacts');
        foreach ($contacts as $contact) {
            $tagIds = array_column($contact['tags'], 'id');
            $this->assertContains($this->tag1->id, $tagIds);
        }
    }

    public function test_search_with_multiple_filters()
    {
        $response = $this->getJson(route('search', [
            'query' => 'Senior', // More specific search to get only one result
            'company' => 'ABC Corporation',
            'tags' => [$this->tag2->id]
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1
        ]);
        
        $contacts = $response->json('contacts');
        $contact = $contacts[0];
        $this->assertEquals('ABC Corporation', $contact['company']);
        $this->assertStringContainsString('Senior', $contact['position']);
        
        $tagIds = array_column($contact['tags'], 'id');
        $this->assertContains($this->tag2->id, $tagIds);
    }

    public function test_search_returns_empty_results_for_no_matches()
    {
        $response = $this->getJson(route('search', ['query' => 'NonexistentName']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'contacts' => [],
            'total' => 0
        ]);
    }

    public function test_search_handles_empty_query()
    {
        $response = $this->getJson(route('search', ['query' => '']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 3
        ]);
        
        $contacts = $response->json('contacts');
        $this->assertCount(3, $contacts);
    }

    // SUGGESTIONS TESTS
    public function test_suggestions_returns_json_response()
    {
        $response = $this->getJson(route('search.suggestions', ['query' => 'Jan']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'suggestions'
        ]);
    }

    public function test_suggestions_returns_empty_for_short_query()
    {
        $response = $this->getJson(route('search.suggestions', ['query' => 'J']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'suggestions' => []
        ]);
    }

    public function test_suggestions_returns_results_for_valid_query()
    {
        $response = $this->getJson(route('search.suggestions', ['query' => 'Jan']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $suggestions = $response->json('suggestions');
        $this->assertNotEmpty($suggestions);
    }

    // GROUP BY COMPANY TESTS
    public function test_group_by_company_returns_json_response()
    {
        $response = $this->getJson(route('search.group-by-company'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'groups' => [
                '*' => [
                    'company',
                    'count',
                    'contacts' => [
                        '*' => [
                            'id', 'full_name', 'first_name', 'last_name', 'email',
                            'phone', 'company', 'position', 'tags', 'initials'
                        ]
                    ]
                ]
            ],
            'total'
        ]);
    }

    public function test_group_by_company_groups_contacts_correctly()
    {
        $response = $this->getJson(route('search.group-by-company'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 3
        ]);
        
        $groups = $response->json('groups');
        
        // Should have 2 groups (ABC Corporation and XYZ Limited)
        $this->assertCount(2, $groups);
        
        // Find ABC Corporation group
        $abcGroup = collect($groups)->firstWhere('company', 'ABC Corporation');
        $this->assertNotNull($abcGroup);
        $this->assertEquals(2, $abcGroup['count']);
        $this->assertCount(2, $abcGroup['contacts']);
        
        // Find XYZ Limited group
        $xyzGroup = collect($groups)->firstWhere('company', 'XYZ Limited');
        $this->assertNotNull($xyzGroup);
        $this->assertEquals(1, $xyzGroup['count']);
        $this->assertCount(1, $xyzGroup['contacts']);
    }

    public function test_group_by_company_with_search_query()
    {
        $response = $this->getJson(route('search.group-by-company', ['query' => 'Developer']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        $groups = $response->json('groups');
        
        // Should only have ABC Corporation group (both developers are there)
        $this->assertCount(1, $groups);
        $this->assertEquals('ABC Corporation', $groups[0]['company']);
        $this->assertEquals(2, $groups[0]['count']);
    }

    // GROUP BY POSITION TESTS
    public function test_group_by_position_returns_json_response()
    {
        $response = $this->getJson(route('search.group-by-position'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'groups' => [
                '*' => [
                    'position',
                    'count',
                    'contacts' => [
                        '*' => [
                            'id', 'full_name', 'first_name', 'last_name', 'email',
                            'phone', 'company', 'position', 'tags', 'initials'
                        ]
                    ]
                ]
            ],
            'total'
        ]);
    }

    public function test_group_by_position_groups_contacts_correctly()
    {
        $response = $this->getJson(route('search.group-by-position'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 3
        ]);
        
        $groups = $response->json('groups');
        
        // Should have 3 groups (Senior Developer, Junior Developer, Project Manager)
        $this->assertCount(3, $groups);
        
        $positions = array_column($groups, 'position');
        $this->assertContains('Senior Developer', $positions);
        $this->assertContains('Junior Developer', $positions);
        $this->assertContains('Project Manager', $positions);
        
        foreach ($groups as $group) {
            $this->assertEquals(1, $group['count']);
            $this->assertCount(1, $group['contacts']);
        }
    }

    public function test_group_by_position_with_company_filter()
    {
        $response = $this->getJson(route('search.group-by-position', ['company' => 'ABC Corporation']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2
        ]);
        
        $groups = $response->json('groups');
        
        // Should only have Developer positions from ABC Corporation
        $this->assertCount(2, $groups);
        
        $positions = array_column($groups, 'position');
        $this->assertContains('Senior Developer', $positions);
        $this->assertContains('Junior Developer', $positions);
        $this->assertNotContains('Project Manager', $positions);
    }

    // SEARCH BY TAGS TESTS
    public function test_search_by_tags_returns_json_response()
    {
        $response = $this->getJson(route('search.by-tags', [
            'tag_ids' => [$this->tag1->id]
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'contacts' => [
                '*' => [
                    'id', 'full_name', 'first_name', 'last_name', 'email',
                    'phone', 'company', 'position', 'tags', 'initials'
                ]
            ],
            'total',
            'search_mode'
        ]);
    }

    public function test_search_by_tags_with_any_mode()
    {
        $response = $this->getJson(route('search.by-tags', [
            'tag_ids' => [$this->tag1->id, $this->tag2->id],
            'search_mode' => 'any'
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 3, // All contacts have at least one of these tags
            'search_mode' => 'any'
        ]);
    }

    public function test_search_by_tags_with_all_mode()
    {
        $response = $this->getJson(route('search.by-tags', [
            'tag_ids' => [$this->tag1->id, $this->tag2->id],
            'search_mode' => 'all'
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1, // Only contact1 has both tags
            'search_mode' => 'all'
        ]);
        
        $contacts = $response->json('contacts');
        $this->assertEquals($this->contact1->id, $contacts[0]['id']);
    }

    public function test_search_by_tags_with_empty_tag_ids()
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

    public function test_search_by_tags_with_invalid_tag_ids()
    {
        $response = $this->getJson(route('search.by-tags', [
            'tag_ids' => [99999] // Non-existent tag ID
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'contacts' => [],
            'total' => 0
        ]);
    }

    // GET AVAILABLE TAGS TESTS
    public function test_get_available_tags_returns_json_response()
    {
        $response = $this->getJson(route('search.available-tags'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'tags' => [
                '*' => [
                    'id', 'name', 'color', 'contacts_count'
                ]
            ]
        ]);
    }

    public function test_get_available_tags_returns_all_tags()
    {
        $response = $this->getJson(route('search.available-tags'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $tags = $response->json('tags');
        $this->assertCount(2, $tags);
        
        $tagNames = array_column($tags, 'name');
        $this->assertContains('VIP', $tagNames);
        $this->assertContains('Client', $tagNames);
    }

    public function test_get_available_tags_includes_contact_counts()
    {
        $response = $this->getJson(route('search.available-tags'));

        $response->assertStatus(200);
        
        $tags = $response->json('tags');
        
        foreach ($tags as $tag) {
            $this->assertArrayHasKey('contacts_count', $tag);
            $this->assertIsInt($tag['contacts_count']);
            $this->assertGreaterThanOrEqual(0, $tag['contacts_count']);
        }
        
        // VIP tag should have 2 contacts, Client tag should have 2 contacts
        $vipTag = collect($tags)->firstWhere('name', 'VIP');
        $clientTag = collect($tags)->firstWhere('name', 'Client');
        
        $this->assertEquals(2, $vipTag['contacts_count']);
        $this->assertEquals(2, $clientTag['contacts_count']);
    }

    // ERROR HANDLING TESTS
    public function test_search_handles_database_errors_gracefully()
    {
        // Test that malformed requests don't crash the application
        $response = $this->getJson(route('search', ['query' => null]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    public function test_search_handles_malformed_tag_filter()
    {
        $response = $this->getJson(route('search', [
            'query' => 'test',
            'tags' => 'not-an-array'
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    // PERFORMANCE TESTS
    public function test_search_with_large_dataset()
    {
        // Create many contacts to test performance
        Contact::factory()->count(50)->create();

        $response = $this->getJson(route('search', ['query' => '']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        // Should return all contacts (3 original + 50 new = 53)
        $this->assertEquals(53, $response->json('total'));
    }

    // RESPONSE FORMAT TESTS
    public function test_search_response_includes_contact_initials()
    {
        $response = $this->getJson(route('search', ['query' => 'Jan']));

        $response->assertStatus(200);
        
        $contacts = $response->json('contacts');
        $this->assertEquals('JK', $contacts[0]['initials']);
    }

    public function test_search_response_includes_tag_information()
    {
        $response = $this->getJson(route('search', ['query' => 'Jan']));

        $response->assertStatus(200);
        
        $contacts = $response->json('contacts');
        $contact = $contacts[0];
        
        $this->assertArrayHasKey('tags', $contact);
        $this->assertCount(2, $contact['tags']); // Jan has VIP and Client tags
        
        foreach ($contact['tags'] as $tag) {
            $this->assertArrayHasKey('id', $tag);
            $this->assertArrayHasKey('name', $tag);
            $this->assertArrayHasKey('color', $tag);
        }
    }

    public function test_search_case_insensitive()
    {
        $response1 = $this->getJson(route('search', ['query' => 'jan']));
        $response2 = $this->getJson(route('search', ['query' => 'JAN']));
        $response3 = $this->getJson(route('search', ['query' => 'Jan']));

        $response1->assertStatus(200);
        $response2->assertStatus(200);
        $response3->assertStatus(200);
        
        // All should return the same result
        $this->assertEquals($response1->json('total'), $response2->json('total'));
        $this->assertEquals($response2->json('total'), $response3->json('total'));
        $this->assertEquals(1, $response1->json('total'));
    }
}