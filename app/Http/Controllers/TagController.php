<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    public function __construct()
    {
        // All tag management requires authentication
        $this->middleware('auth');
    }

    /**
     * Display a listing of tags.
     */
    public function index()
    {
        // Authorization check - only authenticated users can view tags management
        $this->authorize('viewAny', Tag::class);

        try {
            $tags = Tag::withCount('contacts')
                      ->orderBy('name')
                      ->get();

            return view('tags.index', compact('tags'));
        } catch (\Exception $e) {
            Log::error('Błąd podczas pobierania listy tagów: ' . $e->getMessage());
            return back()->with('error', 'Wystąpił błąd podczas pobierania tagów.');
        }
    }

    /**
     * Show the form for creating a new tag.
     */
    public function create()
    {
        // Authorization check - only authenticated users can create tags
        $this->authorize('create', Tag::class);

        return view('tags.create');
    }

    /**
     * Store a newly created tag in storage.
     */
    public function store(StoreTagRequest $request)
    {
        // Authorization check - only authenticated users can create tags
        $this->authorize('create', Tag::class);

        try {
            DB::beginTransaction();

            $tag = Tag::create([
                'name' => $request->name,
                'color' => $request->color ?? '#3B82F6',
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('tags.index')
                           ->with('success', 'Tag został utworzony pomyślnie.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Błąd podczas tworzenia tagu: ' . $e->getMessage());
            return back()->withInput()
                        ->with('error', 'Wystąpił błąd podczas tworzenia tagu.');
        }
    }

    /**
     * Display the specified tag.
     */
    public function show(Tag $tag)
    {
        // Authorization check - only authenticated users can view individual tags
        $this->authorize('view', $tag);

        try {
            $tag->load(['contacts' => function ($query) {
                $query->orderBy('last_name')->orderBy('first_name');
            }]);

            return view('tags.show', compact('tag'));
        } catch (\Exception $e) {
            Log::error('Błąd podczas wyświetlania tagu: ' . $e->getMessage());
            return back()->with('error', 'Wystąpił błąd podczas wyświetlania tagu.');
        }
    }

    /**
     * Show the form for editing the specified tag.
     */
    public function edit(Tag $tag)
    {
        // Authorization check - only authenticated users can edit tags
        $this->authorize('update', $tag);

        return view('tags.edit', compact('tag'));
    }

    /**
     * Update the specified tag in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        // Authorization check - only authenticated users can update tags
        $this->authorize('update', $tag);

        try {
            DB::beginTransaction();

            $tag->update([
                'name' => $request->name,
                'color' => $request->color,
            ]);

            DB::commit();

            return redirect()->route('tags.index')
                           ->with('success', 'Tag został zaktualizowany pomyślnie.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Błąd podczas aktualizacji tagu: ' . $e->getMessage());
            return back()->withInput()
                        ->with('error', 'Wystąpił błąd podczas aktualizacji tagu.');
        }
    }

    /**
     * Remove the specified tag from storage.
     */
    public function destroy(Tag $tag)
    {
        // Authorization check - only authenticated users can delete tags
        $this->authorize('delete', $tag);

        try {
            DB::beginTransaction();

            $contactCount = $tag->contacts()->count();
            $tagName = $tag->name;

            // Detach all contacts first
            $tag->contacts()->detach();
            
            // Delete the tag
            $tag->delete();

            DB::commit();

            $message = $contactCount > 0 
                ? "Tag '{$tagName}' został usunięty. Usunięto go z {$contactCount} kontaktów."
                : "Tag '{$tagName}' został usunięty pomyślnie.";

            return redirect()->route('tags.index')
                           ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Błąd podczas usuwania tagu: ' . $e->getMessage());
            return back()->with('error', 'Wystąpił błąd podczas usuwania tagu.');
        }
    }

    /**
     * Get tags for AJAX requests.
     */
    public function api()
    {
        // Authorization check - only authenticated users can access tags API
        $this->authorize('api', Tag::class);

        try {
            $tags = Tag::orderBy('name')->get(['id', 'name', 'color']);
            return response()->json($tags);
        } catch (\Exception $e) {
            Log::error('Błąd podczas pobierania tagów przez API: ' . $e->getMessage());
            return response()->json(['error' => 'Wystąpił błąd podczas pobierania tagów.'], 500);
        }
    }
}