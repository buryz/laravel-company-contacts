<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * AJAX endpoint for real-time search.
     * Optimized with pagination and reduced data transfer.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('query', '') ?? '';
            $filters = [
                'company' => $request->get('company') ?? '',
                'position' => $request->get('position') ?? '',
                'tags' => $request->get('tags', []) ?? [],
                'tag_search_mode' => $request->get('tag_search_mode', 'any')
            ];
            
            // Get page number from request or default to 1
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 15);

            // Get contacts with pagination
            $contacts = $this->searchService->searchContacts($query, $filters);
            
            // Paginate the collection manually if needed
            if ($contacts->count() > $perPage) {
                $offset = ($page - 1) * $perPage;
                $paginatedContacts = $contacts->slice($offset, $perPage);
            } else {
                $paginatedContacts = $contacts;
            }

            return response()->json([
                'success' => true,
                'contacts' => $paginatedContacts->map(function ($contact) {
                    return [
                        'id' => $contact->id,
                        'full_name' => $contact->full_name,
                        'first_name' => $contact->first_name,
                        'last_name' => $contact->last_name,
                        'email' => $contact->email,
                        'phone' => $contact->phone,
                        'company' => $contact->company,
                        'position' => $contact->position,
                        'tags' => $contact->tags->map(function ($tag) {
                            return [
                                'id' => $tag->id,
                                'name' => $tag->name,
                                'color' => $tag->color
                            ];
                        }),
                        'initials' => strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1))
                    ];
                }),
                'total' => $contacts->count(),
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($contacts->count() / $perPage)
            ]);
        } catch (\Exception $e) {
            Log::error('Błąd podczas wyszukiwania kontaktów: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Wystąpił błąd podczas wyszukiwania kontaktów.'
            ], 500);
        }
    }

    /**
     * Get search suggestions for autocomplete.
     */
    public function suggestions(Request $request): JsonResponse
    {
        try {
            $query = $request->get('query', '') ?? '';
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'suggestions' => []
                ]);
            }

            $suggestions = $this->searchService->getSearchSuggestions($query);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            Log::error('Błąd podczas pobierania podpowiedzi wyszukiwania: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Wystąpił błąd podczas pobierania podpowiedzi.'
            ], 500);
        }
    }

    /**
     * AJAX endpoint for grouped search by company.
     */
    public function groupByCompany(Request $request): JsonResponse
    {
        try {
            $query = $request->get('query', '') ?? '';
            $filters = [
                'company' => $request->get('company') ?? '',
                'position' => $request->get('position') ?? '',
                'tags' => $request->get('tags', []) ?? [],
                'tag_search_mode' => $request->get('tag_search_mode', 'any')
            ];

            $contacts = $this->searchService->searchContacts($query, $filters);
            $groupedContacts = $this->searchService->groupByCompany($contacts);

            return response()->json([
                'success' => true,
                'groups' => $groupedContacts->map(function ($group) {
                    return [
                        'company' => $group['company'],
                        'count' => $group['count'],
                        'contacts' => $group['contacts']->map(function ($contact) {
                            return [
                                'id' => $contact->id,
                                'full_name' => $contact->full_name,
                                'first_name' => $contact->first_name,
                                'last_name' => $contact->last_name,
                                'email' => $contact->email,
                                'phone' => $contact->phone,
                                'company' => $contact->company,
                                'position' => $contact->position,
                                'tags' => $contact->tags->map(function ($tag) {
                                    return [
                                        'id' => $tag->id,
                                        'name' => $tag->name,
                                        'color' => $tag->color
                                    ];
                                }),
                                'initials' => strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1))
                            ];
                        })
                    ];
                }),
                'total' => $contacts->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Błąd podczas grupowania kontaktów według firm: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Wystąpił błąd podczas grupowania kontaktów.'
            ], 500);
        }
    }

    /**
     * AJAX endpoint for grouped search by position.
     */
    public function groupByPosition(Request $request): JsonResponse
    {
        try {
            $query = $request->get('query', '') ?? '';
            $filters = [
                'company' => $request->get('company') ?? '',
                'position' => $request->get('position') ?? '',
                'tags' => $request->get('tags', []) ?? [],
                'tag_search_mode' => $request->get('tag_search_mode', 'any')
            ];

            $contacts = $this->searchService->searchContacts($query, $filters);
            $groupedContacts = $this->searchService->groupByPosition($contacts);

            return response()->json([
                'success' => true,
                'groups' => $groupedContacts->map(function ($group) {
                    return [
                        'position' => $group['position'],
                        'count' => $group['count'],
                        'contacts' => $group['contacts']->map(function ($contact) {
                            return [
                                'id' => $contact->id,
                                'full_name' => $contact->full_name,
                                'first_name' => $contact->first_name,
                                'last_name' => $contact->last_name,
                                'email' => $contact->email,
                                'phone' => $contact->phone,
                                'company' => $contact->company,
                                'position' => $contact->position,
                                'tags' => $contact->tags->map(function ($tag) {
                                    return [
                                        'id' => $tag->id,
                                        'name' => $tag->name,
                                        'color' => $tag->color
                                    ];
                                }),
                                'initials' => strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1))
                            ];
                        })
                    ];
                }),
                'total' => $contacts->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Błąd podczas grupowania kontaktów według stanowisk: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Wystąpił błąd podczas grupowania kontaktów.'
            ], 500);
        }
    }

    /**
     * AJAX endpoint for tag-based search.
     */
    public function searchByTags(Request $request): JsonResponse
    {
        try {
            $tagIds = $request->get('tag_ids', []);
            $searchMode = $request->get('search_mode', 'any'); // 'any' or 'all'
            
            if (empty($tagIds) || !is_array($tagIds)) {
                return response()->json([
                    'success' => true,
                    'contacts' => [],
                    'total' => 0
                ]);
            }

            // Use appropriate search method based on mode
            if ($searchMode === 'all') {
                $contacts = $this->searchService->getContactsWithAllTags($tagIds);
            } else {
                $contacts = $this->searchService->getContactsWithAnyTags($tagIds);
            }

            return response()->json([
                'success' => true,
                'contacts' => $contacts->map(function ($contact) {
                    return [
                        'id' => $contact->id,
                        'full_name' => $contact->full_name,
                        'first_name' => $contact->first_name,
                        'last_name' => $contact->last_name,
                        'email' => $contact->email,
                        'phone' => $contact->phone,
                        'company' => $contact->company,
                        'position' => $contact->position,
                        'tags' => $contact->tags->map(function ($tag) {
                            return [
                                'id' => $tag->id,
                                'name' => $tag->name,
                                'color' => $tag->color
                            ];
                        }),
                        'initials' => strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1))
                    ];
                }),
                'total' => $contacts->count(),
                'search_mode' => $searchMode
            ]);
        } catch (\Exception $e) {
            Log::error('Błąd podczas wyszukiwania po tagach: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Wystąpił błąd podczas wyszukiwania po tagach.'
            ], 500);
        }
    }

    /**
     * Get available tags for search filters.
     * Available to all users since tags are used for filtering public contact data.
     */
    public function getAvailableTags(Request $request): JsonResponse
    {
        try {
            $tags = $this->searchService->getAvailableTags();

            return response()->json([
                'success' => true,
                'tags' => $tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'color' => $tag->color,
                        'contacts_count' => $tag->contacts()->count()
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Błąd podczas pobierania dostępnych tagów: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Wystąpił błąd podczas pobierania tagów.'
            ], 500);
        }
    }
}