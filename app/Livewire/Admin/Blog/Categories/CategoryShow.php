<?php

namespace App\Livewire\Admin\Blog\Categories;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class CategoryShow extends Component
{
    public Category $category;

    public function mount($category)
    {
        $this->category = is_numeric($category) ? Category::findOrFail($category) : Category::where('slug', $category)->firstOrFail();
        
        if (!$this->category) {
            abort(404);
        }
    }

    #[Layout('layouts.admin')]
    #[Title('View Category')]
    public function render()
    {
        return view('livewire.admin.blog.categories.category-show');
    }
}