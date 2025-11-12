<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCardResource;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search articles.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'category' => 'nullable|string',
            'tag' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->input('q');
        $perPage = $request->input('limit', 10);
        $categorySlug = $request->input('category');
        $tagSlug = $request->input('tag');

        $articles = Article::published()
            ->with(['category', 'author'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            });

        // Filter by category
        if ($categorySlug) {
            $articles->whereHas('category', fn ($q) => $q->where('slug', $categorySlug)
            );
        }

        // Filter by tag
        if ($tagSlug) {
            $articles->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug)
            );
        }

        $results = $articles->recent()->paginate($perPage);

        return response()->json([
            'query' => $query,
            'data' => ArticleCardResource::collection($results),
            'pagination' => [
                'page' => $results->currentPage(),
                'limit' => $results->perPage(),
                'total' => $results->total(),
                'totalPages' => $results->lastPage(),
                'hasNext' => $results->hasMorePages(),
                'hasPrev' => $results->currentPage() > 1,
            ],
        ]);
    }

    /**
     * Advanced search with full-text (MySQL 5.7+)
     *
     * Uncomment if you want to use MySQL full-text search
     */
    // public function fulltext(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'q' => 'required|string|min:2',
    //         'limit' => 'nullable|integer|min:1|max:50',
    //     ]);

    //     $query = $request->input('q');
    //     $perPage = $request->input('limit', 10);

    //     $articles = Article::published()
    //         ->with(['category', 'author'])
    //         ->whereRaw(
    //             'MATCH(title, excerpt, content) AGAINST(? IN NATURAL LANGUAGE MODE)',
    //             [$query]
    //         )
    //         ->paginate($perPage);

    //     return response()->json([
    //         'query' => $query,
    //         'data' => ArticleCardResource::collection($articles),
    //         'pagination' => [
    //             'page' => $articles->currentPage(),
    //             'limit' => $articles->perPage(),
    //             'total' => $articles->total(),
    //             'totalPages' => $articles->lastPage(),
    //         ],
    //     ]);
    // }
}
