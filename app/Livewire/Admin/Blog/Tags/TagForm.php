<?php

namespace App\Livewire\Admin\Blog\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Str;

class TagForm extends Component
{
    use WireUiActions, AuthorizesRequests;

    public ?Tag $tag = null;
    public bool $isEditing = false;

    // Form fields
    public $name = '';
    public $slug = '';
    public $description = '';
    public $color = '#3b82f6';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:100',
        'slug' => 'required|string|max:100|unique:tags,slug',
        'description' => 'nullable|string|max:500',
        'color' => 'required|string|size:7',
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Tag name is required.',
        'name.max' => 'Tag name cannot exceed 100 characters.',
        'slug.required' => 'Tag slug is required.',
        'slug.unique' => 'This slug is already taken.',
        'color.required' => 'Tag color is required.',
        'color.size' => 'Color must be a valid hex color code.',
        'description.max' => 'Description cannot exceed 500 characters.',
    ];

    public function mount($tag = null)
    {
        if ($tag) {
            $this->tag = is_numeric($tag) ? Tag::findOrFail($tag) : Tag::where('slug', $tag)->firstOrFail();
            $this->isEditing = true;
            $this->authorize('tags.update');
            $this->loadTagData();
        } else {
            $this->authorize('tags.create');
        }
    }

    public function updatedName($value)
    {
        if (!$this->isEditing || empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function generateSlug()
    {
        if ($this->name) {
            $this->slug = Str::slug($this->name);
        }
    }

    private function loadTagData()
    {
        $this->name = $this->tag->name;
        $this->slug = $this->tag->slug;
        $this->description = $this->tag->description;
        $this->color = $this->tag->color;
        $this->is_active = $this->tag->is_active;
    }

    public function save()
    {
        // Adjust validation for editing
        if ($this->isEditing) {
            $this->rules['slug'] = 'required|string|max:100|unique:tags,slug,' . $this->tag->id;
        }

        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'color' => $this->color,
                'is_active' => $this->is_active,
            ];

            if ($this->isEditing) {
                $this->tag->update($data);
                $message = "Tag '{$this->name}' has been updated successfully.";
            } else {
                Tag::create($data);
                $message = "Tag '{$this->name}' has been created successfully.";
            }

            $this->notification()->success('Success', $message);
            
            return redirect()->route('admin.blog.tags.index');

        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'An error occurred while saving the tag. Please try again.'
            );
        }
    }

    #[Layout('layouts.admin')]
    #[Title('Tag Form')]
    public function render()
    {
        return view('livewire.admin.blog.tags.tag-form');
    }
}