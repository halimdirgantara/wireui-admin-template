<?php

namespace App\Livewire\Admin\Blog\Tags;

use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class TagShow extends Component
{
    public Tag $tag;

    public function mount($tag)
    {
        $this->tag = is_numeric($tag) ? Tag::findOrFail($tag) : Tag::where('slug', $tag)->firstOrFail();
        
        if (!$this->tag) {
            abort(404);
        }
    }

    #[Layout('layouts.admin')]
    #[Title('View Tag')]
    public function render()
    {
        return view('livewire.admin.blog.tags.tag-show');
    }
}