<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'category_id',
        'author_id',
        'featured_image',
        'image_alt',
        'status',
        'is_featured',
        'is_editor_pick',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_editor_pick' => 'boolean',
        'meta_keywords' => 'array',
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'href',
        'timestamp',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    // Accessors
    public function getHrefAttribute(): string
    {
        return "/article/{$this->slug}";
    }

    public function getTimestampAttribute(): string
    {
        return $this->published_at?->diffForHumans() ?? '';
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeEditorPick($query)
    {
        return $query->where('is_editor_pick', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    // Increment views
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
