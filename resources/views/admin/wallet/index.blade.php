@extends('layouts.app')
@section('title', 'Platform Wallet')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 font-semibold">
            ← Dashboard
        </a>
        <h1 class="text-3xl font-bold">Platform Wallet Management</h1>
    </div>

    <!-- Platform Wallet Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-gray-600 text-sm mb-1">Total Platform Balance</p>
            <h2 class="text-3xl font-bold text-blue-600">${{ number_format($totalBalance, 2) }}</h2>
            <p class="text-gray-500 text-xs mt-2">Current funds in platform</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-gray-600 text-sm mb-1">Total Seller Wallets</p>
            <h2 class="text-3xl font-bold text-green-600">${{ number_format($totalSellerWallets, 2) }}</h2>
            <p class="text-gray-500 text-xs mt-2">Owed to sellers</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-gray-600 text-sm mb-1">Total Transactions</p>
            <h2 class="text-3xl font-bold text-purple-600">{{ $totalTransactions }}</h2>
            <p class="text-gray-500 text-xs mt-2">All time transactions</p>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="font-bold text-lg">Recent Transactions</h3>
        </div>

        @if($transactions->isEmpty())
            <div class="p-6 text-center text-gray-500">
                No transactions found.
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">User/Seller</th>
                        <th class="px-6 py-3 text-left">Type</th>
                        <th class="px-6 py-3 text-right">Amount</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-3">{{ $transaction->user->name }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded {{ $transaction->type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right font-semibold">
                            <span class="{{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'credit' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-full {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $transaction->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
