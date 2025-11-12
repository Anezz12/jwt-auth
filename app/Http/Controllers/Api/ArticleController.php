<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCardResource;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', 10);
        $categorySlug = $request->input('category');
        $tagSlug = $request->input('tag');

        $query = Article::published()
            ->with(['category', 'author'])
            ->recent();

        if ($categorySlug) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($tagSlug) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug));
        }

        $articles = $query->paginate($perPage);

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

    public function show(string $slug): JsonResponse
    {
        $article = Article::published()
            ->where('slug', $slug)
            ->with(['category', 'author', 'tags'])
            ->firstOrFail();

        // Increment views asynchronously
        dispatch(fn () => $article->incrementViews())->afterResponse();

        return response()->json(new ArticleResource($article));
    }

    public function related(string $slug): JsonResponse
    {
        $article = Article::published()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->where('category_id', $article->category_id)
            ->recent()
            ->with(['category', 'author'])
            ->take(5)
            ->get();

        return response()->json([
            'data' => ArticleCardResource::collection($related),
        ]);
    }

    public function trending(): JsonResponse
    {
        $trending = Article::published()
            ->orderBy('views_count', 'desc')
            ->recent()
            ->with(['category', 'author'])
            ->take(10)
            ->get();

        return response()->json([
            'data' => ArticleCardResource::collection($trending),
        ]);
    }
}
