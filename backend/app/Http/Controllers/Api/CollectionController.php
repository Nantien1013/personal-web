<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CollectionWork;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CollectionWork::with('categories');

        if ($request->filled('type'))     $query->where('type', $request->type);
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('year'))     $query->where('release_year', $request->year);
        if ($request->filled('season'))   $query->where('release_season', $request->season);
        if ($request->boolean('favorite')) $query->where('is_favorite', true);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('title_original', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category')) {
            $ids = explode(',', $request->category);
            $query->whereHas('categories', fn($q) => $q->whereIn('collection_categories.id', $ids));
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total'     => CollectionWork::count(),
            'anime'     => CollectionWork::where('type', 'anime')->count(),
            'manga'     => CollectionWork::where('type', 'manga')->count(),
            'completed' => CollectionWork::where('status', 'completed')->count(),
            'watching'  => CollectionWork::where('status', 'watching')->count(),
            'favorites' => CollectionWork::where('is_favorite', true)->count(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $work = CollectionWork::with('categories')->findOrFail($id);
        return response()->json($work);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'             => 'required|in:anime,manga',
            'title'            => 'required|string|max:255',
            'title_original'   => 'nullable|string|max:255',
            'cover_url'        => 'nullable|url|max:500',
            'status'           => 'required|in:watching,completed,plan,on_hold,dropped',
            'rating'           => 'nullable|integer|min:0|max:5',
            'is_favorite'      => 'boolean',
            'release_year'     => 'nullable|integer|min:1900|max:2100',
            'release_season'   => 'nullable|in:winter,spring,summer,autumn',
            'media_type'       => 'nullable|string|max:30',
            'source_type'      => 'nullable|string|max:30',
            'episodes_total'   => 'nullable|integer|min:0',
            'episodes_watched' => 'integer|min:0',
            'volumes_total'    => 'nullable|integer|min:0',
            'volumes_read'     => 'integer|min:0',
            'author'           => 'nullable|string|max:100',
            'studio'           => 'nullable|string|max:100',
            'note'             => 'nullable|string',
            'category_ids'     => 'array',
            'category_ids.*'   => 'integer|exists:collection_categories,id',
        ]);

        $work = CollectionWork::create($validated);

        if (!empty($validated['category_ids'])) {
            $work->categories()->sync($validated['category_ids']);
        }

        return response()->json($work->load('categories'), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $work = CollectionWork::findOrFail($id);

        $validated = $request->validate([
            'type'             => 'sometimes|in:anime,manga',
            'title'            => 'sometimes|string|max:255',
            'title_original'   => 'nullable|string|max:255',
            'cover_url'        => 'nullable|url|max:500',
            'status'           => 'sometimes|in:watching,completed,plan,on_hold,dropped',
            'rating'           => 'nullable|integer|min:0|max:5',
            'is_favorite'      => 'boolean',
            'release_year'     => 'nullable|integer|min:1900|max:2100',
            'release_season'   => 'nullable|in:winter,spring,summer,autumn',
            'media_type'       => 'nullable|string|max:30',
            'source_type'      => 'nullable|string|max:30',
            'episodes_total'   => 'nullable|integer|min:0',
            'episodes_watched' => 'integer|min:0',
            'volumes_total'    => 'nullable|integer|min:0',
            'volumes_read'     => 'integer|min:0',
            'author'           => 'nullable|string|max:100',
            'studio'           => 'nullable|string|max:100',
            'note'             => 'nullable|string',
            'category_ids'     => 'array',
            'category_ids.*'   => 'integer|exists:collection_categories,id',
        ]);

        $work->update($validated);

        if (array_key_exists('category_ids', $validated)) {
            $work->categories()->sync($validated['category_ids']);
        }

        return response()->json($work->load('categories'));
    }

    public function destroy(int $id): JsonResponse
    {
        CollectionWork::findOrFail($id)->delete();
        return response()->json(['message' => '已刪除']);
    }
}
