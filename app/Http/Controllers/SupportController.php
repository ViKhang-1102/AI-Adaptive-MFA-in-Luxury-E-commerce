<?php

namespace App\Http\Controllers;

use App\Mail\SupportRequestMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function showContactForm(Request $request)
    {
        $orderId = $request->query('order_id');
        $order = null;
        $messages = collect();

        if ($orderId) {
            $order = Order::where('id', $orderId)
                ->where('customer_id', Auth::id())
                ->first();
        }

        // Show any recent admin messages related to this order (by product)
        if ($order) {
            $productIds = $order->items->pluck('product_id')->unique()->toArray();
            $adminIds = User::where('role', 'admin')->pluck('id')->toArray();

            $messages = \App\Models\Message::whereIn('product_id', $productIds)
                ->where(function ($q) use ($adminIds) {
                    $q->whereIn('sender_id', $adminIds)
                        ->orWhereIn('receiver_id', $adminIds);
                })
                ->where(function ($q) {
                    $q->where('sender_id', Auth::id())
                      ->orWhere('receiver_id', Auth::id());
                })
                ->orderByDesc('created_at')
                ->get();
        }

        return view('support.contact', compact('order', 'messages'));
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'order_id' => 'nullable|integer|exists:orders,id',
        ]);

        $user = Auth::user();

        $order = null;
        if (!empty($validated['order_id'])) {
            $order = Order::where('id', $validated['order_id'])
                ->where('customer_id', $user->id)
                ->first();
        }

        // Store as message for quick admin response
        $productId = $order?->items->first()?->product_id;
        if ($productId) {
            \App\Models\Message::create([
                'sender_id' => $user->id,
                'receiver_id' => User::where('role', 'admin')->first()?->id,
                'product_id' => $productId,
                'message' => $validated['message'],
            ]);
        }

        $adminEmails = User::where('role', 'admin')->pluck('email')->filter()->toArray();

        if (!empty($adminEmails)) {
            try {
                Mail::to($adminEmails)->send(new SupportRequestMail($user, $validated['subject'], $validated['message'], $order));
            } catch (\Exception $e) {
                return back()->with('error', 'Unable to send your message right now. Please try again later.');
            }
        }

        return back()->with('success', 'Your request has been sent to our support team. We will contact you soon.');
    }

    public function messages(Request $request)
    {
        $orderId = $request->query('order_id');
        $order = null;

        if ($orderId) {
            $order = Order::where('id', $orderId)
                ->where('customer_id', Auth::id())
                ->first();
        }

        if (!$order) {
            return response()->json(['messages' => []]);
        }

        $productIds = $order->items->pluck('product_id')->unique()->toArray();
        $adminIds = User::where('role', 'admin')->pluck('id')->toArray();

        $messages = \App\Models\Message::whereIn('product_id', $productIds)
            ->where(function ($q) use ($adminIds) {
                $q->whereIn('sender_id', $adminIds)
                    ->orWhereIn('receiver_id', $adminIds);
            })
            ->where(function ($q) {
                $q->where('sender_id', Auth::id())
                  ->orWhere('receiver_id', Auth::id());
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['messages' => $messages]);
    }
}
