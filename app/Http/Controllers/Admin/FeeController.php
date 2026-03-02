<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemFee;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class FeeController extends Controller
{
    public function index()
    {
        $fees = SystemFee::paginate(10);
        $platformFee = SystemFee::where('is_platform_commission', true)->first();
        return view('admin.fees.index', compact('fees', 'platformFee'));
    }

    public function updatePlatformCommission(Request $request)
    {
        $validated = $request->validate([
            'platform_commission' => 'required|numeric|min:0|max:100',
        ]);

        $fee = SystemFee::firstOrCreate(
            ['is_platform_commission' => true],
            [
                'name' => 'Platform Commission',
                'fee_type' => 'percentage',
                'fee_value' => 0,
                'is_platform_commission' => true,
            ]
        );

        $fee->update([
            'fee_value' => $validated['platform_commission'],
            'description' => "Admin receives {$validated['platform_commission']}%, Seller receives " . (100 - $validated['platform_commission']) . '%',
        ]);

        return redirect()->route('admin.fees.index')
            ->with('success', "Platform commission updated to {$validated['platform_commission']}%");
    }

    public function create()
    {
        return view('admin.fees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fee_type' => 'required|in:percentage,fixed',
            'fee_value' => 'required|numeric|min:0',
        ]);

        SystemFee::create($validated);

        return redirect()->route('admin.fees.index')->with('success', 'Fee created successfully');
    }

    public function edit(SystemFee $fee)
    {
        return view('admin.fees.edit', compact('fee'));
    }

    public function update(Request $request, SystemFee $fee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fee_type' => 'required|in:percentage,fixed',
            'fee_value' => 'required|numeric|min:0',
        ]);

        $fee->update($validated);

        return redirect()->route('admin.fees.index')->with('success', 'Fee updated successfully');
    }

    public function destroy(SystemFee $fee)
    {
        $fee->delete();
        return redirect()->route('admin.fees.index')->with('success', 'Fee deleted successfully');
    }

    public function show(SystemFee $fee = null)
    {
        if (!$fee) {
            return $this->index();
        }
        return view('admin.fees.show', compact('fee'));
    }
}
