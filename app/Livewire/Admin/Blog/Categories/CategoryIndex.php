<?php

namespace App\Livewire\Admin\Blog\Categories;

use App\Models\Category;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Str;

class CategoryIndex extends Component
{
    use WithPagination, WireUiActions, AuthorizesRequests;

    public $search = '';
    public $showInactive = false;
    public $sortBy = 'sort_order';
    public $sortDirection = 'asc';

    // Modal state
    public $showModal = false;
    public $editingCategory = null;
    public $isEditing = false;

    // Form fields
    public $name = '';
    public $slug = '';
    public $description = '';
    public $parent_id = '';
    public $color = '#3b82f6';
    public $icon = '';
    public $image = '';
    public $is_active = true;
    public $sort_order = '';
    public $seo_title = '';
    public $seo_description = '';
    public $meta_keywords = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
        'sortBy' => ['except' => 'sort_order'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:categories,slug',
        'description' => 'nullable|string',
        'parent_id' => 'nullable|exists:categories,id',
        'color' => 'required|string|size:7',
        'icon' => 'nullable|string|max:100',
        'image' => 'nullable|url',
        'is_active' => 'boolean',
        'sort_order' => 'nullable|integer|min:0',
        'seo_title' => 'nullable|string|max:60',
        'seo_description' => 'nullable|string|max:160',
        'meta_keywords' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'name.required' => 'Category name is required.',
        'slug.required' => 'Category slug is required.',
        'slug.unique' => 'This slug is already taken.',
        'parent_id.exists' => 'Selected parent category is invalid.',
        'color.required' => 'Category color is required.',
        'color.size' => 'Color must be a valid hex color code.',
        'seo_title.max' => 'SEO title should not exceed 60 characters.',
        'seo_description.max' => 'SEO description should not exceed 160 characters.',
    ];

    public function mount()
    {
        $this->authorize('categories.view');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingShowInactive()
    {
        $this->resetPage();
    }

    public function updatedName($value)
    {
        if (!$this->isEditing || empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
        
        if (empty($this->seo_title)) {
            $this->seo_title = Str::limit($value, 60);
        }
    }

    public function updatedDescription($value)
    {
        if (empty($this->seo_description) && $value) {
            $this->seo_description = Str::limit(strip_tags($value), 160);
        }
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getCategoriesProperty()
    {
        $query = Category::query()
            ->withCount('posts')
            ->with('parent');

        // Apply search
        if ($this->search) {
            $query->multiColumnSearch($this->search);
        }

        // Apply active filter
        if (!$this->showInactive) {
            $query->where('is_active', true);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(15);
    }

    public function getParentCategoriesProperty()
    {
        $query = Category::active()->rootCategories()->orderBy('name');
        
        if ($this->isEditing && $this->editingCategory) {
            // Exclude self and descendants when editing
            $query->where('id', '!=', $this->editingCategory->id);
            // TODO: Add logic to exclude descendants
        }
        
        return $query->get();
    }

    public function create()
    {
        $this->authorize('categories.create');
        
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function edit($categoryId)
    {
        $this->authorize('categories.update');
        
        $this->editingCategory = Category::findOrFail($categoryId);
        $this->loadCategoryData();
        $this->showModal = true;
        $this->isEditing = true;
    }

    private function loadCategoryData()
    {
        $this->name = $this->editingCategory->name;
        $this->slug = $this->editingCategory->slug;
        $this->description = $this->editingCategory->description;
        $this->parent_id = $this->editingCategory->parent_id;
        $this->color = $this->editingCategory->color;
        $this->icon = $this->editingCategory->icon;
        $this->image = $this->editingCategory->image;
        $this->is_active = $this->editingCategory->is_active;
        $this->sort_order = $this->editingCategory->sort_order;
        $this->seo_title = $this->editingCategory->seo_title;
        $this->seo_description = $this->editingCategory->seo_description;
        $this->meta_keywords = $this->editingCategory->meta_keywords;
    }

    public function save()
    {
        // Adjust validation for editing
        if ($this->isEditing) {
            $this->rules['slug'] = 'required|string|max:255|unique:categories,slug,' . $this->editingCategory->id;
        }

        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'parent_id' => $this->parent_id ?: null,
                'color' => $this->color,
                'icon' => $this->icon,
                'image' => $this->image,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order ?: null,
                'seo_title' => $this->seo_title,
                'seo_description' => $this->seo_description,
                'meta_keywords' => $this->meta_keywords,
            ];

            if ($this->isEditing) {
                $this->editingCategory->update($data);
                $message = "Category '{$this->name}' has been updated successfully.";
            } else {
                Category::create($data);
                $message = "Category '{$this->name}' has been created successfully.";
            }

            $this->notification()->success('Success', $message);
            $this->closeModal();

        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'An error occurred while saving the category. Please try again.'
            );
        }
    }

    public function toggleActive($categoryId)
    {
        $this->authorize('categories.update');
        
        $category = Category::findOrFail($categoryId);
        $category->update(['is_active' => !$category->is_active]);
        
        $status = $category->is_active ? 'activated' : 'deactivated';
        $this->notification()->success(
            'Success',
            "Category '{$category->name}' has been {$status}."
        );
    }

    public function delete($categoryId)
    {
        $this->authorize('categories.delete');
        
        $category = Category::findOrFail($categoryId);
        
        // Check if category has posts
        if ($category->posts()->count() > 0) {
            $this->notification()->error(
                'Cannot Delete',
                "Category '{$category->name}' cannot be deleted because it has posts associated with it."
            );
            return;
        }

        // Check if category has child categories
        if ($category->children()->count() > 0) {
            $this->notification()->error(
                'Cannot Delete',
                "Category '{$category->name}' cannot be deleted because it has subcategories."
            );
            return;
        }

        $this->dialog()->confirm([
            'title' => 'Delete Category',
            'description' => "Are you sure you want to delete the category '{$category->name}'? This action cannot be undone.",
            'acceptLabel' => 'Delete',
            'method' => 'confirmDelete',
            'params' => $categoryId,
        ]);
    }

    public function confirmDelete($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $name = $category->name;
        
        $category->delete();
        
        $this->notification()->success(
            'Success',
            "Category '{$name}' has been deleted."
        );
    }

    public function reorderCategories($orderedIds)
    {
        $this->authorize('categories.reorder');
        
        foreach ($orderedIds as $index => $categoryId) {
            Category::where('id', $categoryId)->update(['sort_order' => $index + 1]);
        }
        
        $this->notification()->success(
            'Success',
            'Category order has been updated.'
        );
    }

    public function generateSlug()
    {
        if ($this->name) {
            $this->slug = Str::slug($this->name);
        }
    }

    private function resetForm()
    {
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->parent_id = '';
        $this->color = '#3b82f6';
        $this->icon = '';
        $this->image = '';
        $this->is_active = true;
        $this->sort_order = '';
        $this->seo_title = '';
        $this->seo_description = '';
        $this->meta_keywords = '';
        $this->editingCategory = null;
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.admin.blog.categories.category-index', [
            'categories' => $this->categories,
            'parentCategories' => $this->parentCategories,
        ])->layout('layouts.admin');
    }
}
