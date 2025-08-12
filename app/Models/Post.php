<?php

namespace App\Models;

use App\Services\TipTapContentProcessor;
use App\Traits\AdvancedSearchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Post extends Model
{
    use HasFactory, AdvancedSearchable, LogsActivity;

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'published_at',
        'user_id',
        'category_id',
        'is_featured',
        'allow_comments',
        'views_count',
        'reading_time',
        'seo_title',
        'seo_description',
        'meta_keywords',
        'og_image',
        'twitter_card',
        'custom_css',
        'custom_js'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'views_count' => 'integer',
        'reading_time' => 'integer'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Configure searchable columns
        $this->searchableColumns = [
            'title',
            'slug',
            'excerpt',
            'content',
            'seo_title',
            'seo_description',
            'meta_keywords',
            'user.name',
            'category.name'
        ];
        
        // Configure date columns for filtering
        $this->dateColumns = [
            'created_at',
            'updated_at',
            'published_at'
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'status', 'published_at', 'is_featured', 'category_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('post')
            ->setDescriptionForEvent(fn(string $eventName) => "Post '{$this->title}' was {$eventName}");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id","id");
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag')
                    ->withTimestamps();
    }

    public function views(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                    ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                    ->where('published_at', '>', now());
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeWithCategory(Builder $query): Builder
    {
        return $query->with('category');
    }

    public function scopeWithTags(Builder $query): Builder
    {
        return $query->with('tags');
    }

    public function scopeWithAuthor(Builder $query): Builder
    {
        return $query->with('user:id,name,email,avatar');
    }

    public function scopeRecent(Builder $query, int $limit = 5): Builder
    {
        return $query->published()
                    ->orderByDesc('published_at')
                    ->limit($limit);
    }

    public function scopePopular(Builder $query, int $limit = 5): Builder
    {
        return $query->published()
                    ->orderByDesc('views_count')
                    ->limit($limit);
    }

    public function scopeByCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByTag(Builder $query, $tagId): Builder
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('tag_id', $tagId);
        });
    }

    public function scopeByAuthor(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopePublishedBetween(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->published()
                    ->whereBetween('published_at', [$start, $end]);
    }

    public function scopeWithViewsCount(Builder $query): Builder
    {
        return $query->withCount('views');
    }

    public function scopeFullTextSearch(Builder $query, string $term): Builder
    {
        return $query->whereRaw(
            "MATCH(title, excerpt, content) AGAINST(? IN NATURAL LANGUAGE MODE)",
            [$term]
        );
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && 
               $this->published_at && 
               $this->published_at->isPast();
    }

    public function getIsScheduledAttribute(): bool
    {
        return $this->status === self::STATUS_SCHEDULED && 
               $this->published_at && 
               $this->published_at->isFuture();
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_ARCHIVED => 'Archived',
            default => 'Unknown'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PUBLISHED => 'green',
            self::STATUS_DRAFT => 'gray',
            self::STATUS_SCHEDULED => 'blue',
            self::STATUS_ARCHIVED => 'red',
            default => 'gray'
        };
    }

    public function getReadingTimeAttribute($value): int
    {
        if ($value) {
            return $value;
        }

        return TipTapContentProcessor::calculateReadingTime($this->content);
    }

    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return TipTapContentProcessor::generateExcerpt($this->content, 160);
    }

    public function getContentWithAnchorsAttribute(): string
    {
        return TipTapContentProcessor::addHeadingAnchors($this->content);
    }

    public function getTableOfContentsAttribute(): array
    {
        return TipTapContentProcessor::extractHeadings($this->content);
    }

    public function getContentImagesAttribute(): array
    {
        return TipTapContentProcessor::extractImages($this->content);
    }

    public function getSanitizedContentAttribute(): string
    {
        return TipTapContentProcessor::sanitizeContent($this->content);
    }

    public function getSeoTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    public function getSeoDescriptionAttribute($value): string
    {
        return $value ?: $this->excerpt;
    }

    public function getUrlAttribute(): string
    {
        return route('admin.blog.posts.show', $this->slug);
    }

    public function getTagNamesAttribute(): string
    {
        return $this->tags->pluck('name')->implode(', ');
    }

    public function getNextPostAttribute(): ?self
    {
        return static::published()
                    ->where('published_at', '>', $this->published_at)
                    ->orderBy('published_at')
                    ->first();
    }

    public function getPreviousPostAttribute(): ?self
    {
        return static::published()
                    ->where('published_at', '<', $this->published_at)
                    ->orderByDesc('published_at')
                    ->first();
    }

    public function getRelatedPostsAttribute(): \Illuminate\Support\Collection
    {
        $tagIds = $this->tags->pluck('id')->toArray();
        
        if (empty($tagIds)) {
            return collect();
        }

        return static::published()
                    ->where('id', '!=', $this->id)
                    ->whereHas('tags', function ($query) use ($tagIds) {
                        $query->whereIn('tag_id', $tagIds);
                    })
                    ->withCount(['tags' => function ($query) use ($tagIds) {
                        $query->whereIn('tag_id', $tagIds);
                    }])
                    ->orderByDesc('tags_count')
                    ->orderByDesc('published_at')
                    ->limit(4)
                    ->get();
    }

    public function incrementViews(?string $ipAddress = null, ?string $userAgent = null, ?string $referrer = null, ?int $userId = null): void
    {
        $this->increment('views_count');

        if ($ipAddress) {
            $this->views()->create([
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'referrer' => $referrer,
                'user_id' => $userId,
                'session_id' => session()->getId(),
                'viewed_at' => now()
            ]);
        }
    }

    public function syncTags(array $tagNames): void
    {
        $tags = Tag::createFromNames($tagNames);
        $this->tags()->sync($tags->pluck('id'));
    }

    public function publish(?Carbon $publishedAt = null): bool
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->published_at = $publishedAt ?: now();
        
        return $this->save();
    }

    public function schedule(Carbon $publishedAt): bool
    {
        $this->status = self::STATUS_SCHEDULED;
        $this->published_at = $publishedAt;
        
        return $this->save();
    }

    public function archive(): bool
    {
        $this->status = self::STATUS_ARCHIVED;
        
        return $this->save();
    }

    public function makeDraft(): bool
    {
        $this->status = self::STATUS_DRAFT;
        $this->published_at = null;
        
        return $this->save();
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_ARCHIVED => 'Archived'
        ];
    }

    public static function publishScheduledPosts(): int
    {
        $posts = static::where('status', self::STATUS_SCHEDULED)
                      ->where('published_at', '<=', now())
                      ->get();

        foreach ($posts as $post) {
            $post->publish($post->published_at);
        }

        return $posts->count();
    }

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            if (is_null($post->allow_comments)) {
                $post->allow_comments = true;
            }
            
            if (is_null($post->user_id)) {
                $post->user_id = auth()->id();
            }

            // Process content with TipTap
            if ($post->content) {
                $post->content = TipTapContentProcessor::sanitizeContent($post->content);
                $post->reading_time = TipTapContentProcessor::calculateReadingTime($post->content);
                
                // Auto-generate excerpt if empty
                if (empty($post->excerpt)) {
                    $post->excerpt = TipTapContentProcessor::generateExcerpt($post->content, 160);
                }
            } else {
                $post->reading_time = 1;
            }
        });

        static::updating(function (Post $post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            if ($post->isDirty('content')) {
                // Sanitize content
                $post->content = TipTapContentProcessor::sanitizeContent($post->content);
                
                // Update reading time
                $post->reading_time = TipTapContentProcessor::calculateReadingTime($post->content);
                
                // Auto-generate excerpt if empty
                if (empty($post->excerpt)) {
                    $post->excerpt = TipTapContentProcessor::generateExcerpt($post->content, 160);
                }
            }
        });
    }
}
