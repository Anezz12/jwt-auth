<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCardResource;
use App\Http\Resources\CategoryResource;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): JsonResponse
    {
        $categories = Cache::remember('categories_list', 3600, function () {
            return Category::where('is_active', true)
                ->orderBy('order')
                ->get();
        });

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Display the specified category.
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Get articles by category.
     */
    public function articles(Request $request, string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $perPage = $request->input('limit', 10);

        $articles = Article::published()
            ->where('category_id', $category->id)
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
     * Store a newly created category (CMS).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'slug' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category = Category::create($validated);

        // Clear cache
        Cache::forget('categories_list');

        return response()->json([
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Update the specified category (CMS).
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255|unique:categories,name,'.$category->id,
            'slug' => 'string|max:255|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        // Clear cache
        Cache::forget('categories_list');

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Remove the specified category (CMS).
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if category has articles
        if ($category->articles()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with existing articles',
            ], 422);
        }

        $category->delete();

        // Clear cache
        Cache::forget('categories_list');

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
