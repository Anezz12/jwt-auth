<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCardResource;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TagController extends Controller
{
    /**
     * Display a listing of tags.
     */
    public function index(): JsonResponse
    {
        $tags = Cache::remember('tags_list', 3600, function () {
            return Tag::withCount('articles')
                ->having('articles_count', '>', 0)
                ->orderBy('articles_count', 'desc')
                ->get();
        });

        return response()->json([
            'data' => TagResource::collection($tags),
        ]);
    }

    /**
     * Get articles by tag.
     */
    public function articles(Request $request, string $slug): JsonResponse
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $perPage = $request->input('limit', 10);

        $articles = $tag->articles()
            ->published()
            ->with(['category', 'author'])
            ->recent()
            ->paginate($perPage);

        return response()->json([
            'data' => ArticleCardResource::collection($articles),
            'pagination' => [
                'page' => $articles->currentPage(),
                'limit' => $articles->perPage(),
                'total' => $articles->total(),
                'totalPages' => $articles->lastPage(),
                'hasNext' => $articles->hasMorePages(),
                'hasPrev' => $articles->currentPage() > 1,
            ],
        ]);
    }

    /**
     * Store a newly created tag (CMS).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags',
            'slug' => 'required|string|max:255|unique:tags',
        ]);

        $tag = Tag::create($validated);

        // Clear cache
        Cache::forget('tags_list');

        return response()->json([
            'message' => 'Tag created successfully',
            'data' => new TagResource($tag),
        ], 201);
    }

    /**
     * Update the specified tag (CMS).
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255|unique:tags,name,'.$tag->id,
            'slug' => 'string|max:255|unique:tags,slug,'.$tag->id,
        ]);

        $tag->update($validated);

        // Clear cache
        Cache::forget('tags_list');

        return response()->json([
            'message' => 'Tag updated successfully',
            'data' => new TagResource($tag),
        ]);
    }

    /**
     * Remove the specified tag (CMS).
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        // Clear cache
        Cache::forget('tags_list');

        return response()->json([
            'message' => 'Tag deleted successfully',
        ]);
    }
}
