<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // Get paginated list of posts
    public function index(Request $request)
    {
        $userId = Auth::user()->id;
        $posts = Post::where('status', 'published')
            ->with('category:id,name') 
            ->orderBy('published_at', 'desc')
            ->paginate($request->query('size', 10));

        return response()->json([
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'count' => $posts->count(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'total_pages' => $posts->lastPage(),
                'next_page_url' => $posts->nextPageUrl(),
                'prev_page_url' => $posts->previousPageUrl(),
            ],
        ]);
    }

    // Show a single post by slug
    public function show($slug)
    {
        $userId = Auth::user()->id;
        $post = Post::where('slug', $slug)
            ->with('category') // eager load category
            ->firstOrFail();

        return response()->json($post);
    }

    // Store a new post
    public function store(Request $request)
    {
        $userId = Auth::user()->id;
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
        ]);

        $post = Post::create(array_merge($validated, ['author_id' => $userId]));

        // Handle tags
        if ($request->has('tags')) {
            $post->tags()->sync($request->input('tags'));
        }

        return response()->json(['message' => 'Post created successfully', 'post' => $post], 201);
    }
}
