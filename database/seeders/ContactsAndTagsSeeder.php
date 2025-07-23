<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ContactsAndTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
            ]
        );

        // Create demo user if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password123'),
            ]
        );

        // Create tags
        $tags = [
            ['name' => 'VIP', 'color' => '#FF0000', 'created_by' => $admin->id],
            ['name' => 'Partner', 'color' => '#0000FF', 'created_by' => $admin->id],
            ['name' => 'Client', 'color' => '#00FF00', 'created_by' => $admin->id],
            ['name' => 'Supplier', 'color' => '#FFA500', 'created_by' => $user->id],
            ['name' => 'Prospect', 'color' => '#800080', 'created_by' => $user->id],
        ];

        $tagIds = [];
        foreach ($tags as $tagData) {
            $tag = Tag::firstOrCreate(
                ['name' => $tagData['name'], 'created_by' => $tagData['created_by']],
                ['color' => $tagData['color']]
            );
            $tagIds[] = $tag->id;
        }

        // Create contacts for admin
        $adminContacts = [
            [
                'first_name' => 'Jan',
                'last_name' => 'Kowalski',
                'email' => 'jan.kowalski@example.com',
                'phone' => '123456789',
                'company' => 'ABC Corporation',
                'position' => 'CEO',
                'created_by' => $admin->id,
                'tags' => [$tagIds[0], $tagIds[1]] // VIP, Partner
            ],
            [
                'first_name' => 'Anna',
                'last_name' => 'Nowak',
                'email' => 'anna.nowak@example.com',
                'phone' => '987654321',
                'company' => 'XYZ Limited',
                'position' => 'Marketing Director',
                'created_by' => $admin->id,
                'tags' => [$tagIds[0], $tagIds[2]] // VIP, Client
            ],
            [
                'first_name' => 'Piotr',
                'last_name' => 'Wiśniewski',
                'email' => 'piotr.wisniewski@example.com',
                'phone' => '555666777',
                'company' => 'ABC Corporation',
                'position' => 'CTO',
                'created_by' => $admin->id,
                'tags' => [$tagIds[1]] // Partner
            ],
            [
                'first_name' => 'Katarzyna',
                'last_name' => 'Lewandowska',
                'email' => 'katarzyna.lewandowska@example.com',
                'phone' => '111222333',
                'company' => 'DEF Solutions',
                'position' => 'HR Manager',
                'created_by' => $admin->id,
                'tags' => [$tagIds[2]] // Client
            ],
            [
                'first_name' => 'Tomasz',
                'last_name' => 'Kamiński',
                'email' => 'tomasz.kaminski@example.com',
                'phone' => '444555666',
                'company' => 'DEF Solutions',
                'position' => 'Sales Manager',
                'created_by' => $admin->id,
                'tags' => [$tagIds[2], $tagIds[4]] // Client, Prospect
            ],
        ];

        // Create contacts for user
        $userContacts = [
            [
                'first_name' => 'Michał',
                'last_name' => 'Zieliński',
                'email' => 'michal.zielinski@example.com',
                'phone' => '777888999',
                'company' => 'GHI Industries',
                'position' => 'Project Manager',
                'created_by' => $user->id,
                'tags' => [$tagIds[3]] // Supplier
            ],
            [
                'first_name' => 'Agnieszka',
                'last_name' => 'Szymańska',
                'email' => 'agnieszka.szymanska@example.com',
                'phone' => '222333444',
                'company' => 'JKL Group',
                'position' => 'Financial Analyst',
                'created_by' => $user->id,
                'tags' => [$tagIds[4]] // Prospect
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Woźniak',
                'email' => 'robert.wozniak@example.com',
                'phone' => '666777888',
                'company' => 'MNO Services',
                'position' => 'IT Specialist',
                'created_by' => $user->id,
                'tags' => [$tagIds[3], $tagIds[4]] // Supplier, Prospect
            ],
            [
                'first_name' => 'Magdalena',
                'last_name' => 'Dąbrowska',
                'email' => 'magdalena.dabrowska@example.com',
                'phone' => '999000111',
                'company' => 'PQR Consulting',
                'position' => 'Senior Consultant',
                'created_by' => $user->id,
                'tags' => [$tagIds[2], $tagIds[3]] // Client, Supplier
            ],
            [
                'first_name' => 'Krzysztof',
                'last_name' => 'Kozłowski',
                'email' => 'krzysztof.kozlowski@example.com',
                'phone' => '333444555',
                'company' => 'STU Technologies',
                'position' => 'Software Developer',
                'created_by' => $user->id,
                'tags' => [$tagIds[1], $tagIds[3]] // Partner, Supplier
            ],
        ];

        // Create all contacts
        $allContacts = array_merge($adminContacts, $userContacts);
        foreach ($allContacts as $contactData) {
            $contactTags = $contactData['tags'];
            unset($contactData['tags']);

            // Check if contact already exists
            $contact = Contact::firstOrCreate(
                ['email' => $contactData['email']],
                $contactData
            );

            // Sync tags
            $contact->tags()->sync($contactTags);
        }

        $this->command->info('Sample contacts and tags created successfully!');
    }
}