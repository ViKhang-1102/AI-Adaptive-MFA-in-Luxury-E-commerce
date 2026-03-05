@extends('layouts.app')
@section('title', 'Create Category')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <a href="{{ route('seller.categories.index') }}" class="text-primary hover:underline mb-6 inline-block">&larr; Back to Categories</a>
    <div class="bg-red-50 border border-red-200 rounded-md-lg p-6">
        <h1 class="text-2xl font-bold text-red-800 mb-4">Access Denied</h1>
        <p class="text-red-700 mb-4">
            Sellers are not allowed to create categories. Categories are managed by the admin only.
        </p>
        <p class="text-neutral-600 mb-6">
            To use additional categories for your products, please contact the administrator to request new categories to be created.
        </p>
        <a href="{{ route('seller.categories.index') }}" class="inline-block px-4 py-2 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 rounded-md hover:bg-primary-light hover:-translate-y-0.5 transition">
            ← Back to Categories
        </a>
    </div>
</div>
@endsection
