@extends('layouts.app')
@section('title', $category->name . ' - My Products')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="{{ route('seller.categories.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Back to Categories</a>
        <h1 class="text-3xl font-bold">{{ $category->name }}</h1>
    </div>

    @if($products->isEmpty())
        <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
            <p>You don't have any products in this category yet.</p>
            <a href="{{ route('seller.products.create') }}" class="text-blue-600 hover:underline">Create a product</a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Product Name</th>
                        <th class="px-6 py-3 text-center">Stock</th>
                        <th class="px-6 py-3 text-right">Price</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3">{{ $product->name }}</td>
                        <td class="px-6 py-3 text-center">{{ $product->stock }}</td>
                        <td class="px-6 py-3 text-right">${{ number_format($product->price / env('VND_PER_USD', 23000), 2) }}</td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ route('seller.products.edit', $product) }}" class="inline-block px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <form action="{{ route('seller.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
