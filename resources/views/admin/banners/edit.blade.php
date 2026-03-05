@extends('layouts.app')
@section('title', 'Edit Banner')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <a href="{{ route('admin.banners.index') }}" class="text-primary hover:underline mb-4 inline-block">← Back</a>
    <h1 class="text-3xl font-bold mb-8">Edit Banner</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-6 rounded-md-lg shadow-sm">
        <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block font-bold mb-2">Title</label>
                <input type="text" name="title" value="{{ old('title', $banner->title) }}"
                    class="w-full px-4 py-2 border rounded-md @error('title') border-red-500 @enderror" required>
                @error('title')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block font-bold mb-2">Current Image</label>
                @if($banner->image)
                    <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}" class="h-32 w-48 object-cover rounded-md mb-2">
                @endif
                <label class="block font-bold mb-2">Change Image (Optional)</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full px-4 py-2 border rounded-md @error('image') border-red-500 @enderror">
                @error('image')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block font-bold mb-2">Link (Optional)</label>
                <input type="url" name="link" value="{{ old('link', $banner->link) }}"
                    class="w-full px-4 py-2 border rounded-md" placeholder="https://example.com">
            </div>

            <div class="mb-4">
                <label class="block font-bold mb-2">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order) }}"
                    class="w-full px-4 py-2 border rounded-md">
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}
                        class="mr-2">
                    <span class="font-bold">Active</span>
                </label>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="px-6 py-2 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 rounded-md hover:bg-primary-light hover:-translate-y-0.5 font-semibold">
                    <i class="fas fa-save"></i> Update Banner
                </button>
                <a href="{{ route('admin.banners.index') }}" class="px-6 py-2 bg-neutral-500 text-white rounded-md hover:bg-gray-600 font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
