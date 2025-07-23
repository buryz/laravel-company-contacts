<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactModalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_modal_returns_contact_data_for_ajax_request()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'phone' => '+48123456789',
            'company' => 'Test Company',
            'position' => 'Developer',
            'created_by' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ])->get("/contacts/{$contact->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'contact' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'company',
                        'position',
                        'created_by',
                        'created_at',
                        'updated_at'
                    ],
                    'vcard',
                    'qr_code'
                ]);
    }

    public function test_modal_includes_tags_in_response()
    {
        $contact = Contact::factory()->create([
            'created_by' => $this->user->id
        ]);
        
        $tag1 = Tag::factory()->create([
            'name' => 'VIP',
            'color' => '#ff0000',
            'created_by' => $this->user->id
        ]);
        
        $tag2 = Tag::factory()->create([
            'name' => 'Client',
            'color' => '#00ff00',
            'created_by' => $this->user->id
        ]);
        
        $contact->tags()->attach([$tag1->id, $tag2->id]);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ])->get("/contacts/{$contact->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'contact' => [
                        'tags' => [
                            '*' => [
                                'id',
                                'name',
                                'color'
                            ]
                        ]
                    ]
                ]);
                
        $data = $response->json();
        $this->assertCount(2, $data['contact']['tags']);
        $this->assertEquals('VIP', $data['contact']['tags'][0]['name']);
        $this->assertEquals('Client', $data['contact']['tags'][1]['name']);
    }

    public function test_modal_returns_vcard_in_correct_format()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'phone' => '+48123456789',
            'company' => 'Test Company',
            'position' => 'Developer',
            'created_by' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ])->get("/contacts/{$contact->id}");

        $data = $response->json();
        
        $this->assertStringContainsString('BEGIN:VCARD', $data['vcard']);
        $this->assertStringContainsString('VERSION:3.0', $data['vcard']);
        $this->assertStringContainsString('FN:Jan Kowalski', $data['vcard']);
        $this->assertStringContainsString('N:Kowalski;Jan;;;', $data['vcard']);
        $this->assertStringContainsString('EMAIL:jan@example.com', $data['vcard']);
        $this->assertStringContainsString('TEL:+48123456789', $data['vcard']);
        $this->assertStringContainsString('ORG:Test Company', $data['vcard']);
        $this->assertStringContainsString('TITLE:Developer', $data['vcard']);
        $this->assertStringContainsString('END:VCARD', $data['vcard']);
    }

    public function test_modal_returns_qr_code_as_data_url()
    {
        $contact = Contact::factory()->create([
            'created_by' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ])->get("/contacts/{$contact->id}");

        $data = $response->json();
        
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $data['qr_code']);
        
        // Decode the base64 data to ensure it's valid SVG
        $svg = base64_decode(substr($data['qr_code'], 26)); // Remove 'data:image/svg+xml;base64,' prefix
        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_modal_handles_non_ajax_request_appropriately()
    {
        $contact = Contact::factory()->create([
            'created_by' => $this->user->id
        ]);

        // Make a non-AJAX request to the same endpoint
        $response = $this->get("/contacts/{$contact->id}");
        
        // The response should be a view, not JSON
        $response->assertStatus(200);
        $response->assertViewIs('contacts.show');
        $response->assertViewHas('contact');
    }

    public function test_modal_handles_contact_with_missing_phone()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'phone' => null, // Missing phone
            'company' => 'Test Company',
            'position' => 'Developer',
            'created_by' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ])->get("/contacts/{$contact->id}");

        $data = $response->json();
        
        // The vCard should not contain a TEL field
        $this->assertStringNotContainsString('TEL:', $data['vcard']);
    }

    public function test_modal_handles_special_characters_in_contact_data()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Łukasz',
            'last_name' => 'Żółć',
            'email' => 'lukasz@example.com',
            'phone' => '+48123456789',
            'company' => 'Firma Ćwiczeniowa',
            'position' => 'Programista',
            'created_by' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ])->get("/contacts/{$contact->id}");

        $data = $response->json();
        
        // Check that special characters are preserved in the response
        $this->assertEquals('Łukasz', $data['contact']['first_name']);
        $this->assertEquals('Żółć', $data['contact']['last_name']);
        $this->assertEquals('Firma Ćwiczeniowa', $data['contact']['company']);
        
        // Check that special characters are preserved in the vCard
        $this->assertStringContainsString('FN:Łukasz Żółć', $data['vcard']);
        $this->assertStringContainsString('ORG:Firma Ćwiczeniowa', $data['vcard']);
    }
}