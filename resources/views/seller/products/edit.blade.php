@extends('layouts.app')
@section('title', 'Edit Product')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <a href="{{ route('seller.products.index') }}" class="text-primary hover:underline mb-6 inline-block">&larr; Back to Products</a>
    <h1 class="text-3xl font-bold mb-8">Edit Product</h1>
    
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md relative mb-6" role="alert">
            <strong class="font-bold">Oops!</strong>
            <span class="block sm:inline">There were some problems with your input.</span>
            <ul class="list-disc mt-2 ml-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-6 rounded-md-lg shadow-sm">
        <form action="{{ route('seller.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Product Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-neutral-700 mb-2">Product Name *</label>
                <input type="text" id="name" name="name" class="w-full px-4 py-2 border border-neutral-200 rounded-md-lg focus:ring-2 focus:ring-gold focus:border-transparent @error('name') border-red-500 @enderror" 
                    value="{{ old('name', $product->name) }}" required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-neutral-700 mb-2">Description *</label>
                <textarea id="description" name="description" rows="5" class="w-full px-4 py-2 border border-neutral-200 rounded-md-lg focus:ring-2 focus:ring-gold focus:border-transparent @error('description') border-red-500 @enderror" 
                    required>{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div class="mb-6">
                <label for="category_id" class="block text-sm font-medium text-neutral-700 mb-2">Category *</label>
                <select id="category_id" name="category_id" class="w-full px-4 py-2 border border-neutral-200 rounded-md-lg focus:ring-2 focus:ring-gold focus:border-transparent @error('category_id') border-red-500 @enderror" 
                    required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Price -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="price" class="block text-sm font-medium text-neutral-700 mb-2">Price (USD) *</label>
                    <input type="number" id="price" name="price" step="any" min="1" max="999999999" 
                        class="w-full px-4 py-2 border border-neutral-200 rounded-md-lg focus:ring-2 focus:ring-gold focus:border-transparent @error('price') border-red-500 @enderror" 
                        value="{{ old('price', $product->price) }}" placeholder="Enter USD price" required>
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stock -->
                <div>
                    <label for="stock" class="block text-sm font-medium text-neutral-700 mb-2">Stock *</label>
                    <input type="number" id="stock" name="stock" min="0" class="w-full px-4 py-2 border border-neutral-200 rounded-md-lg focus:ring-2 focus:ring-gold focus:border-transparent @error('stock') border-red-500 @enderror" 
                        value="{{ old('stock', $product->stock) }}" required>
                    @error('stock')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Discount -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div>
                    <label for="discount_percent" class="block text-sm font-medium text-neutral-700 mb-2">Discount % (1-100)</label>
                    <input type="number" id="discount_percent" name="discount_percent" min="1" max="100" 
                        class="w-full px-4 py-2 border border-neutral-200 rounded-md-lg focus:ring-2 focus:ring-gold focus:border-transparent @error('discount_percent') border-red-500 @enderror" 
                        value="{{ old('discount_percent', $product->discount_percent) }}" placeholder="Leave blank if no discount">
                    <p class="text-xs text-neutral-500 mt-1">Requires both start and end dates</p>
                    @error('discount_percent')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_start_date" class="block text-sm font-medium text-neutral-700 mb-2">Discount Start Date</label>
                    <input type="date" id="discount_start_date" name="discount_start_date" 
                        class="w-full px-4 py-2 border border-neutral-200 rounded-md-lg focus:ring-2 focus:ring-gold focus:border-transparent @error('discount_start_date') border-red-500 @enderror" 
                        value="{{ old('discount_start_date', $product->discount_start_date?->format('Y-m-d')) }}">
                    <p class="text-xs text-neutral-500 mt-1">Cannot be in the past</p>
                    @error('discount_start_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_end_date" class="block text-sm font-medium text-neutral-700 mb-2">Discount End Date</label>
                    <input type="date" id="discount_end_date" name="discount_end_date" 
                        class="w-full px-4 py-2 border border-neutral-200 rounded-md-lg focus:ring-2 focus:ring-gold focus:border-transparent @error('discount_end_date') border-red-500 @enderror" 
                        value="{{ old('discount_end_date', $product->discount_end_date?->format('Y-m-d')) }}">
                    <p class="text-xs text-neutral-500 mt-1">Must be after start date</p>
                    @error('discount_end_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Current Images -->
            @if($product->images->count() > 0)
            <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Current Images</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($product->images as $image)
                    <div class="relative bg-neutral-100 rounded-md-lg overflow-hidden" style="aspect-ratio: 1;">
                        <img src="{{ asset('storage/' . $image->image) }}" class="w-full h-full object-cover">
                        <button type="button" 
                            onclick="if(confirm('Delete this image?')) { document.getElementById('delete-image-form').action = '{{ route('seller.products.deleteImage', $image) }}'; document.getElementById('delete-image-form').submit(); }"
                            class="absolute top-2 right-2 bg-red-600 text-white rounded-md-full w-6 h-6 flex items-center justify-center hover:bg-red-700 z-10">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Add More Images -->
            <div class="mb-6">
                <label for="new_images" class="block text-sm font-medium text-neutral-700 mb-2">Add More Images</label>
                <div class="border-2 border-dashed border-neutral-200 rounded-md-lg p-6 text-center cursor-pointer hover:border-gold transition" id="drop-zone">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                    <p class="text-neutral-600 mb-1">Drag and drop images here or click to select</p>
                    <p class="text-sm text-neutral-500">Supported formats: JPG, PNG, GIF (Max 10 images total)</p>
                    <input type="file" id="new_images" name="images[]" multiple accept="image/jpeg,image/png,image/gif" class="hidden">
                </div>
                @error('images')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('images.*')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                <!-- New Image Preview -->
                <div id="image-preview" class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-6"></div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 px-6 py-2 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 rounded-md-lg hover:bg-primary-light hover:-translate-y-0.5 transition">
                    Update Product
                </button>
                <a href="{{ route('seller.products.index') }}" class="flex-1 px-6 py-2 bg-neutral-500 text-white rounded-md-lg hover:bg-gray-600 transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<form id="delete-image-form" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    const dropZone = document.getElementById('drop-zone');
    const imageInput = document.getElementById('new_images');
    const preview = document.getElementById('image-preview');
    let selectedFiles = []; // Store selected files
    const existingImageCount = parseInt('{{ $product->images->count() }}'); // Count existing images

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = Array.from(dt.files);
        addFilesToSelection(files);
    });

    dropZone.addEventListener('click', () => imageInput.click());

    imageInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        addFilesToSelection(files);
    });

    function addFilesToSelection(newFiles) {
        // Calculate max allowed new images (10 total - existing images)
        const maxNewImages = 10 - existingImageCount;
        
        // Add new files to selectedFiles array
        newFiles.forEach(file => {
            if (file.type.startsWith('image/') && selectedFiles.length < maxNewImages) {
                selectedFiles.push(file);
            }
        });

        // Update file input with DataTransfer
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        imageInput.files = dataTransfer.files;

        // Display previews
        displayPreviews();
    }

    function displayPreviews() {
        preview.innerHTML = '';

        if (selectedFiles.length === 0) {
            preview.innerHTML = '<p class="col-span-full text-neutral-500 text-center py-4">No new images selected</p>';
            return;
        }

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'relative bg-neutral-100 rounded-md-lg overflow-hidden';
                div.style.aspectRatio = '1';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" class="absolute top-2 right-2 bg-red-600 text-white rounded-md-full w-6 h-6 flex items-center justify-center hover:bg-red-700" data-index="${index}">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                    <div class="absolute top-2 left-2 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 rounded-md px-2 py-1 text-xs font-bold">${existingImageCount + index + 1}</div>
                `;
                
                // Add remove event listener
                div.querySelector('button').addEventListener('click', (e) => {
                    e.preventDefault();
                    const idx = parseInt(e.currentTarget.dataset.index);
                    selectedFiles.splice(idx, 1);
                    
                    // Update file input
                    const dataTransfer = new DataTransfer();
                    selectedFiles.forEach(file => {
                        dataTransfer.items.add(file);
                    });
                    imageInput.files = dataTransfer.files;
                    
                    displayPreviews();
                });
                
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
</script>
@endsection
