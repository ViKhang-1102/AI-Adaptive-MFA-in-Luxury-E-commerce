<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class WalletController extends Controller
{
    public function index()
    {
        $adminWallet = auth()->user()->wallet;
        $transactions = $adminWallet->transactions()->latest()->paginate(15);

        return view('admin.wallet.index', compact('adminWallet', 'transactions'));
    }
}
