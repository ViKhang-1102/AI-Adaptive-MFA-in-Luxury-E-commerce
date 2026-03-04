<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\SellerMiddleware::class);
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $products = $user->products()->with('category', 'images')->paginate(10);
        return view('seller.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        return view('seller.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:1|max:999999999',
            'stock' => 'required|integer|min:0',
            'discount_percent' => 'nullable|integer|min:1|max:100',
            'discount_start_date' => 'nullable|date|after_or_equal:today',
            'discount_end_date' => 'nullable|date|after:discount_start_date',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,gif|max:5120',
        ]);

        // Validate discount dates if percent is provided
        if ($validated['discount_percent'] && (!$validated['discount_start_date'] || !$validated['discount_end_date'])) {
            return back()->withErrors(['discount_percent' => 'Both start and end dates are required when setting a discount percentage.'])->withInput();
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $product = $user->products()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'discount_percent' => $validated['discount_percent'] ?? null,
            'discount_start_date' => $validated['discount_start_date'] ?? null,
            'discount_end_date' => $validated['discount_end_date'] ?? null,
            'slug' => str()->slug($validated['name']),
            'is_active' => true,
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/' . $product->id, 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        return redirect()->route('seller.products.show', $product)->with('success', 'Product created successfully');
    }

    public function edit(Product $product)
    {
        $userId = Auth::user()?->id;
        if ($product->seller_id !== $userId) {
            abort(403);
        }

        $categories = Category::active()->get();
        return view('seller.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $userId = Auth::user()?->id;
        if ($product->seller_id !== $userId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:1|max:999999999',
            'stock' => 'required|integer|min:0',
            'discount_percent' => 'nullable|integer|min:1|max:100',
            'discount_start_date' => 'nullable|date',
            'discount_end_date' => 'nullable|date|after:discount_start_date',
            'images' => 'nullable|array|max:10',
            'images.*' => 'nullable|image|mimes:jpeg,png,gif|max:5120',
        ]);

        // Validate discount dates if percent is provided
        if ($validated['discount_percent'] && (!$validated['discount_start_date'] || !$validated['discount_end_date'])) {
            return back()->withErrors(['discount_percent' => 'Both start and end dates are required when setting a discount percentage.'])->withInput();
        }

        // Update product details
        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'discount_percent' => $validated['discount_percent'] ?? null,
            'discount_start_date' => $validated['discount_start_date'] ?? null,
            'discount_end_date' => $validated['discount_end_date'] ?? null,
        ]);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/' . $product->id, 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        return back()->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $userId = Auth::user()?->id;
        if ($product->seller_id !== $userId) {
            abort(403);
        }

        // Previously we prevented sellers from removing products once they had been ordered.
        // order_items now use a nullable product_id with ON DELETE SET NULL, so it's safe to delete
        // the product without losing historical order data.

        // Delete all product images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image);
            $image->delete();
        }

        $product->delete();
        return back()->with('success', 'Product deleted successfully');
    }

    public function deleteImage(ProductImage $productImage)
    {
        $product = $productImage->product;
        $userId = Auth::user()?->id;
        
        if ($product->seller_id !== $userId) {
            abort(403);
        }

        // Delete file from storage
        Storage::disk('public')->delete($productImage->image);
        $productImage->delete();

        return back()->with('success', 'Image deleted successfully');
    }

    public function show(Product $product)
    {
        $userId = Auth::user()?->id;
        if ($product->seller_id !== $userId) {
            abort(403);
        }

        return view('seller.products.show', compact('product'));
    }
}
