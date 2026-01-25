<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerCategory;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = auth()->user()->sellerCategories()->with('category')->paginate(10);
        return view('seller.categories.index', compact('categories'));
    }

    public function create()
    {
        $adminCategories = Category::active()->get();
        return view('seller.categories.create', compact('adminCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id|unique:seller_categories,category_id,NULL,id,seller_id,' . auth()->id(),
            'description' => 'nullable|string',
        ]);

        SellerCategory::create([
            'seller_id' => auth()->id(),
            ...$validated,
            'is_active' => true,
        ]);

        return redirect()->route('seller.categories.index')->with('success', 'Category added');
    }

    public function edit(SellerCategory $category)
    {
        if ($category->seller_id !== auth()->id()) {
            abort(403);
        }

        $adminCategories = Category::active()->get();
        return view('seller.categories.edit', compact('category', 'adminCategories'));
    }

    public function update(Request $request, SellerCategory $category)
    {
        if ($category->seller_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return back()->with('success', 'Category updated');
    }

    public function destroy(SellerCategory $category)
    {
        if ($category->seller_id !== auth()->id()) {
            abort(403);
        }

        $category->delete();
        return back()->with('success', 'Category deleted');
    }

    public function show(SellerCategory $category)
    {
        if ($category->seller_id !== auth()->id()) {
            abort(403);
        }

        return view('seller.categories.show', compact('category'));
    }
}
