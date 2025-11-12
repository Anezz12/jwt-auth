<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'href' => $this->href,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'category' => $this->category->name,
            'categorySlug' => $this->category->slug,
            'timestamp' => $this->timestamp,
            'publishedAt' => $this->published_at?->toISOString(),
            'image' => $this->featured_image,
            'imageAlt' => $this->image_alt ?? $this->title,
            'author' => $this->when($this->relationLoaded('author'), [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'avatar' => $this->author->avatar,
            ]),
        ];
    }
}
