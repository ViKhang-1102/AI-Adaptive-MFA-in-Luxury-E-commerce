<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = auth()->user()->products()->with('category', 'images')->paginate(10);
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
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'discount_start_date' => 'nullable|date',
            'discount_end_date' => 'nullable|date|after:discount_start_date',
        ]);

        $product = auth()->user()->products()->create([
            ...$validated,
            'slug' => str()->slug($validated['name']),
            'is_active' => true,
        ]);

        return redirect()->route('seller.products.show', $product)->with('success', 'Product created');
    }

    public function edit(Product $product)
    {
        if ($product->seller_id !== auth()->id()) {
            abort(403);
        }

        $categories = Category::active()->get();
        return view('seller.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        if ($product->seller_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'discount_start_date' => 'nullable|date',
            'discount_end_date' => 'nullable|date|after:discount_start_date',
        ]);

        $product->update($validated);

        return back()->with('success', 'Product updated');
    }

    public function destroy(Product $product)
    {
        if ($product->seller_id !== auth()->id()) {
            abort(403);
        }

        $product->delete();
        return back()->with('success', 'Product deleted');
    }

    public function show(Product $product)
    {
        if ($product->seller_id !== auth()->id()) {
            abort(403);
        }

        return view('seller.products.show', compact('product'));
    }
}
