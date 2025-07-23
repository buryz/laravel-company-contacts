<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_has_fillable_attributes()
    {
        $contact = new Contact();
        
        $expected = [
            'first_name',
            'last_name',
            'email',
            'phone',
            'company',
            'position',
            'created_by',
        ];
        
        $this->assertEquals($expected, $contact->getFillable());
    }

    public function test_contact_belongs_to_creator()
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['created_by' => $user->id]);
        
        $this->assertInstanceOf(User::class, $contact->creator);
        $this->assertEquals($user->id, $contact->creator->id);
    }

    public function test_contact_can_have_many_tags()
    {
        $contact = Contact::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        
        $contact->tags()->attach([$tag1->id, $tag2->id]);
        
        $this->assertCount(2, $contact->tags);
        $this->assertTrue($contact->tags->contains($tag1));
        $this->assertTrue($contact->tags->contains($tag2));
    }

    public function test_contact_tags_relationship_includes_pivot_timestamp()
    {
        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create();
        
        $contact->tags()->attach($tag->id);
        $contact->refresh();
        
        $this->assertNotNull($contact->tags->first()->pivot->created_at);
    }

    public function test_full_name_attribute_concatenates_first_and_last_name()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski'
        ]);
        
        $this->assertEquals('Jan Kowalski', $contact->full_name);
    }

    public function test_to_vcard_generates_valid_vcard_format()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan.kowalski@example.com',
            'phone' => '+48123456789',
            'company' => 'Test Company',
            'position' => 'Developer'
        ]);
        
        $vcard = $contact->toVCard();
        
        // Test basic vCard structure
        $this->assertStringStartsWith('BEGIN:VCARD', $vcard);
        $this->assertStringEndsWith("END:VCARD\r\n", $vcard);
        $this->assertStringContainsString('VERSION:3.0', $vcard);
        
        // Test contact information
        $this->assertStringContainsString('FN:Jan Kowalski', $vcard);
        $this->assertStringContainsString('N:Kowalski;Jan;;;', $vcard);
        $this->assertStringContainsString('EMAIL:jan.kowalski@example.com', $vcard);
        $this->assertStringContainsString('TEL:+48123456789', $vcard);
        $this->assertStringContainsString('ORG:Test Company', $vcard);
        $this->assertStringContainsString('TITLE:Developer', $vcard);
    }

    public function test_to_vcard_handles_missing_phone_number()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan.kowalski@example.com',
            'phone' => null,
            'company' => 'Test Company',
            'position' => 'Developer'
        ]);
        
        $vcard = $contact->toVCard();
        
        $this->assertStringNotContainsString('TEL:', $vcard);
        $this->assertStringContainsString('FN:Jan Kowalski', $vcard);
        $this->assertStringContainsString('EMAIL:jan.kowalski@example.com', $vcard);
    }

    public function test_to_vcard_handles_empty_phone_number()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan.kowalski@example.com',
            'phone' => '',
            'company' => 'Test Company',
            'position' => 'Developer'
        ]);
        
        $vcard = $contact->toVCard();
        
        $this->assertStringNotContainsString('TEL:', $vcard);
    }

    public function test_contact_can_be_created_with_factory()
    {
        $contact = Contact::factory()->create();
        
        $this->assertInstanceOf(Contact::class, $contact);
        $this->assertNotNull($contact->first_name);
        $this->assertNotNull($contact->last_name);
        $this->assertNotNull($contact->email);
        $this->assertNotNull($contact->company);
        $this->assertNotNull($contact->position);
        $this->assertNotNull($contact->created_by);
    }

    public function test_contact_creator_relationship_can_be_null()
    {
        $contact = Contact::factory()->create(['created_by' => null]);
        
        $this->assertNull($contact->creator);
    }

    public function test_contact_can_detach_tags()
    {
        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create();
        
        $contact->tags()->attach($tag->id);
        $this->assertCount(1, $contact->tags);
        
        $contact->tags()->detach($tag->id);
        $contact->refresh();
        $this->assertCount(0, $contact->tags);
    }

    public function test_contact_full_name_handles_special_characters()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Józef',
            'last_name' => 'Nowak-Kowalski'
        ]);
        
        $this->assertEquals('Józef Nowak-Kowalski', $contact->full_name);
    }

    public function test_to_vcard_escapes_special_characters_properly()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@test.com',
            'company' => 'Test & Company',
            'position' => 'Senior Developer'
        ]);
        
        $vcard = $contact->toVCard();
        
        $this->assertStringContainsString('ORG:Test & Company', $vcard);
        $this->assertStringContainsString('TITLE:Senior Developer', $vcard);
    }
}