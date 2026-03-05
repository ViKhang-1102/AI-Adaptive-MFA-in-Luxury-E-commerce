@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Categories</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($categories as $category)
        <a href="{{ route('categories.show', $category) }}" 
            class="bg-white p-6 rounded-md-lg shadow-sm hover:shadow-sm-lg transition text-center">
            <i class="fas fa-folder text-6xl text-primary mb-4"></i>
            <h3 class="font-bold text-lg">{{ $category->name }}</h3>
            @if($category->children->count() > 0)
            <p class="text-sm text-neutral-600">{{ $category->children->count() }} subcategories</p>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endsection
