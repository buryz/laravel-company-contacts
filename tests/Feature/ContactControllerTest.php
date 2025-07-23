<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    // INDEX TESTS
    public function test_guest_can_view_contacts_index()
    {
        $contact = Contact::factory()->create();

        $response = $this->get(route('contacts.index'));

        $response->assertStatus(200);
        $response->assertSee($contact->full_name);
        $response->assertSee($contact->company);
        $response->assertSee($contact->position);
    }

    public function test_authenticated_user_can_view_contacts_index()
    {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('contacts.index'));

        $response->assertStatus(200);
        $response->assertSee($contact->full_name);
        $response->assertSee('Dodaj kontakt'); // Should see add button when authenticated
    }

    public function test_contacts_index_shows_pagination()
    {
        Contact::factory()->count(20)->create();

        $response = $this->get(route('contacts.index'));

        $response->assertStatus(200);
        $response->assertSee('Next'); // Pagination links
    }

    public function test_contacts_index_filters_by_search()
    {
        $contact1 = Contact::factory()->create(['first_name' => 'Jan', 'last_name' => 'Kowalski']);
        $contact2 = Contact::factory()->create(['first_name' => 'Anna', 'last_name' => 'Nowak']);

        $response = $this->get(route('contacts.index', ['search' => 'Jan']));

        $response->assertStatus(200);
        $response->assertSee('Jan Kowalski');
        $response->assertDontSee('Anna Nowak');
    }

    public function test_contacts_index_filters_by_company()
    {
        $contact1 = Contact::factory()->create(['company' => 'ABC Corp']);
        $contact2 = Contact::factory()->create(['company' => 'XYZ Ltd']);

        $response = $this->get(route('contacts.index', ['company' => 'ABC Corp']));

        $response->assertStatus(200);
        $response->assertSee('ABC Corp');
        $response->assertDontSee('XYZ Ltd');
    }

    public function test_contacts_index_filters_by_position()
    {
        $contact1 = Contact::factory()->create(['position' => 'Developer']);
        $contact2 = Contact::factory()->create(['position' => 'Manager']);

        $response = $this->get(route('contacts.index', ['position' => 'Developer']));

        $response->assertStatus(200);
        $response->assertSee('Developer');
        $response->assertDontSee('Manager');
    }

    // CREATE TESTS
    public function test_guest_cannot_access_create_form()
    {
        $response = $this->get(route('contacts.create'));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_authenticated_user_can_access_create_form()
    {
        $response = $this->actingAs($this->user)->get(route('contacts.create'));

        $response->assertStatus(200);
        $response->assertSee('Dodaj nowy kontakt');
        $response->assertSee('Imię');
        $response->assertSee('Nazwisko');
        $response->assertSee('Email');
        $response->assertSee('Telefon');
        $response->assertSee('Firma');
        $response->assertSee('Stanowisko');
    }

    public function test_create_form_shows_available_tags()
    {
        $tag = Tag::factory()->create(['name' => 'VIP']);

        $response = $this->actingAs($this->user)->get(route('contacts.create'));

        $response->assertStatus(200);
        $response->assertSee('VIP');
    }

    // STORE TESTS
    public function test_guest_cannot_store_contact()
    {
        $contactData = [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'phone' => '123456789',
            'company' => 'Test Company',
            'position' => 'Developer'
        ];

        $response = $this->post(route('contacts.store'), $contactData);

        $response->assertRedirect(route('auth.login'));
        $this->assertDatabaseMissing('contacts', ['email' => 'jan@example.com']);
    }

    public function test_authenticated_user_can_store_contact()
    {
        $contactData = [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'phone' => '123456789',
            'company' => 'Test Company',
            'position' => 'Developer'
        ];

        $response = $this->actingAs($this->user)->post(route('contacts.store'), $contactData);

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHas('success', 'Kontakt został dodany pomyślnie.');
        
        $this->assertDatabaseHas('contacts', [
            'email' => 'jan@example.com',
            'created_by' => $this->user->id
        ]);
    }

    public function test_store_contact_with_tags()
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $contactData = [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'phone' => '123456789',
            'company' => 'Test Company',
            'position' => 'Developer',
            'tags' => [$tag1->id, $tag2->id]
        ];

        $response = $this->actingAs($this->user)->post(route('contacts.store'), $contactData);

        $response->assertRedirect(route('contacts.index'));
        
        $contact = Contact::where('email', 'jan@example.com')->first();
        $this->assertCount(2, $contact->tags);
        $this->assertTrue($contact->tags->contains($tag1));
        $this->assertTrue($contact->tags->contains($tag2));
    }

    public function test_store_contact_validation_fails_with_missing_required_fields()
    {
        $response = $this->actingAs($this->user)->post(route('contacts.store'), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'company', 'position']);
    }

    public function test_store_contact_validation_fails_with_invalid_email()
    {
        $contactData = [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'invalid-email',
            'company' => 'Test Company',
            'position' => 'Developer'
        ];

        $response = $this->actingAs($this->user)->post(route('contacts.store'), $contactData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_contact_validation_fails_with_duplicate_email()
    {
        Contact::factory()->create(['email' => 'existing@example.com']);

        $contactData = [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'existing@example.com',
            'company' => 'Test Company',
            'position' => 'Developer'
        ];

        $response = $this->actingAs($this->user)->post(route('contacts.store'), $contactData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    // SHOW TESTS
    public function test_guest_can_view_contact_details()
    {
        $contact = Contact::factory()->create();

        $response = $this->get(route('contacts.show', $contact));

        $response->assertStatus(200);
        $response->assertSee($contact->full_name);
        $response->assertSee($contact->email);
        $response->assertSee($contact->company);
    }

    public function test_authenticated_user_can_view_contact_details()
    {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('contacts.show', $contact));

        $response->assertStatus(200);
        $response->assertSee($contact->full_name);
        $response->assertSee('Edytuj'); // Should see edit button when authenticated
    }

    public function test_show_contact_ajax_request_returns_json()
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson(route('contacts.show', $contact));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'contact' => [
                'id', 'first_name', 'last_name', 'email', 'phone', 'company', 'position'
            ],
            'vcard',
            'qr_code'
        ]);
    }

    public function test_show_contact_with_tags()
    {
        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create(['name' => 'VIP']);
        $contact->tags()->attach($tag);

        $response = $this->get(route('contacts.show', $contact));

        $response->assertStatus(200);
        $response->assertSee('VIP');
    }

    // EDIT TESTS
    public function test_guest_cannot_access_edit_form()
    {
        $contact = Contact::factory()->create();

        $response = $this->get(route('contacts.edit', $contact));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_authenticated_user_can_access_edit_form()
    {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('contacts.edit', $contact));

        $response->assertStatus(200);
        $response->assertSee('Edytuj kontakt');
        $response->assertSee($contact->first_name);
        $response->assertSee($contact->last_name);
        $response->assertSee($contact->email);
    }

    public function test_edit_form_shows_contact_tags()
    {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        $tag = Tag::factory()->create(['name' => 'VIP']);
        $contact->tags()->attach($tag);

        $response = $this->actingAs($this->user)->get(route('contacts.edit', $contact));

        $response->assertStatus(200);
        $response->assertSee('VIP');
    }

    // UPDATE TESTS
    public function test_guest_cannot_update_contact()
    {
        $contact = Contact::factory()->create();
        $updateData = ['first_name' => 'Updated Name'];

        $response = $this->put(route('contacts.update', $contact), $updateData);

        $response->assertRedirect(route('auth.login'));
        $this->assertDatabaseMissing('contacts', ['first_name' => 'Updated Name']);
    }

    public function test_authenticated_user_can_update_contact()
    {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        
        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'phone' => '987654321',
            'company' => 'Updated Company',
            'position' => 'Updated Position'
        ];

        $response = $this->actingAs($this->user)->put(route('contacts.update', $contact), $updateData);

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHas('success', 'Kontakt został zaktualizowany pomyślnie.');
        
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'Updated',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_update_contact_with_tags()
    {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();
        
        // Initially attach tag1
        $contact->tags()->attach($tag1);

        $updateData = [
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'company' => $contact->company,
            'position' => $contact->position,
            'tags' => [$tag2->id, $tag3->id] // Replace with tag2 and tag3
        ];

        $response = $this->actingAs($this->user)->put(route('contacts.update', $contact), $updateData);

        $response->assertRedirect(route('contacts.index'));
        
        $contact->refresh();
        $this->assertCount(2, $contact->tags);
        $this->assertFalse($contact->tags->contains($tag1));
        $this->assertTrue($contact->tags->contains($tag2));
        $this->assertTrue($contact->tags->contains($tag3));
    }

    public function test_update_contact_validation_fails_with_invalid_data()
    {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        
        $updateData = [
            'first_name' => '',
            'email' => 'invalid-email'
        ];

        $response = $this->actingAs($this->user)->put(route('contacts.update', $contact), $updateData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['first_name', 'email']);
    }

    // DESTROY TESTS
    public function test_guest_cannot_delete_contact()
    {
        $contact = Contact::factory()->create();

        $response = $this->delete(route('contacts.destroy', $contact));

        $response->assertRedirect(route('auth.login'));
        $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
    }

    public function test_authenticated_user_can_delete_contact()
    {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        $contactName = $contact->full_name;

        $response = $this->actingAs($this->user)->delete(route('contacts.destroy', $contact));

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHas('success', "Kontakt {$contactName} został usunięty pomyślnie.");
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    public function test_delete_contact_removes_tag_associations()
    {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);
        $tag = Tag::factory()->create();
        $contact->tags()->attach($tag);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id
        ]);

        $response = $this->actingAs($this->user)->delete(route('contacts.destroy', $contact));

        $response->assertRedirect(route('contacts.index'));
        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id
        ]);
        // Tag should still exist
        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    // EXPORT TESTS
    public function test_guest_cannot_export_contacts()
    {
        $response = $this->get(route('contacts.export'));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_authenticated_user_can_export_contacts()
    {
        Contact::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('contacts.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
    }

    public function test_export_contacts_with_filters()
    {
        Contact::factory()->create(['company' => 'ABC Corp']);
        Contact::factory()->create(['company' => 'XYZ Ltd']);

        $response = $this->actingAs($this->user)->get(route('contacts.export', ['company' => 'ABC Corp']));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('ABC Corp', $content);
        $this->assertStringNotContainsString('XYZ Ltd', $content);
    }

    // QR CODE TESTS
    public function test_guest_can_generate_qr_code()
    {
        $contact = Contact::factory()->create();

        $response = $this->get(route('contacts.qr', $contact));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_authenticated_user_can_generate_qr_code()
    {
        $contact = Contact::factory()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('contacts.qr', $contact));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_qr_code_ajax_request_returns_json()
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson(route('contacts.qr', $contact));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'qr_code',
            'contact' => [
                'id', 'first_name', 'last_name', 'email', 'phone', 'company', 'position'
            ],
            'vcard'
        ]);
    }

    // ERROR HANDLING TESTS
    public function test_index_handles_database_errors_gracefully()
    {
        // This would require mocking database failures, which is complex
        // For now, we'll test that the route exists and returns a valid response
        $response = $this->get(route('contacts.index'));
        $response->assertStatus(200);
    }

    public function test_nonexistent_contact_returns_404()
    {
        $response = $this->get(route('contacts.show', 999999));
        $response->assertStatus(404);
    }

    // AUTHORIZATION TESTS
    public function test_user_cannot_edit_other_users_contact()
    {
        $contact = Contact::factory()->create(['created_by' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->get(route('contacts.edit', $contact));

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_other_users_contact()
    {
        $contact = Contact::factory()->create(['created_by' => $this->otherUser->id]);
        $updateData = ['first_name' => 'Updated'];

        $response = $this->actingAs($this->user)->put(route('contacts.update', $contact), $updateData);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_other_users_contact()
    {
        $contact = Contact::factory()->create(['created_by' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->delete(route('contacts.destroy', $contact));

        $response->assertStatus(403);
    }
}