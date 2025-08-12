<?php

namespace App\Livewire\Admin\Blog\Categories;

use App\Models\Category;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Str;

class CategoryForm extends Component
{
    use WireUiActions, AuthorizesRequests;

    public ?Category $category = null;
    public bool $isEditing = false;

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

    public function mount($category = null)
    {
        if ($category) {
            $this->category = is_numeric($category) ? Category::findOrFail($category) : Category::where('slug', $category)->firstOrFail();
            $this->isEditing = true;
            $this->authorize('categories.update');
            $this->loadCategoryData();
        } else {
            $this->authorize('categories.create');
        }
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

    public function generateSlug()
    {
        if ($this->name) {
            $this->slug = Str::slug($this->name);
        }
    }

    private function loadCategoryData()
    {
        $this->name = $this->category->name;
        $this->slug = $this->category->slug;
        $this->description = $this->category->description;
        $this->parent_id = $this->category->parent_id;
        $this->color = $this->category->color;
        $this->icon = $this->category->icon;
        $this->image = $this->category->image;
        $this->is_active = $this->category->is_active;
        $this->sort_order = $this->category->sort_order;
        $this->seo_title = $this->category->seo_title;
        $this->seo_description = $this->category->seo_description;
        $this->meta_keywords = $this->category->meta_keywords;
    }

    public function getParentCategoriesProperty()
    {
        $query = Category::active()->rootCategories()->orderBy('name');
        
        if ($this->isEditing && $this->category) {
            // Exclude self and descendants when editing
            $query->where('id', '!=', $this->category->id);
        }
        
        return $query->get();
    }

    public function save()
    {
        // Adjust validation for editing
        if ($this->isEditing) {
            $this->rules['slug'] = 'required|string|max:255|unique:categories,slug,' . $this->category->id;
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
                $this->category->update($data);
                $message = "Category '{$this->name}' has been updated successfully.";
            } else {
                Category::create($data);
                $message = "Category '{$this->name}' has been created successfully.";
            }

            $this->notification()->success('Success', $message);
            
            return redirect()->route('admin.blog.categories.index');

        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'An error occurred while saving the category. Please try again.'
            );
        }
    }

    #[Layout('layouts.admin')]
    #[Title('Category Form')]
    public function render()
    {
        return view('livewire.admin.blog.categories.category-form', [
            'parentCategories' => $this->parentCategories,
        ]);
    }
}