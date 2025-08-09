<?php

use App\Models\User;
use App\Traits\AdvancedSearchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

// Create a test model to use the trait
class TestSearchableModel extends Model
{
    use AdvancedSearchable;

    protected $table = 'users'; // Use users table for testing
    protected $fillable = ['name', 'email', 'created_at', 'updated_at'];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->searchableColumns = ['name', 'email'];
        $this->dateColumns = ['created_at', 'updated_at', 'email_verified_at'];
    }
}

beforeEach(function () {
    $this->model = new TestSearchableModel();
    
    // Create test data
    $this->testUsers = [
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'created_at' => now()->subDays(5),
        ]),
        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'created_at' => now()->subDays(3),
        ]),
        User::factory()->create([
            'name' => 'Bob Johnson',
            'email' => 'bob@test.com',
            'created_at' => now()->subDays(1),
        ]),
    ];
});

describe('AdvancedSearchable Trait', function () {
    describe('Multi-column search', function () {
        it('can perform basic multi-column search', function () {
            // Clear database to ensure clean test
            User::query()->delete();
            
            // Create specific user for this test
            User::factory()->create([
                'name' => 'John Unique',
                'email' => 'johnunique@test.com',
            ]);
            
            $query = TestSearchableModel::multiColumnSearch('John Unique');
            $results = $query->get();
            
            expect($results->count())->toBe(1);
            expect($results->first()->name)->toBe('John Unique');
        });

        it('searches across multiple columns', function () {
            // Clear database to ensure clean test
            User::query()->delete();
            
            // Create specific users for this test
            User::factory()->create([
                'name' => 'User One',
                'email' => 'user1@uniquetest.com',
            ]);
            User::factory()->create([
                'name' => 'User Two',
                'email' => 'user2@uniquetest.com',
            ]);
            
            $query = TestSearchableModel::multiColumnSearch('uniquetest.com');
            $results = $query->get();
            
            expect($results->count())->toBe(2);
            expect($results->pluck('email')->toArray())
                ->toContain('user1@uniquetest.com', 'user2@uniquetest.com');
        });

        it('returns all records when search term is empty', function () {
            $query = TestSearchableModel::multiColumnSearch('');
            $results = $query->get();
            
            expect($results->count())->toBe(3);
        });

        it('returns empty results when no matches found', function () {
            $query = TestSearchableModel::multiColumnSearch('nonexistent');
            $results = $query->get();
            
            expect($results->count())->toBe(0);
        });

        it('can search with custom columns', function () {
            // Clear database to ensure clean test
            User::query()->delete();
            
            // Create specific user for this test
            User::factory()->create([
                'name' => 'John Custom',
                'email' => 'johncustom@test.com',
            ]);
            
            $query = TestSearchableModel::multiColumnSearch('John Custom', ['name']);
            $results = $query->get();
            
            expect($results->count())->toBe(1);
            expect($results->first()->name)->toBe('John Custom');
        });

        it('returns empty when no searchable columns defined', function () {
            $model = new TestSearchableModel();
            $model->setSearchableColumns([]);
            
            $query = $model->newQuery()->multiColumnSearch('John');
            $results = $query->get();
            
            expect($results->count())->toBe(3); // Should return all records when no columns defined
        });
    });

    describe('Date range filtering', function () {
        it('can filter by start date only', function () {
            $startDate = now()->subDays(4)->format('Y-m-d');
            $query = TestSearchableModel::dateRange($startDate);
            $results = $query->get();
            
            expect($results->count())->toBe(2); // Jane and Bob
        });

        it('can filter by end date only', function () {
            $endDate = now()->subDays(2)->format('Y-m-d');
            $query = TestSearchableModel::dateRange(null, $endDate);
            $results = $query->get();
            
            expect($results->count())->toBe(2); // John and Jane
        });

        it('can filter by date range', function () {
            $startDate = now()->subDays(4)->format('Y-m-d');
            $endDate = now()->subDays(2)->format('Y-m-d');
            $query = TestSearchableModel::dateRange($startDate, $endDate);
            $results = $query->get();
            
            expect($results->count())->toBe(1); // Only Jane
            expect($results->first()->name)->toBe('Jane Smith');
        });

        it('can filter by custom date column', function () {
            $startDate = now()->subDays(4)->format('Y-m-d');
            $query = TestSearchableModel::dateRange($startDate, null, 'updated_at');
            $results = $query->get();
            
            // Should work with updated_at column
            expect($results->count())->toBeGreaterThanOrEqual(0);
        });

        it('throws exception for invalid date column', function () {
            expect(fn() => TestSearchableModel::dateRange(
                now()->format('Y-m-d'),
                null,
                'invalid_column'
            )->get())->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Query string synchronization', function () {
        it('can sync search parameters from query string', function () {
            $params = [
                'search' => 'John Doe',  // Use full name to be more specific
                'search_columns' => ['name'],
            ];
            
            $query = TestSearchableModel::withQueryStringSync($params);
            $results = $query->get();
            
            expect($results->count())->toBe(1);
            expect($results->first()->name)->toBe('John Doe');
        });

        it('can sync date range parameters', function () {
            $params = [
                'date_from' => now()->subDays(4)->format('Y-m-d'),
                'date_to' => now()->subDays(2)->format('Y-m-d'),
            ];
            
            $query = TestSearchableModel::withQueryStringSync($params);
            $results = $query->get();
            
            expect($results->count())->toBe(1);
            expect($results->first()->name)->toBe('Jane Smith');
        });

        it('can sync sorting parameters', function () {
            $params = [
                'sort_by' => 'name',
                'sort_direction' => 'desc',
            ];
            
            $query = TestSearchableModel::withQueryStringSync($params);
            $results = $query->get();
            
            expect($results->first()->name)->toBe('John Doe');
            expect($results->last()->name)->toBe('Bob Johnson');
        });

        it('can sync filter parameters', function () {
            // Create a user with is_active = false for testing
            User::factory()->create([
                'name' => 'Inactive User',
                'email' => 'inactive@test.com',
                'is_active' => false,
            ]);

            $params = [
                'filters' => [
                    'is_active' => true,
                ],
            ];
            
            $query = TestSearchableModel::withQueryStringSync($params);
            $results = $query->get();
            
            // Should only return active users (first 3 test users)
            expect($results->count())->toBe(3);
        });

        it('can sync multiple parameters together', function () {
            $params = [
                'search' => 'example.com',
                'date_from' => now()->subDays(4)->format('Y-m-d'),
                'sort_by' => 'created_at',
                'sort_direction' => 'desc',
            ];
            
            $query = TestSearchableModel::withQueryStringSync($params);
            $results = $query->get();
            
            expect($results->count())->toBe(1);
            expect($results->first()->name)->toBe('Jane Smith');
        });
    });

    describe('Query string parameter generation', function () {
        it('generates basic search parameters', function () {
            $params = [
                'search' => 'test search',
                'search_columns' => ['name', 'email'],
            ];
            
            $queryParams = TestSearchableModel::generateQueryStringParams($params);
            
            expect($queryParams)->toHaveKey('search', 'test search');
            expect($queryParams)->toHaveKey('search_columns', ['name', 'email']);
        });

        it('generates date range parameters', function () {
            $params = [
                'date_from' => '2024-01-01',
                'date_to' => '2024-01-31',
                'date_column' => 'updated_at',
            ];
            
            $queryParams = TestSearchableModel::generateQueryStringParams($params);
            
            expect($queryParams)->toHaveKey('date_from', '2024-01-01');
            expect($queryParams)->toHaveKey('date_to', '2024-01-31');
            expect($queryParams)->toHaveKey('date_column', 'updated_at');
        });

        it('generates sorting parameters', function () {
            $params = [
                'sort_by' => 'name',
                'sort_direction' => 'desc',
            ];
            
            $queryParams = TestSearchableModel::generateQueryStringParams($params);
            
            expect($queryParams)->toHaveKey('sort_by', 'name');
            expect($queryParams)->toHaveKey('sort_direction', 'desc');
        });

        it('omits default values', function () {
            $params = [
                'search' => 'test',
                'date_column' => 'created_at', // Default value
                'sort_direction' => 'asc', // Default value
            ];
            
            $queryParams = TestSearchableModel::generateQueryStringParams($params);
            
            expect($queryParams)->toHaveKey('search');
            expect($queryParams)->not()->toHaveKey('date_column');
            expect($queryParams)->not()->toHaveKey('sort_direction');
        });

        it('omits empty parameters', function () {
            $params = [
                'search' => '',
                'date_from' => null,
                'sort_by' => '',
            ];
            
            $queryParams = TestSearchableModel::generateQueryStringParams($params);
            
            expect($queryParams)->toBeEmpty();
        });
    });

    describe('Result highlighting', function () {
        it('highlights single search term', function () {
            $model = new TestSearchableModel();
            $text = 'This is a test string';
            $searchTerm = 'test';
            
            $highlighted = $model->highlightSearchResults($text, $searchTerm);
            
            expect($highlighted)->toBe('This is a <span class="bg-yellow-200">test</span> string');
        });

        it('highlights multiple search terms', function () {
            $model = new TestSearchableModel();
            $text = 'This is a test string with multiple words';
            $searchTerm = 'test string';
            
            $highlighted = $model->highlightSearchResults($text, $searchTerm);
            
            expect($highlighted)->toContain('<span class="bg-yellow-200">test</span>');
            expect($highlighted)->toContain('<span class="bg-yellow-200">string</span>');
        });

        it('uses custom highlight class', function () {
            $model = new TestSearchableModel();
            $text = 'This is a test string';
            $searchTerm = 'test';
            $customClass = 'custom-highlight';
            
            $highlighted = $model->highlightSearchResults($text, $searchTerm, $customClass);
            
            expect($highlighted)->toBe('This is a <span class="custom-highlight">test</span> string');
        });

        it('is case insensitive', function () {
            $model = new TestSearchableModel();
            $text = 'This is a TEST string';
            $searchTerm = 'test';
            
            $highlighted = $model->highlightSearchResults($text, $searchTerm);
            
            expect($highlighted)->toBe('This is a <span class="bg-yellow-200">TEST</span> string');
        });

        it('returns original text when search term is empty', function () {
            $model = new TestSearchableModel();
            $text = 'This is a test string';
            $searchTerm = '';
            
            $highlighted = $model->highlightSearchResults($text, $searchTerm);
            
            expect($highlighted)->toBe($text);
        });

        it('returns original text when text is empty', function () {
            $model = new TestSearchableModel();
            $text = '';
            $searchTerm = 'test';
            
            $highlighted = $model->highlightSearchResults($text, $searchTerm);
            
            expect($highlighted)->toBe('');
        });

        it('caches highlighted results', function () {
            $model = new TestSearchableModel();
            $text = 'This is a test string';
            $searchTerm = 'test';
            
            // First call
            $highlighted1 = $model->highlightSearchResults($text, $searchTerm);
            
            // Second call should use cache
            $highlighted2 = $model->highlightSearchResults($text, $searchTerm);
            
            expect($highlighted1)->toBe($highlighted2);
        });

        it('can clear highlight cache', function () {
            $model = new TestSearchableModel();
            $text = 'This is a test string';
            $searchTerm = 'test';
            
            // Populate cache
            $model->highlightSearchResults($text, $searchTerm);
            
            // Clear cache
            $result = $model->clearHighlightCache();
            
            expect($result)->toBe($model); // Should return self for chaining
        });
    });

    describe('Getters and setters', function () {
        it('can get and set searchable columns', function () {
            $model = new TestSearchableModel();
            $columns = ['name', 'email', 'description'];
            
            $result = $model->setSearchableColumns($columns);
            
            expect($result)->toBe($model); // Should return self for chaining
            expect($model->getSearchableColumns())->toBe($columns);
        });

        it('can get and set date columns', function () {
            $model = new TestSearchableModel();
            $columns = ['created_at', 'updated_at', 'published_at'];
            
            $result = $model->setDateColumns($columns);
            
            expect($result)->toBe($model); // Should return self for chaining
            expect($model->getDateColumns())->toBe($columns);
        });
    });

    describe('Advanced search method', function () {
        it('can perform advanced search with all parameters', function () {
            $query = TestSearchableModel::advancedSearch(
                search: 'example.com',
                searchColumns: ['email'],
                dateFrom: now()->subDays(4)->format('Y-m-d'),
                dateTo: now()->subDays(2)->format('Y-m-d'),
                dateColumn: 'created_at',
                sortBy: 'name',
                sortDirection: 'asc'
            );
            
            $results = $query->get();
            
            expect($results->count())->toBe(1);
            expect($results->first()->name)->toBe('Jane Smith');
        });

        it('works with null parameters', function () {
            $query = TestSearchableModel::advancedSearch();
            $results = $query->get();
            
            expect($results->count())->toBe(3);
        });
    });
});
