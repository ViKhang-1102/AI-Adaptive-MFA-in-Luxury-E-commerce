<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::active()->parent()->with('children')->get();
        return view('categories.index', compact('categories'));
    }

    public function show(Category $category)
    {
        if (!$category->is_active) {
            abort(404);
        }

        $products = $category->products()
            ->active()
            ->inStock()
            ->with('seller', 'images')
            ->paginate(12);

        $relatedCategories = Category::where('parent_id', $category->parent_id ?? $category->id)
            ->where('id', '!=', $category->id)
            ->active()
            ->limit(6)
            ->get();

        return view('categories.show', compact('category', 'products', 'relatedCategories'));
    }
}
