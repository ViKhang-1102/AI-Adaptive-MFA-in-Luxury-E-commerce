<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::active()->orderBy('sort_order')->get();
        $categories = Category::active()->parent()->with('children')->get();
        
        // Top selling products (by order count)
        $topProducts = Product::active()
            ->inStock()
            ->with('seller', 'images')
            ->select('products.*')
            ->selectRaw('(SELECT COUNT(*) FROM order_items WHERE product_id = products.id) as order_count')
            ->orderByRaw('(SELECT COUNT(*) FROM order_items WHERE product_id = products.id) DESC')
            ->limit(10)
            ->get();

        // Discounted products
        $discountedProducts = Product::onDiscount()
            ->active()
            ->inStock()
            ->with('seller', 'images')
            ->orderBy('discount_percent', 'desc')
            ->limit(10)
            ->get();

        // All products with pagination
        $products = Product::active()
            ->inStock()
            ->with('seller', 'images')
            ->latest()
            ->paginate(12);

        return view('home', compact(
            'banners',
            'categories',
            'topProducts',
            'discountedProducts',
            'products'
        ));
    }
}
