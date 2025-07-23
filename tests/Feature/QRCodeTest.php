<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use App\Services\QRCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QRCodeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected QRCodeService $qrCodeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->qrCodeService = app(QRCodeService::class);
    }

    public function test_contact_can_generate_vcard()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'phone' => '+48123456789',
            'company' => 'Test Company',
            'position' => 'Developer'
        ]);

        $vcard = $contact->toVCard();

        $this->assertStringContainsString('BEGIN:VCARD', $vcard);
        $this->assertStringContainsString('END:VCARD', $vcard);
        $this->assertStringContainsString('FN:Jan Kowalski', $vcard);
        $this->assertStringContainsString('EMAIL:jan@example.com', $vcard);
        $this->assertStringContainsString('TEL:+48123456789', $vcard);
        $this->assertStringContainsString('ORG:Test Company', $vcard);
        $this->assertStringContainsString('TITLE:Developer', $vcard);
    }

    public function test_vcard_format_is_valid_with_special_characters()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Łukasz',
            'last_name' => 'Żółć',
            'email' => 'lukasz@example.com',
            'phone' => '+48123456789',
            'company' => 'Firma Ćwiczeniowa',
            'position' => 'Programista'
        ]);

        $vcard = $contact->toVCard();

        $this->assertStringContainsString('FN:Łukasz Żółć', $vcard);
        $this->assertStringContainsString('N:Żółć;Łukasz;;;', $vcard);
        $this->assertStringContainsString('ORG:Firma Ćwiczeniowa', $vcard);
        $this->assertStringContainsString('TITLE:Programista', $vcard);
    }

    public function test_vcard_handles_missing_phone_number()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'phone' => null,
            'company' => 'Test Company',
            'position' => 'Developer'
        ]);

        $vcard = $contact->toVCard();

        $this->assertStringContainsString('BEGIN:VCARD', $vcard);
        $this->assertStringContainsString('END:VCARD', $vcard);
        $this->assertStringContainsString('FN:Jan Kowalski', $vcard);
        $this->assertStringContainsString('EMAIL:jan@example.com', $vcard);
        $this->assertStringNotContainsString('TEL:', $vcard);
    }

    public function test_qr_service_can_generate_qr_code()
    {
        $contact = Contact::factory()->create();
        $qrCode = $this->qrCodeService->generateContactQR($contact);

        $this->assertNotEmpty($qrCode);
        $this->assertStringContainsString('<svg', $qrCode);
    }

    public function test_qr_service_can_generate_data_url()
    {
        $contact = Contact::factory()->create();
        $dataUrl = $this->qrCodeService->generateContactQRDataUrl($contact);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUrl);
    }

    public function test_qr_code_contains_vcard_data()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com'
        ]);
        
        // Generate QR code
        $qrCode = $this->qrCodeService->generateContactQR($contact);
        
        // Check that the QR code contains the vCard data
        // This is a basic check since we can't easily decode the QR code in a test
        $this->assertNotEmpty($qrCode);
        $this->assertStringContainsString('<svg', $qrCode);
        
        // Generate a QR code directly from vCard string for comparison
        $vcard = $contact->toVCard();
        $qrCodeFromVCard = $this->qrCodeService->generateVCardQR($vcard);
        
        // Both QR codes should be identical since they encode the same data
        $this->assertEquals($qrCode, $qrCodeFromVCard);
    }

    public function test_contact_show_returns_qr_code_for_ajax_request()
    {
        $contact = Contact::factory()->create();

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
                        'company',
                        'position'
                    ],
                    'vcard',
                    'qr_code'
                ]);

        $data = $response->json();
        $this->assertStringContainsString('BEGIN:VCARD', $data['vcard']);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $data['qr_code']);
    }

    public function test_contact_show_with_tags_returns_tags_in_response()
    {
        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create(['name' => 'VIP', 'created_by' => $this->user->id]);
        $contact->tags()->attach($tag);

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
        $this->assertEquals('VIP', $data['contact']['tags'][0]['name']);
    }

    public function test_generate_qr_endpoint_returns_qr_code_for_ajax()
    {
        $contact = Contact::factory()->create();

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ])->get("/contacts/{$contact->id}/qr");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'qr_code',
                    'contact',
                    'vcard'
                ]);

        $data = $response->json();
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $data['qr_code']);
    }

    public function test_generate_qr_endpoint_returns_svg_for_direct_request()
    {
        $contact = Contact::factory()->create();

        $response = $this->get("/contacts/{$contact->id}/qr");

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'image/svg+xml');

        $this->assertStringContainsString('<svg', $response->getContent());
    }

    public function test_qr_code_size_and_margin_are_correct()
    {
        $contact = Contact::factory()->create();
        $qrCode = $this->qrCodeService->generateContactQR($contact);
        
        // Check that the QR code has the expected size and margin attributes
        $this->assertStringContainsString('width="200"', $qrCode);
        $this->assertStringContainsString('height="200"', $qrCode);
    }

    public function test_modal_can_be_opened_for_contact_details()
    {
        // This test simulates the AJAX request that would be made when opening a modal
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'company' => 'Test Company',
            'position' => 'Developer'
        ]);

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ])->get("/contacts/{$contact->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'contact' => [
                        'first_name' => 'Jan',
                        'last_name' => 'Kowalski',
                        'email' => 'jan@example.com',
                        'company' => 'Test Company',
                        'position' => 'Developer'
                    ]
                ]);
                
        // Check that the response contains the vCard and QR code
        $data = $response->json();
        $this->assertArrayHasKey('vcard', $data);
        $this->assertArrayHasKey('qr_code', $data);
    }
}