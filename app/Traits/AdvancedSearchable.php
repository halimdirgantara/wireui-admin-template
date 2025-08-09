<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

trait AdvancedSearchable
{
    /**
     * Columns that are searchable by the multi-column search.
     *
     * @var array
     */
    protected $searchableColumns = [];

    /**
     * Date columns that can be used for date-range filtering.
     *
     * @var array
     */
    protected $dateColumns = ['created_at', 'updated_at'];

    /**
     * Highlighted search results cache.
     *
     * @var array
     */
    protected $highlightedResults = [];

    /**
     * Apply multi-column search to the query.
     *
     * @param Builder $query
     * @param string $searchTerm
     * @param array|null $columns
     * @return Builder
     */
    public function scopeMultiColumnSearch(Builder $query, string $searchTerm, ?array $columns = null): Builder
    {
        if (empty($searchTerm)) {
            return $query;
        }

        $searchableColumns = $columns ?: $this->getSearchableColumns();

        if (empty($searchableColumns)) {
            return $query;
        }

        return $query->where(function ($q) use ($searchTerm, $searchableColumns) {
            foreach ($searchableColumns as $column) {
                // Handle relationship columns (e.g., 'user.name')
                if (strpos($column, '.') !== false) {
                    [$relation, $relationColumn] = explode('.', $column, 2);
                    $q->orWhereHas($relation, function ($relationQuery) use ($relationColumn, $searchTerm) {
                        $relationQuery->where($relationColumn, 'LIKE', '%' . $searchTerm . '%');
                    });
                } else {
                    $q->orWhere($column, 'LIKE', '%' . $searchTerm . '%');
                }
            }
        });
    }

    /**
     * Apply date range filter to the query.
     *
     * @param Builder $query
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string $column
     * @return Builder
     */
    public function scopeDateRange(Builder $query, ?string $startDate = null, ?string $endDate = null, string $column = 'created_at'): Builder
    {
        if (!in_array($column, $this->getDateColumns())) {
            throw new \InvalidArgumentException("Column '{$column}' is not configured for date range filtering.");
        }

        if ($startDate) {
            $query->whereDate($column, '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->whereDate($column, '<=', Carbon::parse($endDate));
        }

        return $query;
    }

    /**
     * Build query with search parameters synchronized from query string.
     *
     * @param Builder $query
     * @param array $params
     * @return Builder
     */
    public function scopeWithQueryStringSync(Builder $query, array $params): Builder
    {
        // Apply search if provided
        if (!empty($params['search'])) {
            $columns = $params['search_columns'] ?? null;
            $query->multiColumnSearch($params['search'], $columns);
        }

        // Apply date range if provided
        if (!empty($params['date_from']) || !empty($params['date_to'])) {
            $dateColumn = $params['date_column'] ?? 'created_at';
            $query->dateRange(
                $params['date_from'] ?? null, 
                $params['date_to'] ?? null, 
                $dateColumn
            );
        }

        // Apply sorting if provided
        if (!empty($params['sort_by'])) {
            $direction = $params['sort_direction'] ?? 'asc';
            $query->orderBy($params['sort_by'], $direction);
        }

        // Apply filters
        if (!empty($params['filters']) && is_array($params['filters'])) {
            foreach ($params['filters'] as $field => $value) {
                if ($value !== null && $value !== '') {
                    if (is_array($value)) {
                        $query->whereIn($field, $value);
                    } else {
                        $query->where($field, $value);
                    }
                }
            }
        }

        return $query;
    }

    /**
     * Generate query string parameters for current search state.
     *
     * @param array $params
     * @return array
     */
    public static function generateQueryStringParams(array $params): array
    {
        $queryParams = [];

        // Add search parameters
        if (!empty($params['search'])) {
            $queryParams['search'] = $params['search'];
            
            if (!empty($params['search_columns'])) {
                $queryParams['search_columns'] = $params['search_columns'];
            }
        }

        // Add date range parameters
        if (!empty($params['date_from'])) {
            $queryParams['date_from'] = $params['date_from'];
        }
        
        if (!empty($params['date_to'])) {
            $queryParams['date_to'] = $params['date_to'];
        }

        if (!empty($params['date_column']) && $params['date_column'] !== 'created_at') {
            $queryParams['date_column'] = $params['date_column'];
        }

        // Add sorting parameters
        if (!empty($params['sort_by'])) {
            $queryParams['sort_by'] = $params['sort_by'];
            
            if (!empty($params['sort_direction']) && $params['sort_direction'] !== 'asc') {
                $queryParams['sort_direction'] = $params['sort_direction'];
            }
        }

        // Add filter parameters
        if (!empty($params['filters']) && is_array($params['filters'])) {
            $queryParams['filters'] = $params['filters'];
        }

        return $queryParams;
    }

    /**
     * Highlight search terms in the given text.
     *
     * @param string $text
     * @param string $searchTerm
     * @param string $highlightClass
     * @return string
     */
    public function highlightSearchResults(string $text, string $searchTerm, string $highlightClass = 'bg-yellow-200'): string
    {
        if (empty($searchTerm) || empty($text)) {
            return $text;
        }

        $cacheKey = md5($text . $searchTerm . $highlightClass);
        
        if (isset($this->highlightedResults[$cacheKey])) {
            return $this->highlightedResults[$cacheKey];
        }

        // Split search term by spaces to highlight multiple words
        $terms = array_filter(explode(' ', $searchTerm));
        
        foreach ($terms as $term) {
            $pattern = '/(' . preg_quote(trim($term), '/') . ')/i';
            $replacement = '<span class="' . $highlightClass . '">$1</span>';
            $text = Str::replaceMatches($pattern, $replacement, $text);
        }

        $this->highlightedResults[$cacheKey] = $text;

        return $text;
    }

    /**
     * Get model's searchable columns.
     *
     * @return array
     */
    public function getSearchableColumns(): array
    {
        return $this->searchableColumns;
    }

    /**
     * Set model's searchable columns.
     *
     * @param array $columns
     * @return $this
     */
    public function setSearchableColumns(array $columns): static
    {
        $this->searchableColumns = $columns;
        return $this;
    }

    /**
     * Get model's date columns.
     *
     * @return array
     */
    public function getDateColumns(): array
    {
        return $this->dateColumns;
    }

    /**
     * Set model's date columns.
     *
     * @param array $columns
     * @return $this
     */
    public function setDateColumns(array $columns): static
    {
        $this->dateColumns = $columns;
        return $this;
    }

    /**
     * Clear highlighted results cache.
     *
     * @return $this
     */
    public function clearHighlightCache(): static
    {
        $this->highlightedResults = [];
        return $this;
    }

    /**
     * Build a complete search query with all parameters.
     *
     * @param Builder $query
     * @param string|null $search
     * @param array|null $searchColumns
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param string|null $dateColumn
     * @param string|null $sortBy
     * @param string|null $sortDirection
     * @param array|null $filters
     * @return Builder
     */
    public function scopeAdvancedSearch(
        Builder $query,
        ?string $search = null,
        ?array $searchColumns = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $dateColumn = 'created_at',
        ?string $sortBy = null,
        ?string $sortDirection = 'asc',
        ?array $filters = null
    ): Builder {
        return $query->withQueryStringSync([
            'search' => $search,
            'search_columns' => $searchColumns,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'date_column' => $dateColumn,
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'filters' => $filters,
        ]);
    }
}
