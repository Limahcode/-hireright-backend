@extends('layouts.app')

@section('title', 'Blog Posts')

@section('content')
    <div class="container mx-auto my-12">
        <h1 class="text-3xl font-bold text-center mb-8">Latest Blog Posts</h1>

        @foreach($posts as $post)
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-semibold">
                    <a href="{{ route('posts.show', $post->slug) }}" class="text-blue-500 hover:underline">
                        {{ $post->title }}
                    </a>
                </h2>
                <p class="text-gray-700">
                    {{ Str::limit(strip_tags($post->content), 150) }} <!-- Show an excerpt -->
                </p>
                <p class="mt-4 text-sm text-gray-600">
                    <!-- Check if the category exists -->
                    Posted in 
                    <strong>
                        @if($post->category)
                            {{ $post->category->name }}
                        @else
                            Uncategorised
                        @endif
                    </strong>
                    on {{ $post->published_at->format('M d, Y') }}
                </p>
            </div>
        @endforeach

        <!-- Pagination -->
        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    </div>
@endsection
