<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::customers()->paginate(15);
        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $user)
    {
        if (!$user->isCustomer()) {
            abort(404);
        }

        return view('admin.customers.show', compact('user'));
    }

    public function edit(User $user)
    {
        if (!$user->isCustomer()) {
            abort(404);
        }

        return view('admin.customers.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (!$user->isCustomer()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        return redirect()->route('admin.customers.show', $user)->with('success', 'Customer updated');
    }

    public function destroy(User $user)
    {
        if (!$user->isCustomer()) {
            abort(404);
        }

        $user->update(['is_active' => false]);

        return back()->with('success', 'Customer deactivated');
    }
}
