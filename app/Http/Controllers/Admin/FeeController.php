<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemFee;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $fees = SystemFee::all();
        return view('admin.fees.index', compact('fees'));
    }

    public function edit()
    {
        $fee = SystemFee::firstOrFail();
        return view('admin.fees.edit', compact('fee'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'platform_fee_percent' => 'required|numeric|min:0|max:100',
            'transaction_fee_percent' => 'required|numeric|min:0|max:100',
            'shipping_fee_default' => 'required|numeric|min:0',
        ]);

        $fee = SystemFee::firstOrFail();
        $fee->update($validated);

        return back()->with('success', 'Fees updated');
    }

    public function show()
    {
        return $this->index();
    }
}
