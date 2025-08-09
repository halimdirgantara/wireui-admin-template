# AdvancedSearchable Trait

The `AdvancedSearchable` trait provides a comprehensive set of search utilities for Laravel Eloquent models, including multi-column search, date-range filtering, query-string synchronization, and result highlighting.

## Features

- **Multi-column search**: Search across multiple database columns with a single query
- **Date-range filtering**: Filter records by date ranges on configurable date columns
- **Query-string synchronization**: Build queries from URL parameters for consistent state management
- **Result highlighting**: Highlight search terms in text using `Str::replaceMatches()`
- **Relationship support**: Search through model relationships using dot notation
- **Flexible filtering**: Support for multiple filter types including array-based filtering
- **Sorting support**: Dynamic sorting with customizable direction

## Installation

1. Add the trait to your model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AdvancedSearchable;

class User extends Model
{
    use AdvancedSearchable;
    
    /**
     * Columns that are searchable by the multi-column search.
     */
    protected $searchableColumns = ['name', 'email'];
    
    /**
     * Date columns that can be used for date-range filtering.
     */
    protected $dateColumns = ['created_at', 'updated_at', 'email_verified_at'];
}
```

## Usage Examples

### Multi-Column Search

```php
// Basic search across configured columns
$users = User::multiColumnSearch('john')->get();

// Search with custom columns
$users = User::multiColumnSearch('john', ['name', 'email'])->get();

// Search through relationships (dot notation)
$posts = Post::multiColumnSearch('john', ['user.name', 'title', 'content'])->get();
```

### Date Range Filtering

```php
// Filter by start date only
$users = User::dateRange('2024-01-01')->get();

// Filter by end date only
$users = User::dateRange(null, '2024-01-31')->get();

// Filter by date range
$users = User::dateRange('2024-01-01', '2024-01-31')->get();

// Filter by custom date column
$users = User::dateRange('2024-01-01', null, 'email_verified_at')->get();
```

### Query String Synchronization

```php
// Build query from request parameters
$params = [
    'search' => 'john',
    'search_columns' => ['name', 'email'],
    'date_from' => '2024-01-01',
    'date_to' => '2024-01-31',
    'sort_by' => 'created_at',
    'sort_direction' => 'desc',
    'filters' => [
        'is_active' => true,
        'status' => ['published', 'draft']
    ]
];

$users = User::withQueryStringSync($params)->get();
```

### Advanced Search (All-in-One)

```php
$users = User::advancedSearch(
    search: 'john',
    searchColumns: ['name', 'email'],
    dateFrom: '2024-01-01',
    dateTo: '2024-01-31',
    dateColumn: 'created_at',
    sortBy: 'name',
    sortDirection: 'asc',
    filters: ['is_active' => true]
)->get();
```

### Result Highlighting

```php
$user = User::first();
$highlighted = $user->highlightSearchResults(
    'John Doe is a software engineer',
    'John engineer',
    'bg-yellow-300' // Custom CSS class (optional)
);
// Output: "<span class="bg-yellow-300">John</span> Doe is a software <span class="bg-yellow-300">engineer</span>"
```

### Query String Parameter Generation

```php
// Generate clean query parameters for URLs
$queryParams = User::generateQueryStringParams([
    'search' => 'john',
    'date_from' => '2024-01-01',
    'sort_by' => 'name',
    'sort_direction' => 'asc', // Will be omitted (default value)
    'date_column' => 'created_at', // Will be omitted (default value)
]);
// Result: ['search' => 'john', 'date_from' => '2024-01-01', 'sort_by' => 'name']
```

## Configuration

### Searchable Columns

Configure which columns can be searched:

```php
// In your model
protected $searchableColumns = ['name', 'email', 'description'];

// Or set dynamically
$user = new User();
$user->setSearchableColumns(['name', 'email']);
```

### Date Columns

Configure which columns can be used for date filtering:

```php
// In your model
protected $dateColumns = ['created_at', 'updated_at', 'published_at'];

// Or set dynamically
$user = new User();
$user->setDateColumns(['created_at', 'updated_at']);
```

## API Reference

### Scopes

- `multiColumnSearch(string $searchTerm, array $columns = null): Builder`
- `dateRange(string $startDate = null, string $endDate = null, string $column = 'created_at'): Builder`
- `withQueryStringSync(array $params): Builder`
- `advancedSearch(...): Builder`

### Methods

- `highlightSearchResults(string $text, string $searchTerm, string $highlightClass = 'bg-yellow-200'): string`
- `getSearchableColumns(): array`
- `setSearchableColumns(array $columns): static`
- `getDateColumns(): array`
- `setDateColumns(array $columns): static`
- `clearHighlightCache(): static`

### Static Methods

- `generateQueryStringParams(array $params): array`

## Query String Parameters

The trait supports the following query parameters:

- `search`: Search term
- `search_columns`: Array of columns to search (optional)
- `date_from`: Start date for filtering
- `date_to`: End date for filtering
- `date_column`: Date column to filter by (default: 'created_at')
- `sort_by`: Column to sort by
- `sort_direction`: Sort direction ('asc' or 'desc')
- `filters`: Associative array of field => value filters

## Error Handling

The trait includes validation for date columns:

```php
// This will throw InvalidArgumentException
User::dateRange('2024-01-01', null, 'invalid_column')->get();
```

## Performance Considerations

- **Caching**: Search result highlighting uses internal caching to improve performance
- **Indexing**: Ensure your searchable columns are properly indexed in the database
- **Relationships**: Searching through relationships uses `whereHas()` which may impact performance on large datasets

## Testing

The trait comes with comprehensive tests covering all functionality. Run tests using:

```bash
php artisan test tests/Feature/AdvancedSearchableTraitTest.php
```

## Examples with Livewire Components

```php
class UserIndex extends Component
{
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    
    public function render()
    {
        $users = User::advancedSearch(
            search: $this->search,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
            filters: ['is_active' => true]
        )->paginate(10);
        
        return view('livewire.user-index', compact('users'));
    }
    
    public function getQueryString()
    {
        return User::generateQueryStringParams([
            'search' => $this->search,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
        ]);
    }
}
```

## License

This trait is part of the Laravel application and follows the same license terms.
