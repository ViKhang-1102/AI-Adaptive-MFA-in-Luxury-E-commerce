<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()->inStock()->with('seller', 'images');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'newest':
                    $query->latest();
                    break;
                case 'price_low':
                    $query->orderBy('price');
                    break;
                case 'price_high':
                    $query->orderByDesc('price');
                    break;
                case 'popular':
                    $query->orderByDesc('views');
                    break;
            }
        }

        $products = $query->paginate(12);
        $categories = Category::active()->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        if (!$product->is_active || $product->stock <= 0) {
            abort(404);
        }

        $product->increment('views');

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->inStock()
            ->with('images', 'seller')
            ->limit(6)
            ->get();

        $reviews = $product->reviews()
            ->with('customer', 'images')
            ->latest()
            ->paginate(5);

        $userReview = null;
        $canReview = false;

        if (auth()->check() && auth()->user()->isCustomer()) {
            $userId = auth()->id();
            // find any delivered order for this product
            $deliveredOrder = \App\Models\Order::where('customer_id', $userId)
                ->where('status', 'delivered')
                ->whereHas('items', function($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->orderByDesc('delivered_at')
                ->first();

            if ($deliveredOrder) {
                // check if user already reviewed this order's product
                $has = $product->reviews()
                    ->where('customer_id', $userId)
                    ->where('order_id', $deliveredOrder->id)
                    ->exists();
                $canReview = !$has;
            }

            // also existing user review for convenience (most recent)
            $userReview = $product->reviews()
                ->where('customer_id', $userId)
                ->orderByDesc('created_at')
                ->first();
        }

        return view('products.show', compact('product', 'relatedProducts', 'reviews', 'userReview', 'canReview'));

    }

    public function wishlist()
    {
        $wishlists = auth()->user()->wishlist()->with('product.images', 'product.seller')->paginate(12);
        return view('products.wishlist', compact('wishlists'));
    }

    public function addWishlist($productId)
    {
        $product = Product::findOrFail($productId);

        Wishlist::firstOrCreate([
            'customer_id' => auth()->id(),
            'product_id' => $product->id,
        ]);

        return back()->with('success', 'Added to wishlist');
    }

    public function removeWishlist($productId)
    {
        Wishlist::where('customer_id', auth()->id())
            ->where('product_id', $productId)
            ->delete();

        return back()->with('success', 'Removed from wishlist');
    }
}
