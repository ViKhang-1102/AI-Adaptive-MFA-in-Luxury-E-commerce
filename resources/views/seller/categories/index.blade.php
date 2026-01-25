@extends('layouts.app')
@section('title', 'My Categories')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">My Categories</h1>
        <a href="{{ route('seller.categories.create') }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">+ Add Category</a>
    </div>

    @if($categories->isEmpty())
        <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
            No categories found. <a href="{{ route('seller.categories.create') }}" class="text-blue-600 hover:underline">Create one</a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Category Name</th>
                        <th class="px-6 py-3 text-left">Products</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Created</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold">{{ $category->name }}</td>
                        <td class="px-6 py-3">{{ $category->products_count ?? 0 }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3">{{ $category->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3">
                            <a href="{{ route('seller.categories.edit', $category) }}" class="text-blue-600 hover:underline text-sm">Edit</a>
                            <form method="POST" action="{{ route('seller.categories.destroy', $category) }}" class="inline ml-3" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>
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
