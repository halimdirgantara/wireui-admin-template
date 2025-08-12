<?php

namespace App\Models;

use App\Traits\AdvancedSearchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tag extends Model
{
    use HasFactory, AdvancedSearchable, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'meta_keywords',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Configure searchable columns
        $this->searchableColumns = [
            'name',
            'slug',
            'description'
        ];
        
        // Configure date columns for filtering
        $this->dateColumns = [
            'created_at',
            'updated_at'
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'description', 'color', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('tag')
            ->setDescriptionForEvent(fn(string $eventName) => "Tag '{$this->name}' was {$eventName}");
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag')
                    ->withTimestamps();
    }

    public function publishedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag')
                    ->where('posts.status', 'published')
                    ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePopular(Builder $query, int $limit = 10): Builder
    {
        return $query->withCount('publishedPosts')
                    ->orderByDesc('published_posts_count')
                    ->limit($limit);
    }

    public function scopeWithPostCounts(Builder $query): Builder
    {
        return $query->withCount(['posts', 'publishedPosts']);
    }

    public function getPostsCountAttribute(): int
    {
        return $this->posts()->count();
    }

    public function getPublishedPostsCountAttribute(): int
    {
        return $this->publishedPosts()->count();
    }

    public function getColorClassAttribute(): string
    {
        $colorMap = [
            '#ef4444' => 'bg-red-100 text-red-800',
            '#f97316' => 'bg-orange-100 text-orange-800', 
            '#eab308' => 'bg-yellow-100 text-yellow-800',
            '#22c55e' => 'bg-green-100 text-green-800',
            '#3b82f6' => 'bg-blue-100 text-blue-800',
            '#6366f1' => 'bg-indigo-100 text-indigo-800',
            '#8b5cf6' => 'bg-purple-100 text-purple-800',
            '#ec4899' => 'bg-pink-100 text-pink-800',
            '#6b7280' => 'bg-gray-100 text-gray-800'
        ];

        return $colorMap[$this->color] ?? 'bg-blue-100 text-blue-800';
    }

    public function getTextColorAttribute(): string
    {
        $color = ltrim($this->color, '#');
        
        if (strlen($color) !== 6) {
            return '#ffffff';
        }
        
        $rgb = array_map('hexdec', str_split($color, 2));
        $brightness = (($rgb[0] * 299) + ($rgb[1] * 587) + ($rgb[2] * 114)) / 1000;
        
        return $brightness > 155 ? '#000000' : '#ffffff';
    }

    public static function getPopularTags(int $limit = 10): \Illuminate\Support\Collection
    {
        return static::active()
                    ->withCount('publishedPosts')
                    ->having('published_posts_count', '>', 0)
                    ->orderByDesc('published_posts_count')
                    ->limit($limit)
                    ->get();
    }

    public static function findBySlugOrCreate(string $name): static
    {
        $slug = Str::slug($name);
        
        return static::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'color' => '#3b82f6',
                'is_active' => true
            ]
        );
    }

    public static function createFromNames(array $names): \Illuminate\Support\Collection
    {
        return collect($names)->map(function ($name) {
            return static::findBySlugOrCreate(trim($name));
        });
    }

    protected static function booted(): void
    {
        static::creating(function (Tag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
            
            if (empty($tag->color)) {
                $tag->color = '#3b82f6';
            }
        });

        static::updating(function (Tag $tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }
}
