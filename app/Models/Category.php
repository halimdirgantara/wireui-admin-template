<?php

namespace App\Models;

use App\Traits\AdvancedSearchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogsActivity;
use Spatie\Activitylog\Traits\LogsActivity as LogsActivityTrait;

class Category extends Model implements LogsActivity
{
    use HasFactory, AdvancedSearchable, LogsActivityTrait;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'color',
        'icon',
        'image',
        'is_active',
        'sort_order',
        'seo_title',
        'seo_description',
        'meta_keywords'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'parent_id' => 'integer'
    ];

    protected $searchableColumns = [
        'name',
        'slug', 
        'description',
        'seo_title',
        'seo_description',
        'meta_keywords'
    ];

    protected $dateColumns = [
        'created_at',
        'updated_at'
    ];

    protected static $logAttributes = [
        'name',
        'slug',
        'description',
        'parent_id',
        'is_active',
        'sort_order'
    ];

    protected static $logName = 'category';

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Category {$this->name} was {$eventName}";
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
                    ->orderBy('sort_order')
                    ->orderBy('name');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function activePosts(): HasMany
    {
        return $this->hasMany(Post::class)->where('status', 'published');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRootCategories(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithChildren(Builder $query): Builder
    {
        return $query->with(['children' => function ($q) {
            $q->active()->orderBy('sort_order')->orderBy('name');
        }]);
    }

    public function scopeOrderedBySort(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getPostsCountAttribute(): int
    {
        return $this->posts()->count();
    }

    public function getActivePostsCountAttribute(): int
    {
        return $this->activePosts()->count();
    }

    public function getHasChildrenAttribute(): bool
    {
        return $this->children()->exists();
    }

    public function getFullNameAttribute(): string
    {
        $names = collect([$this->name]);
        $parent = $this->parent;
        
        while ($parent) {
            $names->prepend($parent->name);
            $parent = $parent->parent;
        }
        
        return $names->implode(' > ');
    }

    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        $category = $this;
        
        while ($category) {
            array_unshift($breadcrumb, [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug
            ]);
            $category = $category->parent;
        }
        
        return $breadcrumb;
    }

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            
            if (is_null($category->sort_order)) {
                $maxSort = static::where('parent_id', $category->parent_id)
                                ->max('sort_order') ?? 0;
                $category->sort_order = $maxSort + 1;
            }
        });

        static::updating(function (Category $category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
