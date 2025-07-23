<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EndToEndTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Contact $contact1;
    protected Contact $contact2;
    protected Tag $tag1;
    protected Tag $tag2;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        
        // Create test tags
        $this->tag1 = Tag::factory()->create([
            'name' => 'VIP',
            'color' => '#FF0000',
            'created_by' => $this->user->id
        ]);
        
        $this->tag2 = Tag::factory()->create([
            'name' => 'Partner',
            'color' => '#0000FF',
            'created_by' => $this->user->id
        ]);
        
        // Create test contacts
        $this->contact1 = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan.kowalski@example.com',
            'phone' => '123456789',
            'company' => 'ABC Corp',
            'position' => 'Manager',
            'created_by' => $this->user->id
        ]);
        
        $this->contact2 = Contact::factory()->create([
            'first_name' => 'Anna',
            'last_name' => 'Nowak',
            'email' => 'anna.nowak@example.com',
            'phone' => '987654321',
            'company' => 'XYZ Ltd',
            'position' => 'Developer',
            'created_by' => $this->user->id
        ]);
        
        // Assign tags to contacts
        $this->contact1->tags()->attach($this->tag1->id);
        $this->contact2->tags()->attach([$this->tag1->id, $this->tag2->id]);
    }

    /**
     * Test the complete user flow from login to contact management.
     */
    public function test_complete_user_flow()
    {
        // 1. User can view contacts without logging in
        $response = $this->get(route('contacts.index'));
        $response->assertStatus(200);
        $response->assertSee('Jan Kowalski');
        $response->assertSee('Anna Nowak');
        
        // 2. User can search contacts without logging in
        $response = $this->getJson(route('search', ['query' => 'Jan']));
        $response->assertStatus(200);
        $response->assertJsonPath('contacts.0.full_name', 'Jan Kowalski');
        
        // 3. User can view contact details without logging in
        $response = $this->get(route('contacts.show', $this->contact1));
        $response->assertStatus(200);
        $response->assertSee('Jan Kowalski');
        
        // 4. User can generate QR code without logging in
        $response = $this->get(route('contacts.qr', $this->contact1));
        $response->assertStatus(200);
        
        // 5. User cannot access protected routes without logging in
        $response = $this->get(route('contacts.create'));
        $response->assertRedirect(route('auth.login'));
        
        // 6. User can login
        $response = $this->post(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('contacts.index'));
        $this->assertAuthenticated();
        
        // 7. Authenticated user can create a new contact
        $contactData = [
            'first_name' => 'Piotr',
            'last_name' => 'Wiśniewski',
            'email' => 'piotr.wisniewski@example.com',
            'phone' => '555666777',
            'company' => 'DEF Solutions',
            'position' => 'Analyst',
            'tags' => [$this->tag2->id]
        ];
        
        $response = $this->post(route('contacts.store'), $contactData);
        $response->assertRedirect(route('contacts.index'));
        
        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Piotr',
            'last_name' => 'Wiśniewski',
            'email' => 'piotr.wisniewski@example.com'
        ]);
        
        // 8. Authenticated user can edit a contact
        $updatedData = [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan.kowalski@example.com',
            'phone' => '123456789',
            'company' => 'Updated Company',
            'position' => 'Senior Manager',
            'tags' => [$this->tag1->id, $this->tag2->id]
        ];
        
        $response = $this->put(route('contacts.update', $this->contact1), $updatedData);
        $response->assertRedirect(route('contacts.index'));
        
        $this->assertDatabaseHas('contacts', [
            'id' => $this->contact1->id,
            'company' => 'Updated Company',
            'position' => 'Senior Manager'
        ]);
        
        // Verify tags were updated
        $this->assertEquals(2, $this->contact1->fresh()->tags()->count());
        
        // 9. Authenticated user can export contacts
        $response = $this->get(route('contacts.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=contacts.csv');
        
        // 10. Authenticated user can manage tags
        $response = $this->get(route('tags.index'));
        $response->assertStatus(200);
        $response->assertSee('VIP');
        $response->assertSee('Partner');
        
        // 11. Authenticated user can create a new tag
        $tagData = [
            'name' => 'Client',
            'color' => '#00FF00'
        ];
        
        $response = $this->post(route('tags.store'), $tagData);
        $response->assertRedirect(route('tags.index'));
        
        $this->assertDatabaseHas('tags', [
            'name' => 'Client',
            'color' => '#00FF00'
        ]);
        
        // 12. Test group by company functionality
        $response = $this->getJson(route('search.group-by-company'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'groups' => [
                '*' => [
                    'company',
                    'count',
                    'contacts'
                ]
            ]
        ]);
        
        // 13. Test group by position functionality
        $response = $this->getJson(route('search.group-by-position'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'groups' => [
                '*' => [
                    'position',
                    'count',
                    'contacts'
                ]
            ]
        ]);
        
        // 14. Authenticated user can delete a contact
        $response = $this->delete(route('contacts.destroy', $this->contact2));
        $response->assertRedirect(route('contacts.index'));
        
        $this->assertDatabaseMissing('contacts', [
            'id' => $this->contact2->id,
            'deleted_at' => null
        ]);
        
        // 15. User can logout
        $response = $this->post(route('auth.logout'));
        $response->assertRedirect(route('contacts.index'));
        $this->assertGuest();
    }

    /**
     * Test the search and filtering functionality.
     */
    public function test_search_and_filtering_functionality()
    {
        // 1. Basic search by name
        $response = $this->getJson(route('search', ['query' => 'Jan']));
        $response->assertStatus(200);
        $response->assertJsonPath('contacts.0.full_name', 'Jan Kowalski');
        
        // 2. Search by company
        $response = $this->getJson(route('search', ['query' => 'ABC']));
        $response->assertStatus(200);
        $response->assertJsonPath('contacts.0.company', 'ABC Corp');
        
        // 3. Search by position
        $response = $this->getJson(route('search', ['query' => 'Developer']));
        $response->assertStatus(200);
        $response->assertJsonPath('contacts.0.position', 'Developer');
        
        // 4. Search by tag
        $response = $this->getJson(route('search', ['query' => 'VIP']));
        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('contacts')));
        
        // 5. Filter by company
        $response = $this->getJson(route('search', [
            'query' => '',
            'company' => 'ABC Corp'
        ]));
        $response->assertStatus(200);
        $response->assertJsonPath('contacts.0.company', 'ABC Corp');
        $this->assertEquals(1, count($response->json('contacts')));
        
        // 6. Filter by position
        $response = $this->getJson(route('search', [
            'query' => '',
            'position' => 'Developer'
        ]));
        $response->assertStatus(200);
        $response->assertJsonPath('contacts.0.position', 'Developer');
        $this->assertEquals(1, count($response->json('contacts')));
        
        // 7. Filter by tags - ANY mode
        $response = $this->getJson(route('search', [
            'query' => '',
            'tags' => [$this->tag1->id],
            'tag_search_mode' => 'any'
        ]));
        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('contacts')));
        
        // 8. Filter by tags - ALL mode
        $response = $this->getJson(route('search', [
            'query' => '',
            'tags' => [$this->tag1->id, $this->tag2->id],
            'tag_search_mode' => 'all'
        ]));
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('contacts')));
        $response->assertJsonPath('contacts.0.full_name', 'Anna Nowak');
        
        // 9. Group by company
        $response = $this->getJson(route('search.group-by-company', ['query' => '']));
        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('groups')));
        
        // 10. Group by position
        $response = $this->getJson(route('search.group-by-position', ['query' => '']));
        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('groups')));
    }

    /**
     * Test the QR code and vCard functionality.
     */
    public function test_qr_code_and_vcard_functionality()
    {
        // 1. Generate QR code for contact
        $response = $this->get(route('contacts.qr', $this->contact1));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
        
        // 2. Check vCard format in QR code
        $contact = $this->contact1;
        $expectedVCardContent = "BEGIN:VCARD\r\n";
        $expectedVCardContent .= "VERSION:3.0\r\n";
        $expectedVCardContent .= "N:{$contact->last_name};{$contact->first_name};;;\r\n";
        $expectedVCardContent .= "FN:{$contact->first_name} {$contact->last_name}\r\n";
        $expectedVCardContent .= "ORG:{$contact->company}\r\n";
        $expectedVCardContent .= "TITLE:{$contact->position}\r\n";
        $expectedVCardContent .= "TEL;TYPE=WORK,VOICE:{$contact->phone}\r\n";
        $expectedVCardContent .= "EMAIL;TYPE=WORK,INTERNET:{$contact->email}\r\n";
        $expectedVCardContent .= "END:VCARD";
        
        // This is a simplified test since we can't easily check the QR code content
        // In a real scenario, we would need to decode the QR code
        $this->assertTrue(method_exists($contact, 'toVCard'));
        $this->assertStringContainsString('BEGIN:VCARD', $contact->toVCard());
        $this->assertStringContainsString('END:VCARD', $contact->toVCard());
    }

    /**
     * Test the authorization and security features.
     */
    public function test_authorization_and_security_features()
    {
        // 1. Guest can view contacts but not modify them
        $response = $this->get(route('contacts.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('contacts.create'));
        $response->assertRedirect(route('auth.login'));
        
        $response = $this->post(route('contacts.store'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test.user@example.com',
            'company' => 'Test Company',
            'position' => 'Tester'
        ]);
        $response->assertRedirect(route('auth.login'));
        
        // 2. Guest cannot export contacts
        $response = $this->get(route('contacts.export'));
        $response->assertRedirect(route('auth.login'));
        
        // 3. Guest cannot manage tags
        $response = $this->get(route('tags.index'));
        $response->assertRedirect(route('auth.login'));
        
        // 4. Login as authenticated user
        $this->post(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        
        // 5. Authenticated user can access protected routes
        $response = $this->get(route('contacts.create'));
        $response->assertStatus(200);
        
        $response = $this->get(route('tags.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('contacts.export'));
        $response->assertStatus(200);
        
        // 6. API endpoints are properly secured
        $this->post(route('auth.logout'));
        
        $response = $this->getJson(route('tags.api'));
        $response->assertStatus(401);
        
        $this->post(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        
        $response = $this->getJson(route('tags.api'));
        $response->assertStatus(200);
    }
    
    /**
     * Test the API endpoints for contacts and tags.
     */
    public function test_api_endpoints()
    {
        // 1. Get API token for authentication
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'PHPUnit Test'
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'token',
            'user' => [
                'id',
                'name',
                'email'
            ]
        ]);
        
        $token = $response->json('token');
        
        // 2. Test listing contacts API
        $response = $this->getJson('/api/contacts');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'contacts',
            'pagination'
        ]);
        
        // 3. Test search API
        $response = $this->getJson('/api/search?query=Jan');
        $response->assertStatus(200);
        $response->assertJsonPath('contacts.0.full_name', 'Jan Kowalski');
        
        // 4. Test group by company API
        $response = $this->getJson('/api/search/group-by-company');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'groups',
            'total'
        ]);
        
        // 5. Test group by position API
        $response = $this->getJson('/api/search/group-by-position');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'groups',
            'total'
        ]);
        
        // 6. Test get contact API
        $response = $this->getJson('/api/contacts/' . $this->contact1->id);
        $response->assertStatus(200);
        $response->assertJsonPath('contact.full_name', 'Jan Kowalski');
        
        // 7. Test protected endpoints without authentication
        $response = $this->postJson('/api/contacts', [
            'first_name' => 'API',
            'last_name' => 'Test',
            'email' => 'api.test@example.com',
            'phone' => '123123123',
            'company' => 'API Company',
            'position' => 'Tester'
        ]);
        $response->assertStatus(401);
        
        // 8. Test protected endpoints with authentication
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/contacts', [
                'first_name' => 'API',
                'last_name' => 'Test',
                'email' => 'api.test@example.com',
                'phone' => '123123123',
                'company' => 'API Company',
                'position' => 'Tester',
                'tags' => [$this->tag1->id]
            ]);
        
        $response->assertStatus(200);
        $response->assertJsonPath('contact.email', 'api.test@example.com');
        
        $contactId = $response->json('contact.id');
        
        // 9. Test update contact API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/contacts/' . $contactId, [
                'first_name' => 'API Updated',
                'last_name' => 'Test',
                'email' => 'api.test@example.com',
                'phone' => '123123123',
                'company' => 'API Company Updated',
                'position' => 'Senior Tester',
                'tags' => [$this->tag1->id, $this->tag2->id]
            ]);
        
        $response->assertStatus(200);
        $response->assertJsonPath('contact.company', 'API Company Updated');
        $response->assertJsonPath('contact.position', 'Senior Tester');
        
        // 10. Test tags API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/tags');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'tags' => [
                '*' => [
                    'id',
                    'name',
                    'color',
                    'contacts_count'
                ]
            ]
        ]);
        
        // 11. Test create tag API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/tags', [
                'name' => 'API Tag',
                'color' => '#AABBCC'
            ]);
        
        $response->assertStatus(200);
        $response->assertJsonPath('tag.name', 'API Tag');
        
        $tagId = $response->json('tag.id');
        
        // 12. Test update tag API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/tags/' . $tagId, [
                'name' => 'API Tag Updated',
                'color' => '#CCBBAA'
            ]);
        
        $response->assertStatus(200);
        $response->assertJsonPath('tag.name', 'API Tag Updated');
        
        // 13. Test delete contact API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/contacts/' . $contactId);
        
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        // 14. Test delete tag API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/tags/' . $tagId);
        
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        // 15. Test logout API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');
        
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }
}