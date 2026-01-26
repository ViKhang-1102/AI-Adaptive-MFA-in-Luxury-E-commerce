<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SellerController extends Controller
{
    public function index()
    {
        $sellers = User::sellers()->paginate(15);
        return view('admin.sellers.index', compact('sellers'));
    }

    public function create()
    {
        return view('admin.sellers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $seller = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'role' => 'seller',
            'is_active' => true,
        ]);

        EWallet::create([
            'user_id' => $seller->id,
            'balance' => 0,
            'total_received' => 0,
            'total_spent' => 0,
        ]);

        return redirect()->route('admin.sellers.index')->with('success', 'Seller created');
    }

    public function edit(User $seller)
    {
        if (!$seller->isSeller()) {
            abort(404);
        }

        return view('admin.sellers.edit', compact('seller'));
    }

    public function update(Request $request, User $seller)
    {
        if (!$seller->isSeller()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $seller->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $seller->update($validated);

        return redirect()->route('admin.sellers.index')->with('success', 'Seller updated');
    }

    public function destroy(User $seller)
    {
        if (!$seller->isSeller()) {
            abort(404);
        }

        // Delete all products and their images
        $seller->products()->each(function ($product) {
            $product->images()->delete();
            $product->reviews()->delete();
            $product->cartItems()->delete();
            $product->orderItems()->delete();
            $product->wishlistItems()->delete();
            $product->delete();
        });

        // Delete seller categories
        $seller->sellerCategories()->delete();

        // Delete orders and their items
        $seller->ordersAsSeller()->each(function ($order) {
            $order->items()->delete();
            $order->delete();
        });

        // Delete wallet
        if ($seller->wallet) {
            $seller->wallet->delete();
        }

        // Delete seller permanently
        $seller->delete();

        return back()->with('success', 'Seller deleted permanently');
    }
}
