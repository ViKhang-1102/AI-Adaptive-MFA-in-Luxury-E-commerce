@extends('layouts.app')
@section('title', 'Manage Banners')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-neutral-500 text-white rounded-md hover:bg-gray-600 font-semibold">
                ← Dashboard
            </a>
            <h1 class="text-3xl font-bold">Banners Management</h1>
        </div>
        <a href="{{ route('admin.banners.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
            <i class="fas fa-plus"></i> Add Banner
        </a>
    </div>

    @if($banners->isEmpty())
        <div class="bg-white p-6 rounded-md-lg shadow-sm text-center text-neutral-500">
            No banners found. <a href="{{ route('admin.banners.create') }}" class="text-primary hover:underline">Create one</a>
        </div>
    @else
        <div class="bg-white rounded-md-lg shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-neutral-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Title</th>
                        <th class="px-6 py-3 text-left">Image</th>
                        <th class="px-6 py-3 text-left">Sort Order</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($banners as $banner)
                    <tr class="border-b hover:bg-neutral-50">
                        <td class="px-6 py-3 font-semibold">{{ $banner->title }}</td>
                        <td class="px-6 py-3">
                            @if($banner->image)
                                <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}" class="h-12 w-20 object-cover rounded-md">
                            @else
                                <span class="text-gray-400">No image</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">{{ $banner->sort_order }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-md-full {{ $banner->is_active ? 'bg-green-100 text-green-800' : 'bg-neutral-100 text-primary' }}">
                                {{ $banner->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="px-3 py-1 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 rounded-md hover:bg-primary-light hover:-translate-y-0.5 text-sm font-semibold">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-semibold">
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
            {{ $banners->links() }}
        </div>
    @endif
</div>
@endsection
