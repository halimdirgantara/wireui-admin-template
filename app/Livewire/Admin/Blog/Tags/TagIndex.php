<?php

namespace App\Livewire\Admin\Blog\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Str;

class TagIndex extends Component
{
    use WithPagination, WireUiActions, AuthorizesRequests;

    public $search = '';
    public $showInactive = false;
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    // Modal state
    public $showModal = false;
    public $editingTag = null;
    public $isEditing = false;

    // Form fields
    public $name = '';
    public $slug = '';
    public $description = '';
    public $color = '#3b82f6';
    public $is_active = true;

    // Merge functionality
    public $showMergeModal = false;
    public $sourceTagId = null;
    public $targetTagId = null;
    
    // Bulk operations
    public $selectedTags = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

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

    public function mount()
    {
        $this->authorize('tags.view');
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
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedTags = $this->tags->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedTags = [];
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

    public function getTagsProperty()
    {
        $query = Tag::query()->withPostCounts();

        // Apply search
        if ($this->search) {
            $query->multiColumnSearch($this->search);
        }

        // Apply active filter
        if (!$this->showInactive) {
            $query->where('is_active', true);
        }

        // Apply sorting
        if ($this->sortBy === 'posts_count') {
            $query->orderBy('posts_count', $this->sortDirection);
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query->paginate(20);
    }

    public function getAllTagsProperty()
    {
        return Tag::active()->orderBy('name')->get();
    }

    public function create()
    {
        $this->authorize('tags.create');
        
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function edit($tagId)
    {
        $this->authorize('tags.update');
        
        $this->editingTag = Tag::findOrFail($tagId);
        $this->loadTagData();
        $this->showModal = true;
        $this->isEditing = true;
    }

    private function loadTagData()
    {
        $this->name = $this->editingTag->name;
        $this->slug = $this->editingTag->slug;
        $this->description = $this->editingTag->description;
        $this->color = $this->editingTag->color;
        $this->is_active = $this->editingTag->is_active;
    }

    public function save()
    {
        // Adjust validation for editing
        if ($this->isEditing) {
            $this->rules['slug'] = 'required|string|max:100|unique:tags,slug,' . $this->editingTag->id;
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
                $this->editingTag->update($data);
                $message = "Tag '{$this->name}' has been updated successfully.";
            } else {
                Tag::create($data);
                $message = "Tag '{$this->name}' has been created successfully.";
            }

            $this->notification()->success('Success', $message);
            $this->closeModal();

        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'An error occurred while saving the tag. Please try again.'
            );
        }
    }

    public function toggleActive($tagId)
    {
        $this->authorize('tags.update');
        
        $tag = Tag::findOrFail($tagId);
        $tag->update(['is_active' => !$tag->is_active]);
        
        $status = $tag->is_active ? 'activated' : 'deactivated';
        $this->notification()->success(
            'Success',
            "Tag '{$tag->name}' has been {$status}."
        );
    }

    public function delete($tagId)
    {
        $this->authorize('tags.delete');
        
        $tag = Tag::findOrFail($tagId);
        
        $this->dialog()->confirm([
            'title' => 'Delete Tag',
            'description' => "Are you sure you want to delete the tag '{$tag->name}'? This will also remove it from all associated posts.",
            'acceptLabel' => 'Delete',
            'method' => 'confirmDelete',
            'params' => $tagId,
        ]);
    }

    public function confirmDelete($tagId)
    {
        $tag = Tag::findOrFail($tagId);
        $name = $tag->name;
        
        $tag->delete(); // This will automatically remove the pivot records
        
        $this->notification()->success(
            'Success',
            "Tag '{$name}' has been deleted."
        );

        // Remove from selected if it was selected
        $this->selectedTags = array_filter($this->selectedTags, fn($id) => $id != $tagId);
    }

    public function showMergeDialog($sourceTagId)
    {
        $this->authorize('tags.merge');
        
        $this->sourceTagId = $sourceTagId;
        $this->targetTagId = null;
        $this->showMergeModal = true;
    }

    public function mergeTags()
    {
        $this->authorize('tags.merge');
        
        if (!$this->sourceTagId || !$this->targetTagId) {
            $this->notification()->error('Error', 'Please select both source and target tags.');
            return;
        }

        if ($this->sourceTagId == $this->targetTagId) {
            $this->notification()->error('Error', 'Source and target tags must be different.');
            return;
        }

        $sourceTag = Tag::findOrFail($this->sourceTagId);
        $targetTag = Tag::findOrFail($this->targetTagId);

        try {
            // Get all posts from source tag
            $postIds = $sourceTag->posts()->pluck('post_id');
            
            // Attach these posts to target tag (with unique constraint, duplicates will be ignored)
            foreach ($postIds as $postId) {
                $targetTag->posts()->syncWithoutDetaching([$postId]);
            }
            
            // Delete source tag
            $sourceTag->delete();
            
            $this->notification()->success(
                'Success',
                "Tag '{$sourceTag->name}' has been merged into '{$targetTag->name}' and deleted."
            );
            
            $this->closeMergeModal();

        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'An error occurred while merging tags. Please try again.'
            );
        }
    }

    public function bulkDelete()
    {
        $this->authorize('tags.delete');
        
        if (empty($this->selectedTags)) {
            $this->notification()->warning('No Selection', 'Please select tags to delete.');
            return;
        }

        $this->dialog()->confirm([
            'title' => 'Delete Selected Tags',
            'description' => 'Are you sure you want to delete the selected tags? This will also remove them from all associated posts.',
            'acceptLabel' => 'Delete All',
            'method' => 'confirmBulkDelete',
        ]);
    }

    public function confirmBulkDelete()
    {
        $tags = Tag::whereIn('id', $this->selectedTags);
        $count = $tags->count();
        
        $tags->delete();
        
        $this->selectedTags = [];
        $this->selectAll = false;
        
        $this->notification()->success(
            'Success',
            "{$count} tags have been deleted."
        );
    }

    public function bulkToggleActive($makeActive = true)
    {
        $this->authorize('tags.update');
        
        if (empty($this->selectedTags)) {
            $this->notification()->warning('No Selection', 'Please select tags to update.');
            return;
        }

        $count = Tag::whereIn('id', $this->selectedTags)
                    ->update(['is_active' => $makeActive]);
        
        $action = $makeActive ? 'activated' : 'deactivated';
        
        $this->selectedTags = [];
        $this->selectAll = false;
        
        $this->notification()->success(
            'Success',
            "{$count} tags have been {$action}."
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
        $this->color = '#3b82f6';
        $this->is_active = true;
        $this->editingTag = null;
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeMergeModal()
    {
        $this->showMergeModal = false;
        $this->sourceTagId = null;
        $this->targetTagId = null;
    }

    public function render()
    {
        return view('livewire.admin.blog.tags.tag-index', [
            'tags' => $this->tags,
            'allTags' => $this->allTags,
        ])->layout('layouts.admin');
    }
}
