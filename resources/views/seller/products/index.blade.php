<!-- Seller Products List Stub -->
@extends('layouts.app')
@section('title', 'My Products')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <a href="{{ route('seller.dashboard') }}" class="text-primary hover:underline mb-4 inline-block">&larr; Back to Dashboard</a>
            <h1 class="text-3xl font-bold">My Products</h1>
        </div>
        <a href="{{ route('seller.products.create') }}" class="bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 px-6 py-2 rounded-md hover:bg-primary-light hover:-translate-y-0.5 transition">
            <i class="fas fa-plus mr-2"></i> Add Product
        </a>
    </div>
    <div class="bg-white rounded-md-lg shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-neutral-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left">Image</th>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Category</th>
                    <th class="px-6 py-3 text-center">Stock</th>
                    <th class="px-6 py-3 text-right">Price</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr class="border-b hover:bg-neutral-50">
                    <td class="px-6 py-3">
                        @if($product->images->first())
                        <img src="{{ asset('storage/' . $product->images->first()->image) }}" class="w-16 h-16 object-cover rounded-md" alt="{{ $product->name }}">
                        @else
                        <div class="w-16 h-16 bg-neutral-200 rounded-md flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-3">{{ $product->name }}</td>
                    <td class="px-6 py-3">{{ $product->category->name }}</td>
                    <td class="px-6 py-3 text-center">{{ $product->stock }}</td>
                    <td class="px-6 py-3 text-right">${{ number_format($product->price, 2) }}</td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('seller.products.edit', $product) }}" class="inline-block px-3 py-1 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 text-sm rounded-md hover:bg-primary-light hover:-translate-y-0.5 transition">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <form action="{{ route('seller.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition">
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
    {{ $products->links() }}
</div>
@endsection
