<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use App\Models\ReviewImage;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        // Verify user is authenticated
        if (!Auth::check()) {
            return back()->with('error', 'Please login to leave a review');
        }

        // Find a delivered order that contains this product and which hasn't already been reviewed for this order.
        $eligibleOrder = Order::where('customer_id', Auth::id())
            ->where('status', 'delivered')
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->with(['items' => function($q) use ($product) {
                $q->where('product_id', $product->id);
            }])
            ->orderByDesc('delivered_at')
            ->first();

        if (!$eligibleOrder) {
            return back()->with('error', 'You can only review products after the order has been delivered.');
        }

        // check if review already exists for this order/product/customer
        $existing = ProductReview::where('product_id', $product->id)
            ->where('customer_id', Auth::id())
            ->where('order_id', $eligibleOrder->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already reviewed this product for this order');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'images.*' => 'nullable|image|max:2048',
        ]);

        // Create review tied to the order
        $review = ProductReview::create([
            'product_id' => $product->id,
            'customer_id' => Auth::id(),
            'order_id' => $eligibleOrder->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified_purchase' => true,
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                ReviewImage::create([
                    'review_id' => $review->id,
                    'image' => $path,
                ]);
            }
        }

        return back()->with('success', 'Thank you for your review!');
    }

    public function destroy(ProductReview $review)
    {
        if ($review->customer_id !== Auth::id()) {
            abort(403);
        }

        // Delete review images
        foreach ($review->images as $image) {
            Storage::disk('public')->delete($image->image);
            $image->delete();
        }

        $review->delete();
        return back()->with('success', 'Review deleted successfully');
    }
}
