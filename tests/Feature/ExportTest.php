<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ExportService $exportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->exportService = app(ExportService::class);
    }

    public function test_guest_cannot_export_contacts()
    {
        $response = $this->get(route('contacts.export'));
        
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_export_contacts()
    {
        // Create test contacts
        $contact1 = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'company' => 'Test Company',
            'position' => 'Developer',
            'created_by' => $this->user->id
        ]);

        $contact2 = Contact::factory()->create([
            'first_name' => 'Anna',
            'last_name' => 'Nowak',
            'email' => 'anna@example.com',
            'company' => 'Another Company',
            'position' => 'Manager',
            'created_by' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->get(route('contacts.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
        
        $content = $response->getContent();
        $this->assertStringContainsString('Jan', $content);
        $this->assertStringContainsString('Kowalski', $content);
        $this->assertStringContainsString('Anna', $content);
        $this->assertStringContainsString('Nowak', $content);
    }

    public function test_export_with_search_filter()
    {
        // Create test contacts
        Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'company' => 'Test Company',
            'created_by' => $this->user->id
        ]);

        Contact::factory()->create([
            'first_name' => 'Anna',
            'last_name' => 'Nowak',
            'email' => 'anna@example.com',
            'company' => 'Another Company',
            'created_by' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->get(route('contacts.export', ['search' => 'Jan']));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('Jan', $content);
        $this->assertStringNotContainsString('Anna', $content);
    }

    public function test_export_with_company_filter()
    {
        // Create test contacts
        Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'company' => 'Test Company',
            'created_by' => $this->user->id
        ]);

        Contact::factory()->create([
            'first_name' => 'Anna',
            'last_name' => 'Nowak',
            'company' => 'Another Company',
            'created_by' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->get(route('contacts.export', ['company' => 'Test Company']));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('Jan', $content);
        $this->assertStringContainsString('Test Company', $content);
        $this->assertStringNotContainsString('Anna', $content);
    }

    public function test_export_with_position_filter()
    {
        // Create test contacts
        Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'position' => 'Developer',
            'created_by' => $this->user->id
        ]);

        Contact::factory()->create([
            'first_name' => 'Anna',
            'last_name' => 'Nowak',
            'position' => 'Manager',
            'created_by' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->get(route('contacts.export', ['position' => 'Developer']));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('Jan', $content);
        $this->assertStringContainsString('Developer', $content);
        $this->assertStringNotContainsString('Anna', $content);
    }

    public function test_export_with_tags_filter()
    {
        // Create test tag
        $tag = Tag::factory()->create([
            'name' => 'VIP',
            'created_by' => $this->user->id
        ]);

        // Create test contacts
        $contact1 = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'created_by' => $this->user->id
        ]);
        $contact1->tags()->attach($tag);

        $contact2 = Contact::factory()->create([
            'first_name' => 'Anna',
            'last_name' => 'Nowak',
            'created_by' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)->get(route('contacts.export', ['tags' => [$tag->id]]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('Jan', $content);
        $this->assertStringContainsString('VIP', $content);
        $this->assertStringNotContainsString('Anna', $content);
    }

    public function test_export_with_multiple_filters_combined()
    {
        // Create test tags
        $tagVIP = Tag::factory()->create([
            'name' => 'VIP',
            'created_by' => $this->user->id
        ]);
        
        $tagClient = Tag::factory()->create([
            'name' => 'Client',
            'created_by' => $this->user->id
        ]);

        // Create test contacts
        $contact1 = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'company' => 'Test Company',
            'position' => 'Developer',
            'created_by' => $this->user->id
        ]);
        $contact1->tags()->attach($tagVIP);
        $contact1->tags()->attach($tagClient);

        $contact2 = Contact::factory()->create([
            'first_name' => 'Anna',
            'last_name' => 'Nowak',
            'company' => 'Test Company',
            'position' => 'Manager',
            'created_by' => $this->user->id
        ]);
        $contact2->tags()->attach($tagClient);

        $contact3 = Contact::factory()->create([
            'first_name' => 'Piotr',
            'last_name' => 'Wiśniewski',
            'company' => 'Another Company',
            'position' => 'Developer',
            'created_by' => $this->user->id
        ]);
        $contact3->tags()->attach($tagVIP);

        // Test with company and position filters
        $response = $this->actingAs($this->user)->get(route('contacts.export', [
            'company' => 'Test Company',
            'position' => 'Developer'
        ]));

        $content = $response->getContent();
        $this->assertStringContainsString('Jan', $content);
        $this->assertStringNotContainsString('Anna', $content);
        $this->assertStringNotContainsString('Piotr', $content);

        // Test with company and tag filters
        $response = $this->actingAs($this->user)->get(route('contacts.export', [
            'company' => 'Test Company',
            'tags' => [$tagVIP->id]
        ]));

        $content = $response->getContent();
        $this->assertStringContainsString('Jan', $content);
        $this->assertStringNotContainsString('Anna', $content);
        $this->assertStringNotContainsString('Piotr', $content);

        // Test with position and tag filters
        $response = $this->actingAs($this->user)->get(route('contacts.export', [
            'position' => 'Developer',
            'tags' => [$tagVIP->id]
        ]));

        $content = $response->getContent();
        $this->assertStringContainsString('Jan', $content);
        $this->assertStringContainsString('Piotr', $content);
        $this->assertStringNotContainsString('Anna', $content);
    }

    public function test_export_service_generates_correct_csv_format()
    {
        // Create test contact with tag
        $tag = Tag::factory()->create([
            'name' => 'Important',
            'created_by' => $this->user->id
        ]);

        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'phone' => '+48123456789',
            'company' => 'Test Company',
            'position' => 'Developer',
            'created_by' => $this->user->id
        ]);
        $contact->tags()->attach($tag);

        $csv = $this->exportService->exportContactsToCSV();

        // Check CSV headers
        $this->assertStringContainsString('Imię,Nazwisko,Email,Telefon,Firma,Stanowisko,Tagi', $csv);
        
        // Check contact data
        $this->assertStringContainsString('Jan,Kowalski,jan@example.com,+48123456789,"Test Company",Developer,Important', $csv);
    }

    public function test_export_service_generates_correct_filename()
    {
        $filename = $this->exportService->generateCSVFilename();
        
        $this->assertStringStartsWith('kontakty_', $filename);
        $this->assertStringEndsWith('.csv', $filename);
        $this->assertMatchesRegularExpression('/kontakty_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.csv/', $filename);
    }

    public function test_export_includes_utf8_bom_for_excel_compatibility()
    {
        Contact::factory()->create([
            'first_name' => 'Ąćęłńóśźż',
            'last_name' => 'Testowy',
            'created_by' => $this->user->id
        ]);

        $csv = $this->exportService->exportContactsToCSV();
        
        // Check for UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        
        // Check for Polish characters
        $this->assertStringContainsString('Ąćęłńóśźż', $csv);
    }

    public function test_export_respects_active_filters_from_request()
    {
        // Create test contacts with different attributes
        Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'company' => 'ABC Corp',
            'position' => 'Developer',
            'created_by' => $this->user->id
        ]);

        Contact::factory()->create([
            'first_name' => 'Anna',
            'last_name' => 'Nowak',
            'email' => 'anna@example.com',
            'company' => 'XYZ Ltd',
            'position' => 'Manager',
            'created_by' => $this->user->id
        ]);

        // Test with search filter
        $response = $this->actingAs($this->user)
            ->withSession(['_previous' => ['url' => route('contacts.index', ['search' => 'Jan'])]])
            ->get(route('contacts.export', ['search' => 'Jan']));

        $content = $response->getContent();
        $this->assertStringContainsString('Jan', $content);
        $this->assertStringNotContainsString('Anna', $content);

        // Test with company filter
        $response = $this->actingAs($this->user)
            ->withSession(['_previous' => ['url' => route('contacts.index', ['company' => 'ABC Corp'])]])
            ->get(route('contacts.export', ['company' => 'ABC Corp']));

        $content = $response->getContent();
        $this->assertStringContainsString('Jan', $content);
        $this->assertStringContainsString('ABC Corp', $content);
        $this->assertStringNotContainsString('XYZ Ltd', $content);
    }
}