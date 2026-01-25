@extends('layouts.app')
@section('title', 'Manage Sellers')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Sellers Management</h1>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('admin.sellers.index') }}" class="flex gap-4">
            <input type="text" name="search" placeholder="Search by name or email" class="flex-1 px-4 py-2 border rounded" value="{{ request('search') }}">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Search</button>
        </form>
    </div>

    @if($sellers->isEmpty())
        <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
            No sellers found.
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Shop Name</th>
                        <th class="px-6 py-3 text-left">Owner</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-left">Products</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Joined</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sellers as $seller)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold">{{ $seller->shop_name ?? $seller->name }}</td>
                        <td class="px-6 py-3">{{ $seller->name }}</td>
                        <td class="px-6 py-3">{{ $seller->email }}</td>
                        <td class="px-6 py-3">{{ $seller->products_count ?? 0 }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-full {{ $seller->is_active ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $seller->is_active ? 'Active' : 'Pending' }}
                            </span>
                        </td>
                        <td class="px-6 py-3">{{ $seller->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.sellers.show', $seller) }}" class="text-blue-600 hover:underline text-sm">View</a>
                            <a href="{{ route('admin.sellers.edit', $seller) }}" class="ml-3 text-blue-600 hover:underline text-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.sellers.destroy', $seller) }}" class="inline ml-3" onsubmit="return confirm('Are you sure?')">
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
            {{ $sellers->links() }}
        </div>
    @endif
</div>
@endsection
