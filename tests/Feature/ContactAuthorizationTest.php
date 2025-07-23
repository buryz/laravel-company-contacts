<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_contacts_index()
    {
        $response = $this->get(route('contacts.index'));
        $response->assertStatus(200);
    }

    public function test_guest_can_view_individual_contact()
    {
        $contact = Contact::factory()->create();
        
        $response = $this->get(route('contacts.show', $contact));
        $response->assertStatus(200);
    }

    public function test_guest_can_generate_qr_code()
    {
        $contact = Contact::factory()->create();
        
        $response = $this->get(route('contacts.qr', $contact));
        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_create_contact_form()
    {
        $response = $this->get(route('contacts.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_edit_contact_form()
    {
        $contact = Contact::factory()->create();
        
        $response = $this->get(route('contacts.edit', $contact));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_export_contacts()
    {
        $response = $this->get(route('contacts.export'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_create_form()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('contacts.create'));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_edit_form()
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        
        $response = $this->actingAs($user)->get(route('contacts.edit', $contact));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_export_contacts()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('contacts.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}