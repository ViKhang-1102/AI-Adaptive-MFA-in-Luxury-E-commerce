@extends('layouts.app')
@section('title', 'Manage Categories')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 font-semibold">
                ← Dashboard
            </a>
            <h1 class="text-3xl font-bold">Categories Management</h1>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-semibold">
            <i class="fas fa-plus"></i> Add Category
        </a>
    </div>

    @if($categories->isEmpty())
        <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
            No categories found. <a href="{{ route('admin.categories.create') }}" class="text-blue-600 hover:underline">Create one</a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Category Name</th>
                        <th class="px-6 py-3 text-left">Parent</th>
                        <th class="px-6 py-3 text-left">Products</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold">{{ $category->name }}</td>
                        <td class="px-6 py-3">{{ $category->parent->name ?? 'Root' }}</td>
                        <td class="px-6 py-3">{{ $category->products_count ?? 0 }}</td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-semibold">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category and all its products?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm font-semibold">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection
