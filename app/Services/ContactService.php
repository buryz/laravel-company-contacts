<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class ContactService
{
    /**
     * Get paginated contacts with optional filters.
     * Optimized with eager loading and pagination.
     */
    public function getPaginatedContacts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Optimize eager loading by specifying exactly what we need
        $query = Contact::with([
            'tags:id,name,color', // Only select needed tag fields
            'creator:id,name,email' // Only select needed creator fields
        ]);

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        // Apply company filter
        if (!empty($filters['company'])) {
            $query->where('company', $filters['company']);
        }

        // Apply position filter
        if (!empty($filters['position'])) {
            $query->where('position', $filters['position']);
        }

        // Apply tag filter
        if (!empty($filters['tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('tags.id', $filters['tags']);
            });
        }

        return $query->orderBy('last_name')
                    ->orderBy('first_name')
                    ->paginate($perPage);
    }

    /**
     * Create a new contact.
     */
    public function createContact(array $data, User $user): Contact
    {
        return DB::transaction(function () use ($data, $user) {
            $contactData = array_merge($data, ['created_by' => $user->id]);
            $tags = $data['tags'] ?? [];
            
            // Remove tags from contact data before creating
            unset($contactData['tags']);
            
            $contact = Contact::create($contactData);
            
            // Attach tags if provided
            if (!empty($tags)) {
                $contact->tags()->attach($tags);
            }
            
            return $contact->load(['tags', 'creator']);
        });
    }

    /**
     * Update an existing contact.
     */
    public function updateContact(Contact $contact, array $data): Contact
    {
        return DB::transaction(function () use ($contact, $data) {
            $tags = $data['tags'] ?? [];
            
            // Remove tags from contact data before updating
            unset($data['tags']);
            
            $contact->update($data);
            
            // Sync tags
            $contact->tags()->sync($tags);
            
            return $contact->load(['tags', 'creator']);
        });
    }

    /**
     * Delete a contact.
     */
    public function deleteContact(Contact $contact): bool
    {
        return DB::transaction(function () use ($contact) {
            // Detach all tags first
            $contact->tags()->detach();
            
            // Delete the contact
            return $contact->delete();
        });
    }

    /**
     * Get contact by ID with relationships.
     */
    public function getContactById(int $id): ?Contact
    {
        return Contact::with(['tags', 'creator'])->find($id);
    }

    /**
     * Generate vCard format for the contact.
     */
    public function generateVCard(Contact $contact): string
    {
        return $contact->toVCard();
    }

    /**
     * Get all unique companies with caching.
     */
    public function getUniqueCompanies(): SupportCollection
    {
        return cache()->remember('unique_companies', now()->addHours(1), function () {
            return Contact::select('company')
                         ->distinct()
                         ->orderBy('company')
                         ->pluck('company');
        });
    }

    /**
     * Get all unique positions with caching.
     */
    public function getUniquePositions(): SupportCollection
    {
        return cache()->remember('unique_positions', now()->addHours(1), function () {
            return Contact::select('position')
                         ->distinct()
                         ->orderBy('position')
                         ->pluck('position');
        });
    }


}