@extends('layouts.app')
@section('title', 'Edit Category')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <a href="{{ route('seller.categories.index') }}" class="text-blue-600 hover:underline mb-6 inline-block">&larr; Back to Categories</a>
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <h1 class="text-2xl font-bold text-red-800 mb-4">Access Denied</h1>
        <p class="text-red-700 mb-4">
            Sellers are not allowed to edit categories. Categories are managed by the admin only.
        </p>
        <p class="text-gray-600 mb-6">
            If you need changes to a category, please contact the administrator.
        </p>
        <a href="{{ route('seller.categories.index') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            ← Back to Categories
        </a>
    </div>
</div>
@endsection
