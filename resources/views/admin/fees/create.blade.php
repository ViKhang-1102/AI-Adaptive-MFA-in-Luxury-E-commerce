@extends('layouts.app')
@section('title', 'Add New Fee')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.fees.index') }}" class="px-4 py-2 bg-neutral-500 text-white rounded-md hover:bg-gray-600 font-semibold">
            ← Back to Fees
        </a>
        <h1 class="text-3xl font-bold">Add New Fee</h1>
    </div>

    <div class="bg-white p-6 rounded-md-lg shadow-sm">
        <form method="POST" action="{{ route('admin.fees.store') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block text-neutral-700 font-semibold mb-2">Fee Name *</label>
                <input 
                    type="text" 
                    name="name" 
                    value="{{ old('name') }}"
                    placeholder="e.g., Platform Commission, Transaction Fee"
                    class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-gold @error('name') border-red-500 @enderror"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-neutral-700 font-semibold mb-2">Description</label>
                <textarea 
                    name="description" 
                    rows="3"
                    placeholder="Optional description for this fee"
                    class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-gold"
                >{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-neutral-700 font-semibold mb-2">Fee Type *</label>
                    <select 
                        name="fee_type" 
                        class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-gold @error('fee_type') border-red-500 @enderror"
                        required
                    >
                        <option value="">-- Select Type --</option>
                        <option value="percentage" @selected(old('fee_type') === 'percentage')>Percentage (%)</option>
                        <option value="fixed" @selected(old('fee_type') === 'fixed')>Fixed Amount ($)</option>
                    </select>
                    @error('fee_type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-neutral-700 font-semibold mb-2">Fee Value *</label>
                    <input 
                        type="number" 
                        name="fee_value" 
                        step="0.01"
                        value="{{ old('fee_value') }}"
                        placeholder="0.00"
                        class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-gold @error('fee_value') border-red-500 @enderror"
                        required
                    >
                    @error('fee_value')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-md-lg p-4">
                <p class="text-sm text-blue-800">
                    💡 <strong>Tips:</strong> Use percentage for commission fees (e.g., 10% for platform) or fixed amount for flat fees.
                </p>
            </div>

            <div class="flex justify-between pt-4 border-t">
                <a href="{{ route('admin.fees.index') }}" class="px-6 py-2 bg-gray-300 text-primary rounded-md hover:bg-gray-400 font-semibold">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
                    Create Fee
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
