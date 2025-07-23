<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class SearchService
{
    /**
     * Search contacts by various criteria with real-time filtering.
     */
    public function searchContacts(string $query = '', array $filters = []): Collection
    {
        // Extract tag IDs from filters
        $tagIds = !empty($filters['tags']) && is_array($filters['tags']) ? $filters['tags'] : [];
        
        // Use the enhanced multi-criteria search
        return $this->multiCriteriaSearch($query, $tagIds, $filters);
    }

    /**
     * Search contacts by first name.
     */
    public function searchByFirstName(string $firstName): Collection
    {
        return Contact::with(['tags', 'creator'])
                     ->where('first_name', 'like', "%{$firstName}%")
                     ->orderBy('first_name')
                     ->orderBy('last_name')
                     ->get();
    }

    /**
     * Search contacts by last name.
     */
    public function searchByLastName(string $lastName): Collection
    {
        return Contact::with(['tags', 'creator'])
                     ->where('last_name', 'like', "%{$lastName}%")
                     ->orderBy('last_name')
                     ->orderBy('first_name')
                     ->get();
    }

    /**
     * Search contacts by company.
     */
    public function searchByCompany(string $company): Collection
    {
        return Contact::with(['tags', 'creator'])
                     ->where('company', 'like', "%{$company}%")
                     ->orderBy('company')
                     ->orderBy('last_name')
                     ->orderBy('first_name')
                     ->get();
    }

    /**
     * Search contacts by position.
     */
    public function searchByPosition(string $position): Collection
    {
        return Contact::with(['tags', 'creator'])
                     ->where('position', 'like', "%{$position}%")
                     ->orderBy('position')
                     ->orderBy('last_name')
                     ->orderBy('first_name')
                     ->get();
    }

    /**
     * Group contacts by company.
     */
    public function groupByCompany(Collection $contacts): SupportCollection
    {
        return $contacts->groupBy('company')
                       ->map(function ($companyContacts, $company) {
                           return [
                               'company' => $company,
                               'count' => $companyContacts->count(),
                               'contacts' => $companyContacts->sortBy('last_name')
                           ];
                       })
                       ->sortBy('company');
    }

    /**
     * Group contacts by position.
     */
    public function groupByPosition(Collection $contacts): SupportCollection
    {
        return $contacts->groupBy('position')
                       ->map(function ($positionContacts, $position) {
                           return [
                               'position' => $position,
                               'count' => $positionContacts->count(),
                               'contacts' => $positionContacts->sortBy('last_name')
                           ];
                       })
                       ->sortBy('position');
    }

    /**
     * Search contacts by tags.
     */
    public function searchByTags(array $tagIds): Collection
    {
        if (empty($tagIds)) {
            return collect();
        }

        return Contact::with(['tags', 'creator'])
                     ->whereHas('tags', function ($q) use ($tagIds) {
                         $q->whereIn('tags.id', $tagIds);
                     })
                     ->orderBy('last_name')
                     ->orderBy('first_name')
                     ->get();
    }

    /**
     * Search contacts by tag names.
     */
    public function searchByTagNames(array $tagNames): Collection
    {
        if (empty($tagNames)) {
            return collect();
        }

        return Contact::with(['tags', 'creator'])
                     ->whereHas('tags', function ($q) use ($tagNames) {
                         $q->whereIn('tags.name', $tagNames);
                     })
                     ->orderBy('last_name')
                     ->orderBy('first_name')
                     ->get();
    }

    /**
     * Multi-criteria search combining text search with tag filtering.
     * Optimized with eager loading, query caching, and pagination.
     */
    public function multiCriteriaSearch(string $query = '', array $tagIds = [], array $filters = []): Collection
    {
        // Generate a cache key based on search parameters
        $cacheKey = 'search_' . md5($query . json_encode($tagIds) . json_encode($filters));
        
        // Try to get results from cache first
        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }
        
        // Optimize eager loading by specifying exactly what we need
        $queryBuilder = Contact::with([
            'tags:id,name,color', // Only select needed tag fields
            'creator:id,name,email' // Only select needed creator fields
        ]);

        // Apply text search across multiple fields
        if (!empty($query)) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('company', 'like', "%{$query}%")
                  ->orWhere('position', 'like', "%{$query}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"])
                  // Also search in tag names
                  ->orWhereHas('tags', function ($tagQuery) use ($query) {
                      $tagQuery->where('name', 'like', "%{$query}%");
                  });
            });
        }

        // Apply tag filter with configurable search mode
        if (!empty($tagIds) && is_array($tagIds)) {
            $tagSearchMode = $filters['tag_search_mode'] ?? 'any';
            
            if ($tagSearchMode === 'all') {
                // Must have ALL specified tags (AND logic)
                foreach ($tagIds as $tagId) {
                    $queryBuilder->whereHas('tags', function ($q) use ($tagId) {
                        $q->where('tags.id', $tagId);
                    });
                }
            } else {
                // Must have ANY of the specified tags (OR logic)
                $queryBuilder->whereHas('tags', function ($q) use ($tagIds) {
                    $q->whereIn('tags.id', $tagIds);
                });
            }
        }

        // Apply company filter
        if (!empty($filters['company'])) {
            $queryBuilder->where('company', $filters['company']);
        }

        // Apply position filter
        if (!empty($filters['position'])) {
            $queryBuilder->where('position', $filters['position']);
        }

        // Get results with limit for performance
        $results = $queryBuilder->orderBy('last_name')
                               ->orderBy('first_name')
                               ->limit(50)
                               ->get();
        
        // Cache results for 5 minutes
        cache()->put($cacheKey, $results, now()->addMinutes(5));
        
        return $results;
    }

    /**
     * Get search suggestions for autocomplete functionality.
     * Optimized with caching and query optimization.
     */
    public function getSearchSuggestions(string $query): array
    {
        // Generate a cache key based on the query
        $cacheKey = 'suggestions_' . md5($query);
        
        // Try to get suggestions from cache first
        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }
        
        $suggestions = [];

        // Get name suggestions - optimized query
        $nameContacts = Contact::select('first_name', 'last_name')
                              ->where(function ($q) use ($query) {
                                  $q->where('first_name', 'like', "%{$query}%")
                                    ->orWhere('last_name', 'like', "%{$query}%")
                                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                              })
                              ->distinct()
                              ->limit(5)
                              ->get();

        foreach ($nameContacts as $contact) {
            $suggestions[] = [
                'type' => 'name',
                'value' => $contact->first_name . ' ' . $contact->last_name,
                'label' => $contact->first_name . ' ' . $contact->last_name
            ];
        }

        // Get company suggestions - optimized query
        $companies = Contact::select('company')
                           ->where('company', 'like', "%{$query}%")
                           ->distinct()
                           ->limit(5)
                           ->pluck('company');

        foreach ($companies as $company) {
            $suggestions[] = [
                'type' => 'company',
                'value' => $company,
                'label' => $company . ' (firma)'
            ];
        }

        // Get position suggestions - optimized query
        $positions = Contact::select('position')
                           ->where('position', 'like', "%{$query}%")
                           ->distinct()
                           ->limit(5)
                           ->pluck('position');

        foreach ($positions as $position) {
            $suggestions[] = [
                'type' => 'position',
                'value' => $position,
                'label' => $position . ' (stanowisko)'
            ];
        }

        // Get tag suggestions - optimized query
        $tags = \App\Models\Tag::select('id', 'name', 'color')
                              ->where('name', 'like', "%{$query}%")
                              ->distinct()
                              ->limit(5)
                              ->get();

        foreach ($tags as $tag) {
            $suggestions[] = [
                'type' => 'tag',
                'value' => $tag->name,
                'label' => $tag->name . ' (tag)',
                'id' => $tag->id,
                'color' => $tag->color
            ];
        }

        $result = array_slice($suggestions, 0, 15); // Increased limit to accommodate tags
        
        // Cache suggestions for 5 minutes
        cache()->put($cacheKey, $result, now()->addMinutes(5));
        
        return $result;
    }

    /**
     * Get advanced search results with multiple criteria.
     */
    public function advancedSearch(array $criteria): Collection
    {
        $queryBuilder = Contact::with(['tags', 'creator']);

        // Handle name search
        if (!empty($criteria['name'])) {
            $queryBuilder->where(function ($q) use ($criteria) {
                $name = $criteria['name'];
                $q->where('first_name', 'like', "%{$name}%")
                  ->orWhere('last_name', 'like', "%{$name}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$name}%"]);
            });
        }

        // Handle email search
        if (!empty($criteria['email'])) {
            $queryBuilder->where('email', 'like', "%{$criteria['email']}%");
        }

        // Handle company search
        if (!empty($criteria['company'])) {
            $queryBuilder->where('company', 'like', "%{$criteria['company']}%");
        }

        // Handle position search
        if (!empty($criteria['position'])) {
            $queryBuilder->where('position', 'like', "%{$criteria['position']}%");
        }

        // Enhanced tag search - support both tag IDs and tag names
        if (!empty($criteria['tags']) && is_array($criteria['tags'])) {
            // Check if we have tag IDs or tag names
            $firstTag = reset($criteria['tags']);
            if (is_numeric($firstTag)) {
                // Tag IDs - require ALL tags (AND logic)
                foreach ($criteria['tags'] as $tagId) {
                    $queryBuilder->whereHas('tags', function ($q) use ($tagId) {
                        $q->where('tags.id', $tagId);
                    });
                }
            } else {
                // Tag names - require ALL tags (AND logic)
                foreach ($criteria['tags'] as $tagName) {
                    $queryBuilder->whereHas('tags', function ($q) use ($tagName) {
                        $q->where('tags.name', $tagName);
                    });
                }
            }
        }

        // Handle tag search mode - ANY vs ALL
        if (!empty($criteria['tag_search_mode']) && $criteria['tag_search_mode'] === 'any' && !empty($criteria['tags'])) {
            // Override the AND logic above with OR logic for "any" mode
            $queryBuilder = Contact::with(['tags', 'creator']);
            
            // Re-apply other filters
            if (!empty($criteria['name'])) {
                $queryBuilder->where(function ($q) use ($criteria) {
                    $name = $criteria['name'];
                    $q->where('first_name', 'like', "%{$name}%")
                      ->orWhere('last_name', 'like', "%{$name}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$name}%"]);
                });
            }
            if (!empty($criteria['email'])) {
                $queryBuilder->where('email', 'like', "%{$criteria['email']}%");
            }
            if (!empty($criteria['company'])) {
                $queryBuilder->where('company', 'like', "%{$criteria['company']}%");
            }
            if (!empty($criteria['position'])) {
                $queryBuilder->where('position', 'like', "%{$criteria['position']}%");
            }
            
            // Apply ANY tag logic
            $firstTag = reset($criteria['tags']);
            if (is_numeric($firstTag)) {
                $queryBuilder->whereHas('tags', function ($q) use ($criteria) {
                    $q->whereIn('tags.id', $criteria['tags']);
                });
            } else {
                $queryBuilder->whereHas('tags', function ($q) use ($criteria) {
                    $q->whereIn('tags.name', $criteria['tags']);
                });
            }
        }

        return $queryBuilder->orderBy('last_name')
                           ->orderBy('first_name')
                           ->get();
    }

    /**
     * Get all available tags for search filters with caching.
     */
    public function getAvailableTags(): Collection
    {
        return cache()->remember('available_tags', now()->addHours(1), function () {
            return \App\Models\Tag::select('id', 'name', 'color')
                                 ->withCount('contacts')
                                 ->orderBy('name')
                                 ->get();
        });
    }

    /**
     * Get contacts that have any of the specified tags.
     */
    public function getContactsWithAnyTags(array $tagIds): Collection
    {
        if (empty($tagIds)) {
            return collect();
        }

        return Contact::with(['tags', 'creator'])
                     ->whereHas('tags', function ($q) use ($tagIds) {
                         $q->whereIn('tags.id', $tagIds);
                     })
                     ->orderBy('last_name')
                     ->orderBy('first_name')
                     ->get();
    }

    /**
     * Get contacts that have all of the specified tags.
     */
    public function getContactsWithAllTags(array $tagIds): Collection
    {
        if (empty($tagIds)) {
            return collect();
        }

        $queryBuilder = Contact::with(['tags', 'creator']);
        
        // Add a whereHas for each tag to ensure ALL tags are present
        foreach ($tagIds as $tagId) {
            $queryBuilder->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        return $queryBuilder->orderBy('last_name')
                           ->orderBy('first_name')
                           ->get();
    }
}