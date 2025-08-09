<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PostView extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'ip_address',
        'user_agent',
        'referrer',
        'user_id',
        'session_id',
        'viewed_at'
    ];

    protected $casts = [
        'viewed_at' => 'datetime'
    ];

    public $timestamps = false;

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('viewed_at', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('viewed_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('viewed_at', now()->month)
                    ->whereYear('viewed_at', now()->year);
    }

    public function scopeThisYear(Builder $query): Builder
    {
        return $query->whereYear('viewed_at', now()->year);
    }

    public function scopeBetweenDates(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('viewed_at', [$startDate, $endDate]);
    }

    public function scopeByPost(Builder $query, int $postId): Builder
    {
        return $query->where('post_id', $postId);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUniqueVisitors(Builder $query): Builder
    {
        return $query->select('ip_address')
                    ->distinct();
    }

    public function scopeGroupByDate(Builder $query, string $format = '%Y-%m-%d'): Builder
    {
        return $query->select(
            DB::raw("DATE_FORMAT(viewed_at, '{$format}') as date"),
            DB::raw('COUNT(*) as views'),
            DB::raw('COUNT(DISTINCT ip_address) as unique_visitors')
        )->groupBy('date');
    }

    public function scopeWithReferrerData(Builder $query): Builder
    {
        return $query->select('*')
                    ->addSelect(DB::raw("
                        CASE 
                            WHEN referrer IS NULL OR referrer = '' THEN 'Direct'
                            WHEN referrer LIKE '%google%' THEN 'Google'
                            WHEN referrer LIKE '%facebook%' THEN 'Facebook'
                            WHEN referrer LIKE '%twitter%' THEN 'Twitter'
                            WHEN referrer LIKE '%linkedin%' THEN 'LinkedIn'
                            WHEN referrer LIKE '%youtube%' THEN 'YouTube'
                            ELSE 'Other'
                        END as referrer_source
                    "));
    }

    public static function getViewsForPost(int $postId, ?Carbon $startDate = null, ?Carbon $endDate = null): int
    {
        $query = static::where('post_id', $postId);
        
        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }
        
        return $query->count();
    }

    public static function getUniqueViewsForPost(int $postId, ?Carbon $startDate = null, ?Carbon $endDate = null): int
    {
        $query = static::where('post_id', $postId)
                      ->select('ip_address')
                      ->distinct();
        
        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }
        
        return $query->count();
    }

    public static function getTopPostsByViews(int $limit = 10, ?Carbon $startDate = null, ?Carbon $endDate = null): \Illuminate\Support\Collection
    {
        $query = static::select('post_id', DB::raw('COUNT(*) as total_views'))
                      ->with('post:id,title,slug')
                      ->groupBy('post_id')
                      ->orderByDesc('total_views')
                      ->limit($limit);
        
        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }
        
        return $query->get();
    }

    public static function getDailyViewsChart(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();
        
        $views = static::betweenDates($startDate, $endDate)
                      ->groupByDate('%Y-%m-%d')
                      ->orderBy('date')
                      ->get();
        
        $chartData = [];
        $period = new \DatePeriod(
            $startDate,
            new \DateInterval('P1D'),
            $endDate->addDay()
        );
        
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $viewData = $views->firstWhere('date', $dateStr);
            
            $chartData[] = [
                'date' => $dateStr,
                'views' => $viewData ? $viewData->views : 0,
                'unique_visitors' => $viewData ? $viewData->unique_visitors : 0
            ];
        }
        
        return $chartData;
    }

    public static function getReferrerStats(?Carbon $startDate = null, ?Carbon $endDate = null): \Illuminate\Support\Collection
    {
        $query = static::withReferrerData()
                      ->select('referrer_source', DB::raw('COUNT(*) as views'))
                      ->groupBy('referrer_source')
                      ->orderByDesc('views');
        
        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }
        
        return $query->get();
    }

    public static function getViewsAnalytics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }
        
        $totalViews = $query->count();
        $uniqueVisitors = $query->select('ip_address')->distinct()->count();
        $averageViewsPerPost = Post::withCount(['views' => function ($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $q->betweenDates($startDate, $endDate);
            }
        }])->avg('views_count') ?? 0;
        
        return [
            'total_views' => $totalViews,
            'unique_visitors' => $uniqueVisitors,
            'average_views_per_post' => round($averageViewsPerPost, 2),
            'bounce_rate' => 0,
        ];
    }

    public static function cleanOldViews(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return static::where('viewed_at', '<', $cutoffDate)->delete();
    }

    protected static function booted(): void
    {
        static::creating(function (PostView $view) {
            if (is_null($view->viewed_at)) {
                $view->viewed_at = now();
            }
        });
    }
}
