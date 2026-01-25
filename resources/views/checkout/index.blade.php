@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Checkout</h1>

    <form action="{{ route('orders.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf

        <!-- Delivery Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Shipping Address -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Delivery Address</h2>

                @if($defaultAddress)
                <div class="mb-4 p-4 border rounded bg-blue-50">
                    <label class="flex items-center">
                        <input type="radio" name="address_id" value="{{ $defaultAddress->id }}" checked class="mr-2">
                        <div>
                            <strong>{{ $defaultAddress->label ?? 'Default Address' }}</strong>
                            <p class="text-sm">{{ $defaultAddress->recipient_name }} | {{ $defaultAddress->recipient_phone }}</p>
                            <p class="text-sm">{{ $defaultAddress->address }}</p>
                        </div>
                    </label>
                </div>
                @endif

                @foreach($addresses as $address)
                @if(!$address->is_default)
                <div class="mb-4 p-4 border rounded">
                    <label class="flex items-center">
                        <input type="radio" name="address_id" value="{{ $address->id }}" class="mr-2">
                        <div>
                            <strong>{{ $address->label }}</strong>
                            <p class="text-sm">{{ $address->recipient_name }} | {{ $address->recipient_phone }}</p>
                            <p class="text-sm">{{ $address->address }}</p>
                        </div>
                    </label>
                </div>
                @endif
                @endforeach

                <button type="button" class="text-blue-600 hover:underline text-sm mb-4" onclick="toggleAddressForm()">
                    + Add New Address
                </button>

                <div id="newAddressForm" class="hidden space-y-3 p-4 bg-gray-50 rounded border-2 border-dashed">
                    <input type="text" name="recipient_name" placeholder="Recipient Name" 
                        class="w-full px-3 py-2 border rounded" required>
                    <input type="text" name="recipient_phone" placeholder="Phone Number" 
                        class="w-full px-3 py-2 border rounded" required>
                    <textarea name="delivery_address" placeholder="Address" rows="3"
                        class="w-full px-3 py-2 border rounded" required></textarea>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Payment Method</h2>

                <div class="space-y-3">
                    <label class="flex items-center p-4 border rounded hover:bg-gray-50">
                        <input type="radio" name="payment_method" value="cod" checked class="mr-2">
                        <div>
                            <strong>Cash on Delivery (COD)</strong>
                            <p class="text-sm text-gray-600">Pay when you receive your order</p>
                        </div>
                    </label>

                    <label class="flex items-center p-4 border rounded hover:bg-gray-50">
                        <input type="radio" name="payment_method" value="online" class="mr-2">
                        <div>
                            <strong>Online Payment (VNPay)</strong>
                            <p class="text-sm text-gray-600">Pay now with card or mobile wallet</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white p-6 rounded-lg shadow h-fit">
            <h2 class="text-xl font-bold mb-4">Order Summary</h2>

            <div class="space-y-4 mb-6">
                @foreach($items as $item)
                <div class="flex justify-between text-sm border-b pb-2">
                    <span>{{ $item->product->name }} x {{ $item->quantity }}</span>
                    <span>${{ number_format($item->product->getDiscountedPrice() * $item->quantity, 2) }}</span>
                </div>
                @endforeach
            </div>

            <div class="space-y-3 border-t pt-4">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>${{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Shipping:</span>
                    <span>${{ number_format($shippingFee, 2) }}</span>
                </div>
                <div class="flex justify-between text-xl font-bold border-t pt-3">
                    <span>Total:</span>
                    <span>${{ number_format($total, 2) }}</span>
                </div>
            </div>

            <button type="submit" class="w-full bg-green-600 text-white py-3 rounded hover:bg-green-700 font-bold mt-6">
                Place Order
            </button>
        </div>
    </form>
</div>

<script>
function toggleAddressForm() {
    const form = document.getElementById('newAddressForm');
    form.classList.toggle('hidden');
}
</script>
@endsection
