<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = auth()->user()->wallet;
        $transactions = $wallet->transactions()->latest()->paginate(15);

        return view('seller.wallet.index', compact('wallet', 'transactions'));
    }
}
