<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = Auth::user()->cart ?? Cart::create(['customer_id' => Auth::id()]);
        $items = $cart->items()->with('product.seller', 'product.images')->get();

        $subtotal = $items->sum(function ($item) {
            return $item->product->getDiscountedPrice() * $item->quantity;
        });

        return view('cart.index', compact('cart', 'items', 'subtotal'));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if (!$product->is_active || $product->stock < $validated['quantity']) {
            return back()->with('error', 'Product not available');
        }

        $cart = Auth::user()->cart ?? Cart::create(['customer_id' => Auth::id()]);

        $cartItem = $cart->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            if ($newQuantity > $product->stock) {
                return back()->with('error', 'Insufficient stock');
            }
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
            ]);
        }

        return back()->with('success', 'Added to cart');
    }

    public function update(Request $request, CartItem $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($item->product->stock < $validated['quantity']) {
            return back()->with('error', 'Insufficient stock');
        }

        $item->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Cart updated');
    }

    public function remove(CartItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item removed from cart');
    }
}
