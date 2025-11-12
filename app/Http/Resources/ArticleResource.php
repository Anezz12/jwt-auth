<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'href' => $this->href,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content, // Full content
            'category' => $this->category->name,
            'categorySlug' => $this->category->slug,
            'timestamp' => $this->timestamp,
            'publishedAt' => $this->published_at?->toISOString(),
            'image' => $this->featured_image,
            'imageAlt' => $this->image_alt ?? $this->title,
            'viewsCount' => $this->views_count,

            // Full author details
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'email' => $this->author->email,
                'bio' => $this->author->bio,
                'avatar' => $this->author->avatar,
            ],

            // Tags
            'tags' => $this->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ]),

            // Related articles (if loaded)
            'relatedArticles' => $this->when(
                $this->relationLoaded('related'),
                fn () => ArticleCardResource::collection($this->related)
            ),

            // SEO
            'metaDescription' => $this->meta_description,
            'metaKeywords' => $this->meta_keywords,
            'ogImage' => $this->og_image,
        ];
    }
}
