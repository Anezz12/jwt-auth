<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCardResource;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomepageController extends Controller
{
    public function index(): JsonResponse
    {
        $data = Cache::remember('homepage_data', 300, function () {
            return [
                'leadArticle' => $this->getLeadArticle(),
                'topStories' => $this->getTopStories(),
                'editorPicks' => $this->getEditorPicks(),
                'flashNews' => $this->getFlashNews(),
                'newsPlus' => $this->getCategoryArticles('news-plus', 5),
                'newsInsider' => $this->getCategoryArticles('news-insider', 5),
                'decodeSections' => $this->getSections(),
                'tirtoWeekly' => $this->getTirtoWeekly(),
            ];
        });

        return response()->json($data);
    }

    private function getLeadArticle()
    {
        $article = Article::published()
            ->featured()
            ->recent()
            ->with(['category', 'author'])
            ->first();

        return $article ? new ArticleCardResource($article) : null;
    }

    private function getTopStories()
    {
        return ArticleCardResource::collection(
            Article::published()
                ->recent()
                ->with(['category', 'author'])
                ->skip(1)
                ->take(4)
                ->get()
        );
    }

    private function getEditorPicks()
    {
        return ArticleCardResource::collection(
            Article::published()
                ->editorPick()
                ->recent()
                ->with(['category', 'author'])
                ->take(5)
                ->get()
        );
    }

    private function getFlashNews()
    {
        return ArticleCardResource::collection(
            Article::published()
                ->recent()
                ->with(['category', 'author'])
                ->take(12)
                ->get()
        );
    }

    private function getCategoryArticles(string $categorySlug, int $limit)
    {
        return ArticleCardResource::collection(
            Article::published()
                ->whereHas('category', fn ($q) => $q->where('slug', $categorySlug))
                ->recent()
                ->with(['category', 'author'])
                ->take($limit)
                ->get()
        );
    }

    private function getSections()
    {
        // Implement dynamic sections logic
        return [];
    }

    private function getTirtoWeekly()
    {
        // Implement Tirto Weekly logic (special category or flag)
        return ArticleCardResource::collection(
            Article::published()
                ->whereHas('category', fn ($q) => $q->where('slug', 'tirto-weekly'))
                ->recent()
                ->with(['category', 'author'])
                ->take(5)
                ->get()
        );
    }
}
