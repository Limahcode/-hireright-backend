<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class WebController extends Controller
{
    // Get paginated list of posts
    public function index(Request $request)
    {
        $posts = Post::where('status', 'published')
            ->with(['category' => function ($query) {
                // Ensure it returns even if the category is null
                $query->select('id', 'name');
            }])
            ->orderBy('published_at', 'desc')
            ->paginate($request->query('size', 10));

        // Return a view for listing posts with pagination
        return view('blogs.index', [
            'posts' => $posts
        ]);
    }


    // Show a single post by slug
    public function show($slug)
    {
        $post = Post::where('slug', $slug)
            ->with('category') // eager load category
            ->firstOrFail();

        // Return a view for displaying a single post
        return view('blogs.show', [
            'post' => $post
        ]);
    }
}
