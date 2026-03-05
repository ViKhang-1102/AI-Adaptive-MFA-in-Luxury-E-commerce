<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
            'bio' => 'nullable|string|max:500',
            'paypal_email' => 'nullable|email|max:255',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        // Only allow sellers to set paypal_email, but accept the field safely
        /** @var User $current */
        $current = Auth::user();
        if (!$current || !$current->isSeller()) {
            unset($validated['paypal_email']);
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|confirmed|min:6|different:current_password',
        ]);

        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully');
    }

    public function addresses()
    {
        /** @var User $addrUser */
        $addrUser = Auth::user();
        $addresses = $addrUser->addresses;
        return view('addresses.index', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            CustomerAddress::where('customer_id', Auth::id())->update(['is_default' => false]);
        }

        CustomerAddress::create([
            'customer_id' => Auth::id(),
            ...$validated,
            'is_default' => $isDefault,
        ]);

        return back()->with('success', 'Address added successfully');
    }

    public function editAddress(CustomerAddress $address)
    {
        if ($address->customer_id !== Auth::id()) {
            abort(403);
        }

        return view('addresses.edit', compact('address'));
    }

    public function updateAddress(Request $request, CustomerAddress $address)
    {
        if ($address->customer_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault && !$address->is_default) {
            CustomerAddress::where('customer_id', Auth::id())->update(['is_default' => false]);
        }

        $address->update(array_merge($validated, ['is_default' => $isDefault]));

        return back()->with('success', 'Address updated successfully');
    }

    public function destroyAddress(CustomerAddress $address)
    {
        if ($address->customer_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', 'Address deleted successfully');
    }
}
