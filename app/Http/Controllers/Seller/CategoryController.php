<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display all admin-created categories with seller's products count
     */
    public function index()
    {
        $seller = auth()->user();
        
        // Get all active categories with count of seller's products in each
        $categories = Category::active()
            ->withCount(['products' => function ($query) use ($seller) {
                $query->where('seller_id', $seller->id);
            }])
            ->paginate(10);
        
        return view('seller.categories.index', compact('categories'));
    }

    /**
     * Show products filtered by category
     */
    public function show(Category $category)
    {
        if (!$category->is_active) {
            abort(404);
        }

        $seller = auth()->user();
        
        // Get seller's products in this category
        $products = $seller->products()
            ->where('category_id', $category->id)
            ->with('images')
            ->paginate(10);

        return view('seller.categories.show', compact('category', 'products'));
    }

    /**
     * Sellers are not allowed to create categories
     */
    public function create()
    {
        return redirect()->route('seller.categories.index')
            ->with('error', 'Sellers cannot create categories. Please use existing categories from admin.');
    }

    /**
     * Prevent store action
     */
    public function store(Request $request)
    {
        return redirect()->route('seller.categories.index')
            ->with('error', 'Sellers cannot create categories.');
    }

    /**
     * Prevent edit action
     */
    public function edit(Category $category)
    {
        return redirect()->route('seller.categories.index')
            ->with('error', 'Sellers cannot edit categories.');
    }

    /**
     * Prevent update action
     */
    public function update(Request $request, Category $category)
    {
        return redirect()->route('seller.categories.index')
            ->with('error', 'Sellers cannot edit categories.');
    }

    /**
     * Prevent delete action
     */
    public function destroy(Category $category)
    {
        return redirect()->route('seller.categories.index')
            ->with('error', 'Sellers cannot delete categories.');
    }
}
