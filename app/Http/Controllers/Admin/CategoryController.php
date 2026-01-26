<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent', 'children')->whereNull('parent_id')->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create([
            ...$validated,
            'slug' => str()->slug($validated['name']),
            'is_active' => true,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category created');
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::where('id', '!=', $category->id)
            ->whereNull('parent_id')
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated');
    }

    public function destroy(Category $category)
    {
        // Delete all products in this category and their images
        $category->products()->each(function ($product) {
            $product->images()->delete();
            $product->reviews()->delete();
            $product->cartItems()->delete();
            $product->orderItems()->delete();
            $product->wishlistItems()->delete();
            $product->delete();
        });
        
        // Delete subcategories and their products
        foreach ($category->children as $child) {
            $child->products()->each(function ($product) {
                $product->images()->delete();
                $product->reviews()->delete();
                $product->cartItems()->delete();
                $product->orderItems()->delete();
                $product->wishlistItems()->delete();
                $product->delete();
            });
            $child->delete();
        }

        $category->delete();
        return back()->with('success', 'Category and all related products deleted');
    }
}
