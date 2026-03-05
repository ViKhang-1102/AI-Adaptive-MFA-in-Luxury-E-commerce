@extends('layouts.app')
@section('title', 'Edit Category')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <a href="{{ route('admin.categories.index') }}" class="text-primary hover:underline mb-4 inline-block">← Back</a>
    <h1 class="text-3xl font-bold mb-8">Edit Category</h1>

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
        <form action="{{ route('admin.categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block font-bold mb-2">Category Name</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}"
                    class="w-full px-4 py-2 border rounded-md @error('name') border-red-500 @enderror" required>
                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block font-bold mb-2">Parent Category</label>
                <select name="parent_id" class="w-full px-4 py-2 border rounded-md">
                    <option value="">-- No Parent (Root Category) --</option>
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="px-6 py-2 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 rounded-md hover:bg-primary-light hover:-translate-y-0.5 font-semibold">
                    <i class="fas fa-save"></i> Update Category
                </button>
                <a href="{{ route('admin.categories.index') }}" class="px-6 py-2 bg-neutral-500 text-white rounded-md hover:bg-gray-600 font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
