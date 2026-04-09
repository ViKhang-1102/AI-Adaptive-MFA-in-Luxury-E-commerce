<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::customers()->paginate(15);
        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $customer = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'is_active' => true,
        ]);

        EWallet::create([
            'user_id' => $customer->id,
            'balance' => 0,
            'total_received' => 0,
            'total_spent' => 0,
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Customer created');
    }

    public function edit($user)
    {
        $user = User::findOrFail($user);
        
        if (!$user->isCustomer()) {
            abort(404);
        }

        return view('admin.customers.edit', compact('user'));
    }

    public function update(Request $request, $user)
    {
        $user = User::findOrFail($user);
        
        if (!$user->isCustomer()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated');
    }

    public function destroy($user)
    {
        $user = User::findOrFail($user);
        
        if (!$user->isCustomer()) {
            abort(404);
        }

        // 1. Delete Cart and items
        if ($user->cart) {
            $user->cart->items()->delete();
            $user->cart->delete();
        }

        // 2. Delete Personal Data
        $user->addresses()->delete();
        $user->wishlist()->delete();
        $user->reviews()->delete();
        $user->verifiedDevices()->delete();
        $user->securityAudits()->delete();
        $user->notifications()->delete();
        $user->messagesSent()->delete();
        $user->messagesReceived()->delete();

        // 3. Handle Orders as Customer
        $user->ordersAsCustomer()->each(function ($order) use ($user) {
            // If order is in progress, cancel it and restore stock
            if (in_array($order->status, ['pending', 'review', 'confirmed', 'paid', 'processing'])) {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
                
                // Notify seller that order was cancelled due to customer deletion
                if ($order->seller) {
                    \App\Models\OrderNotification::create([
                        'order_id' => $order->id,
                        'customer_id' => $order->customer_id, // Old ID for reference
                        'message' => "Order #{$order->order_number} has been cancelled because the customer account was deleted by administration.",
                    ]);
                    
                    // Also send a direct message if possible
                    $firstItem = $order->items->first();
                    if ($firstItem && $firstItem->product_id) {
                        \App\Models\Message::create([
                            'sender_id' => Auth::id(), // Admin
                            'receiver_id' => $order->seller_id,
                            'product_id' => $firstItem->product_id,
                            'message' => "Order #{$order->order_number} from {$user->name} was cancelled as the user account was removed.",
                        ]);
                    }
                }
            }

            $order->items()->delete();
            $order->notifications()->delete();
            $order->walletTransactions()->delete();
            if ($order->payment) {
                $order->payment->delete();
            }
            $order->delete();
        });

        // 4. Delete Wallet
        if ($user->wallet) {
            $user->wallet->delete();
        }

        // 5. Finally delete the user
        $user->delete();

        return back()->with('success', 'Customer and all associated data deleted permanently');
    }
}
