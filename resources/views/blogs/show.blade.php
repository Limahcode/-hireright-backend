@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <div class="container mx-auto my-12">
        <h1 class="text-4xl font-bold mb-6">{{ $post->title }}</h1>

        <p class="text-sm text-gray-600">
            Posted in 
            <strong>
                @if($post->category)
                    {{ $post->category->name }}
                @else
                    Uncategorised
                @endif
            </strong> 
            on 
            @if($post->published_at)
                {{ $post->published_at->format('M d, Y') }}
            @else
                Not yet published
            @endif
        </p>

        <div class="mt-8 leading-loose text-gray-800">
            {!! $post->content !!} <!-- Allow HTML content -->
        </div>

        <div class="mt-8">
            <a href="{{ route('posts.index') }}" class="text-blue-500 hover:underline">Back to Blog</a>
        </div>
    </div>
@endsection
