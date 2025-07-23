<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_has_fillable_attributes()
    {
        $tag = new Tag();
        
        $expected = [
            'name',
            'color',
            'created_by',
        ];
        
        $this->assertEquals($expected, $tag->getFillable());
    }

    public function test_tag_belongs_to_creator()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['created_by' => $user->id]);
        
        $this->assertInstanceOf(User::class, $tag->creator);
        $this->assertEquals($user->id, $tag->creator->id);
    }

    public function test_tag_can_have_many_contacts()
    {
        $tag = Tag::factory()->create();
        $contact1 = Contact::factory()->create();
        $contact2 = Contact::factory()->create();
        
        $tag->contacts()->attach([$contact1->id, $contact2->id]);
        
        $this->assertCount(2, $tag->contacts);
        $this->assertTrue($tag->contacts->contains($contact1));
        $this->assertTrue($tag->contacts->contains($contact2));
    }

    public function test_tag_contacts_relationship_includes_pivot_timestamp()
    {
        $tag = Tag::factory()->create();
        $contact = Contact::factory()->create();
        
        $tag->contacts()->attach($contact->id);
        $tag->refresh();
        
        $this->assertNotNull($tag->contacts->first()->pivot->created_at);
    }

    public function test_tag_can_be_created_with_factory()
    {
        $tag = Tag::factory()->create();
        
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertNotNull($tag->name);
        $this->assertNotNull($tag->color);
        $this->assertNotNull($tag->created_by);
    }

    public function test_tag_creator_relationship_can_be_null()
    {
        $tag = Tag::factory()->create(['created_by' => null]);
        
        $this->assertNull($tag->creator);
    }

    public function test_tag_can_detach_contacts()
    {
        $tag = Tag::factory()->create();
        $contact = Contact::factory()->create();
        
        $tag->contacts()->attach($contact->id);
        $this->assertCount(1, $tag->contacts);
        
        $tag->contacts()->detach($contact->id);
        $tag->refresh();
        $this->assertCount(0, $tag->contacts);
    }

    public function test_tag_can_be_created_with_custom_attributes()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create([
            'name' => 'VIP Client',
            'color' => '#FF0000',
            'created_by' => $user->id
        ]);
        
        $this->assertEquals('VIP Client', $tag->name);
        $this->assertEquals('#FF0000', $tag->color);
        $this->assertEquals($user->id, $tag->created_by);
    }

    public function test_tag_name_can_contain_special_characters()
    {
        $tag = Tag::factory()->create([
            'name' => 'Ważny Klient & Partner'
        ]);
        
        $this->assertEquals('Ważny Klient & Partner', $tag->name);
    }

    public function test_tag_color_accepts_hex_format()
    {
        $tag = Tag::factory()->create([
            'color' => '#3B82F6'
        ]);
        
        $this->assertEquals('#3B82F6', $tag->color);
    }

    public function test_multiple_contacts_can_share_same_tag()
    {
        $tag = Tag::factory()->create(['name' => 'Important']);
        $contact1 = Contact::factory()->create();
        $contact2 = Contact::factory()->create();
        $contact3 = Contact::factory()->create();
        
        $tag->contacts()->attach([$contact1->id, $contact2->id, $contact3->id]);
        
        $this->assertCount(3, $tag->contacts);
        $this->assertTrue($tag->contacts->pluck('id')->contains($contact1->id));
        $this->assertTrue($tag->contacts->pluck('id')->contains($contact2->id));
        $this->assertTrue($tag->contacts->pluck('id')->contains($contact3->id));
    }

    public function test_tag_relationship_is_bidirectional()
    {
        $tag = Tag::factory()->create();
        $contact = Contact::factory()->create();
        
        // Attach from tag side
        $tag->contacts()->attach($contact->id);
        
        // Verify from both sides
        $this->assertTrue($tag->contacts->contains($contact));
        $this->assertTrue($contact->fresh()->tags->contains($tag));
    }

    public function test_tag_can_sync_contacts()
    {
        $tag = Tag::factory()->create();
        $contact1 = Contact::factory()->create();
        $contact2 = Contact::factory()->create();
        $contact3 = Contact::factory()->create();
        
        // Initial sync
        $tag->contacts()->sync([$contact1->id, $contact2->id]);
        $this->assertCount(2, $tag->contacts);
        
        // Sync with different contacts
        $tag->contacts()->sync([$contact2->id, $contact3->id]);
        $tag->refresh();
        $this->assertCount(2, $tag->contacts);
        $this->assertTrue($tag->contacts->contains($contact2));
        $this->assertTrue($tag->contacts->contains($contact3));
        $this->assertFalse($tag->contacts->contains($contact1));
    }
}