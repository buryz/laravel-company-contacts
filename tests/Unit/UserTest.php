<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_fillable_attributes()
    {
        $user = new User();
        
        $expected = [
            'name',
            'email',
            'password',
        ];
        
        $this->assertEquals($expected, $user->getFillable());
    }

    public function test_user_has_hidden_attributes()
    {
        $user = new User();
        
        $expected = [
            'password',
            'remember_token',
        ];
        
        $this->assertEquals($expected, $user->getHidden());
    }

    public function test_user_has_proper_casts()
    {
        $user = new User();
        
        $casts = $user->getCasts();
        
        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
    }

    public function test_user_can_have_many_contacts()
    {
        $user = User::factory()->create();
        $contact1 = Contact::factory()->create(['created_by' => $user->id]);
        $contact2 = Contact::factory()->create(['created_by' => $user->id]);
        $contact3 = Contact::factory()->create(['created_by' => $user->id]);
        
        $this->assertCount(3, $user->contacts);
        $this->assertTrue($user->contacts->contains($contact1));
        $this->assertTrue($user->contacts->contains($contact2));
        $this->assertTrue($user->contacts->contains($contact3));
    }

    public function test_user_can_have_many_tags()
    {
        $user = User::factory()->create();
        $tag1 = Tag::factory()->create(['created_by' => $user->id]);
        $tag2 = Tag::factory()->create(['created_by' => $user->id]);
        $tag3 = Tag::factory()->create(['created_by' => $user->id]);
        
        $this->assertCount(3, $user->tags);
        $this->assertTrue($user->tags->contains($tag1));
        $this->assertTrue($user->tags->contains($tag2));
        $this->assertTrue($user->tags->contains($tag3));
    }

    public function test_user_contacts_relationship_uses_correct_foreign_key()
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['created_by' => $user->id]);
        
        $this->assertEquals($user->id, $contact->created_by);
        $this->assertTrue($user->contacts->contains($contact));
    }

    public function test_user_tags_relationship_uses_correct_foreign_key()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['created_by' => $user->id]);
        
        $this->assertEquals($user->id, $tag->created_by);
        $this->assertTrue($user->tags->contains($tag));
    }

    public function test_user_can_be_created_with_factory()
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
    }

    public function test_user_password_is_hashed()
    {
        $user = User::factory()->create();
        
        // Password should be hashed, not plain text
        $this->assertNotEquals('password', $user->password);
        $this->assertTrue(password_verify('password', $user->password));
    }

    public function test_user_email_is_unique()
    {
        $email = 'test@example.com';
        User::factory()->create(['email' => $email]);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => $email]);
    }

    public function test_user_can_have_no_contacts()
    {
        $user = User::factory()->create();
        
        $this->assertCount(0, $user->contacts);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->contacts);
    }

    public function test_user_can_have_no_tags()
    {
        $user = User::factory()->create();
        
        $this->assertCount(0, $user->tags);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->tags);
    }

    public function test_user_contacts_are_deleted_when_user_foreign_key_allows()
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['created_by' => $user->id]);
        
        $this->assertEquals($user->id, $contact->created_by);
        
        // When user is deleted, contact's created_by should be set to null
        // (based on the migration's ON DELETE SET NULL)
        $user->delete();
        $contact->refresh();
        
        $this->assertNull($contact->created_by);
    }

    public function test_user_tags_are_deleted_when_user_foreign_key_allows()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['created_by' => $user->id]);
        
        $this->assertEquals($user->id, $tag->created_by);
        
        // When user is deleted, tag's created_by should be set to null
        // (based on the migration's ON DELETE SET NULL)
        $user->delete();
        $tag->refresh();
        
        $this->assertNull($tag->created_by);
    }

    public function test_user_can_create_multiple_contacts_and_tags()
    {
        $user = User::factory()->create();
        
        // Create multiple contacts
        $contacts = Contact::factory()->count(5)->create(['created_by' => $user->id]);
        
        // Create multiple tags
        $tags = Tag::factory()->count(3)->create(['created_by' => $user->id]);
        
        $this->assertCount(5, $user->contacts);
        $this->assertCount(3, $user->tags);
        
        // Verify all contacts belong to the user
        foreach ($contacts as $contact) {
            $this->assertTrue($user->contacts->contains($contact));
        }
        
        // Verify all tags belong to the user
        foreach ($tags as $tag) {
            $this->assertTrue($user->tags->contains($tag));
        }
    }

    public function test_user_relationships_return_correct_types()
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->contacts());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->tags());
    }
}