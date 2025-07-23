<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactGroupingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected array $companies;
    protected array $positions;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        // Define test companies and positions
        $this->companies = [
            'ABC Corporation',
            'XYZ Limited',
            'DEF Solutions',
            'GHI Enterprises'
        ];
        
        $this->positions = [
            'Senior Developer',
            'Junior Developer',
            'Project Manager',
            'CEO',
            'CTO'
        ];
        
        // Create contacts for each company with various positions
        foreach ($this->companies as $company) {
            // Create 2-3 contacts per company with different positions
            $positionsCount = rand(2, 3);
            $selectedPositions = array_slice($this->positions, 0, $positionsCount);
            
            foreach ($selectedPositions as $position) {
                Contact::factory()->create([
                    'company' => $company,
                    'position' => $position
                ]);
            }
        }
        
        // Create multiple contacts with the same position but different companies
        foreach ($this->positions as $position) {
            // Create 1-2 additional contacts per position with random companies
            $companiesCount = rand(1, 2);
            $selectedCompanies = array_slice($this->companies, 0, $companiesCount);
            
            foreach ($selectedCompanies as $company) {
                Contact::factory()->create([
                    'company' => $company,
                    'position' => $position
                ]);
            }
        }
        
        // Create tags and assign to some contacts
        $tag1 = Tag::factory()->create(['name' => 'VIP']);
        $tag2 = Tag::factory()->create(['name' => 'Client']);
        
        // Assign tags to some contacts
        $contacts = Contact::all();
        foreach ($contacts as $index => $contact) {
            if ($index % 3 === 0) {
                $contact->tags()->attach($tag1);
            }
            if ($index % 4 === 0) {
                $contact->tags()->attach($tag2);
            }
        }
    }

    /**
     * Test grouping contacts by company
     */
    public function test_group_contacts_by_company()
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
        
        // Verify all companies are present in the groups
        $groups = $response->json('groups');
        $groupCompanies = array_column($groups, 'company');
        
        foreach ($this->companies as $company) {
            $this->assertContains($company, $groupCompanies);
        }
        
        // Verify each group has the correct company name and contacts
        foreach ($groups as $group) {
            $company = $group['company'];
            $contacts = $group['contacts'];
            
            // Check that all contacts in the group belong to the company
            foreach ($contacts as $contact) {
                $this->assertEquals($company, $contact['company']);
            }
            
            // Check that the count matches the number of contacts
            $this->assertEquals(count($contacts), $group['count']);
        }
    }

    /**
     * Test grouping contacts by position
     */
    public function test_group_contacts_by_position()
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
        
        // Verify all positions are present in the groups
        $groups = $response->json('groups');
        $groupPositions = array_column($groups, 'position');
        
        foreach ($this->positions as $position) {
            $this->assertContains($position, $groupPositions);
        }
        
        // Verify each group has the correct position name and contacts
        foreach ($groups as $group) {
            $position = $group['position'];
            $contacts = $group['contacts'];
            
            // Check that all contacts in the group have the position
            foreach ($contacts as $contact) {
                $this->assertEquals($position, $contact['position']);
            }
            
            // Check that the count matches the number of contacts
            $this->assertEquals(count($contacts), $group['count']);
        }
    }

    /**
     * Test grouping contacts by company with search query
     */
    public function test_group_contacts_by_company_with_search_query()
    {
        // Search for a specific position and group by company
        $response = $this->getJson(route('search.group-by-company', [
            'query' => 'Developer'
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $groups = $response->json('groups');
        
        // Check that all contacts in all groups have "Developer" in their position
        foreach ($groups as $group) {
            foreach ($group['contacts'] as $contact) {
                $this->assertStringContainsString('Developer', $contact['position']);
            }
        }
    }

    /**
     * Test grouping contacts by position with search query
     */
    public function test_group_contacts_by_position_with_search_query()
    {
        // Search for a specific company and group by position
        $response = $this->getJson(route('search.group-by-position', [
            'query' => 'ABC Corporation'
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $groups = $response->json('groups');
        
        // Check that all contacts in all groups are from ABC Corporation
        foreach ($groups as $group) {
            foreach ($group['contacts'] as $contact) {
                $this->assertEquals('ABC Corporation', $contact['company']);
            }
        }
    }

    /**
     * Test grouping contacts by company with tag filter
     */
    public function test_group_contacts_by_company_with_tag_filter()
    {
        // Get a tag ID
        $tag = Tag::where('name', 'VIP')->first();
        
        $response = $this->getJson(route('search.group-by-company', [
            'tags' => [$tag->id]
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $groups = $response->json('groups');
        
        // Check that all contacts in all groups have the VIP tag
        foreach ($groups as $group) {
            foreach ($group['contacts'] as $contact) {
                $tagIds = array_column($contact['tags'], 'id');
                $this->assertContains($tag->id, $tagIds);
            }
        }
    }

    /**
     * Test grouping contacts by position with tag filter
     */
    public function test_group_contacts_by_position_with_tag_filter()
    {
        // Get a tag ID
        $tag = Tag::where('name', 'Client')->first();
        
        $response = $this->getJson(route('search.group-by-position', [
            'tags' => [$tag->id]
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $groups = $response->json('groups');
        
        // Check that all contacts in all groups have the Client tag
        foreach ($groups as $group) {
            foreach ($group['contacts'] as $contact) {
                $tagIds = array_column($contact['tags'], 'id');
                $this->assertContains($tag->id, $tagIds);
            }
        }
    }

    /**
     * Test grouping contacts by company with multiple filters
     */
    public function test_group_contacts_by_company_with_multiple_filters()
    {
        // Get a tag ID
        $tag = Tag::where('name', 'VIP')->first();
        
        // Search for Developer contacts with VIP tag grouped by company
        $response = $this->getJson(route('search.group-by-company', [
            'query' => 'Developer',
            'tags' => [$tag->id]
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $groups = $response->json('groups');
        
        // Check that all contacts in all groups have "Developer" in position and VIP tag
        foreach ($groups as $group) {
            foreach ($group['contacts'] as $contact) {
                $this->assertStringContainsString('Developer', $contact['position']);
                
                $tagIds = array_column($contact['tags'], 'id');
                $this->assertContains($tag->id, $tagIds);
            }
        }
    }

    /**
     * Test grouping contacts by position with company filter
     */
    public function test_group_contacts_by_position_with_company_filter()
    {
        // Group by position but filter by company
        $response = $this->getJson(route('search.group-by-position', [
            'company' => 'ABC Corporation'
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $groups = $response->json('groups');
        
        // Check that all contacts in all groups are from ABC Corporation
        foreach ($groups as $group) {
            foreach ($group['contacts'] as $contact) {
                $this->assertEquals('ABC Corporation', $contact['company']);
            }
        }
    }

    /**
     * Test grouping contacts by company with position filter
     */
    public function test_group_contacts_by_company_with_position_filter()
    {
        // Group by company but filter by position
        $response = $this->getJson(route('search.group-by-company', [
            'position' => 'Senior Developer'
        ]));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $groups = $response->json('groups');
        
        // Check that all contacts in all groups have Senior Developer position
        foreach ($groups as $group) {
            foreach ($group['contacts'] as $contact) {
                $this->assertEquals('Senior Developer', $contact['position']);
            }
        }
    }
}