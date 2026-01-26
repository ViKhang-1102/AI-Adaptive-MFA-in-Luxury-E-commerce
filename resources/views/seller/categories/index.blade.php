@extends('layouts.app')
@section('title', 'My Categories')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="{{ route('seller.dashboard') }}" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Back to Dashboard</a>
        <h1 class="text-3xl font-bold">Available Categories</h1>
    </div>

    @if($categories->isEmpty())
        <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
            No categories available yet. Please contact admin to create categories.
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Category Name</th>
                        <th class="px-6 py-3 text-left">Your Products</th>
                        <th class="px-6 py-3 text-left">Created</th>
                        <th class="px-6 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold">{{ $category->name }}</td>
                        <td class="px-6 py-3">{{ $category->products_count ?? 0 }}</td>
                        <td class="px-6 py-3">{{ $category->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3">
                            <a href="{{ route('seller.categories.show', $category) }}" class="text-blue-600 hover:underline text-sm">View Products</a>
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

        <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-blue-800">
                <strong>Note:</strong> Categories are managed by the admin. 
                To use more categories for your products, please contact the admin to create new ones.
            </p>
        </div>
    @endif
</div>
@endsection
