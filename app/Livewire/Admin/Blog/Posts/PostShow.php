<?php

namespace App\Livewire\Admin\Blog\Posts;

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class PostShow extends Component
{
    public Post $post;

    public function mount($post = null, $slug = null)
    {
        if ($post) {
            $this->post = is_numeric($post) ? Post::findOrFail($post) : Post::where('slug', $post)->firstOrFail();
        } elseif ($slug) {
            $this->post = Post::where('slug', $slug)->firstOrFail();
        }

        if (!$this->post) {
            abort(404);
        }
    }

    #[Layout('layouts.admin')]
    #[Title('View Post')]
    public function render()
    {
        return view('livewire.admin.blog.posts.post-show');
    }
}
