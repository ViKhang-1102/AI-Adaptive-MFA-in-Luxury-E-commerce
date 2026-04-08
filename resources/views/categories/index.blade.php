@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-neutral-500 hover:text-primary transition-colors group">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
            <span>Back to Home</span>
        </a>
    </div>
    <div class="flex items-center gap-4 mb-8">
        <h1 class="text-3xl font-bold">Categories</h1>
    </div>

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
