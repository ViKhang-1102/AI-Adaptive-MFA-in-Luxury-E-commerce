<!-- Seller Products List Stub -->
@extends('layouts.app')
@section('title', 'My Products')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">My Products</h1>
        <a href="{{ route('seller.products.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded">
            Add Product
        </a>
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Category</th>
                    <th class="px-6 py-3 text-center">Stock</th>
                    <th class="px-6 py-3 text-right">Price</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr class="border-b">
                    <td class="px-6 py-3">{{ $product->name }}</td>
                    <td class="px-6 py-3">{{ $product->category->name }}</td>
                    <td class="px-6 py-3 text-center">{{ $product->stock }}</td>
                    <td class="px-6 py-3 text-right">${{ number_format($product->price, 2) }}</td>
                    <td class="px-6 py-3 text-center">
                        <a href="{{ route('seller.products.edit', $product) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('seller.products.destroy', $product) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $products->links() }}
</div>
@endsection
