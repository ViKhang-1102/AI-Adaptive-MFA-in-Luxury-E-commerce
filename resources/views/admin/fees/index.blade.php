@extends('layouts.app')
@section('title', 'System Fees & Settings')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-neutral-500 text-white rounded-md hover:bg-gray-600 font-semibold">
            ← Dashboard
        </a>
        <h1 class="text-3xl font-bold">System Fees & Settings</h1>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    <!-- Platform Commission Configuration -->
    <div class="bg-blue-50 border border-blue-200 rounded-md-lg p-6 mb-8">
        <h2 class="text-lg font-semibold text-blue-900 mb-4">Platform Commission Configuration</h2>
        <form method="POST" action="{{ route('admin.fees.commission.update') }}" class="space-y-4">
            @csrf
            <div>
                <label for="platform_commission" class="block text-sm font-medium text-neutral-700 mb-2">
                    Admin Commission Percentage (%)
                </label>
                <div class="flex gap-4 items-end">
                    <div class="flex-1">
                        <input type="number" id="platform_commission" name="platform_commission" 
                               value="{{ $platformFee?->fee_value ?? 10 }}"
                               min="0" max="100" step="0.01"
                               class="w-full px-3 py-2 border border-neutral-200 rounded-md shadow-sm-sm focus:outline-none focus:blue-500"
                               required>
                        <p class="text-xs text-neutral-500 mt-1">Enter the percentage (0-100) that Admin receives from each order</p>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 rounded-md hover:bg-primary-light hover:-translate-y-0.5 font-semibold">
                        Update Commission
                    </button>
                </div>
            </div>
            <div class="bg-blue-100 p-3 rounded-md text-sm text-blue-800">
                <strong>Current Split:</strong>
                <ul class="list-none mt-2">
                    <li>✓ Admin receives: <strong>{{ $platformFee?->fee_value ?? 10 }}%</strong></li>
                    <li>✓ Seller receives: <strong>{{ 100 - ($platformFee?->fee_value ?? 10) }}%</strong></li>
                </ul>
            </div>
        </form>
    </div>

    @if($fees->isEmpty())
        <div class="bg-white p-6 rounded-md-lg shadow-sm text-center text-neutral-500">
            <p>No additional fees configured yet.</p>
            <a href="{{ route('admin.fees.create') }}" class="mt-4 inline-block px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">+ Add Fee</a>
        </div>
    @else
        <div class="bg-white p-6 rounded-md-lg shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Other Fees</h2>
                <a href="{{ route('admin.fees.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">+ Add Fee</a>
            </div>

            <div class="space-y-4">
                @foreach($fees as $fee)
                <div class="border rounded-md-lg p-4 hover:bg-neutral-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-lg">{{ $fee->name }}</h3>
                            <p class="text-neutral-600">{{ $fee->description }}</p>
                            <div class="mt-2 text-sm">
                                <span class="font-bold text-xl text-primary">
                                    @if($fee->fee_type === 'percentage')
                                        {{ $fee->fee_value }}%
                                    @else
                                        ${{ number_format($fee->fee_value, 2) }}
                                    @endif
                                </span>
                                <span class="text-neutral-500 ml-2">({{ ucfirst($fee->fee_type) }})</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.fees.edit', $fee) }}" class="px-3 py-1 text-primary hover:bg-blue-50 rounded-md">Edit</a>
                            <form method="POST" action="{{ route('admin.fees.destroy', $fee) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1 text-red-600 hover:bg-red-50 rounded-md">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $fees->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
