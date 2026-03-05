@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>

    @if($items->isEmpty())
    <div class="bg-white p-8 rounded-md-lg shadow-sm text-center">
        <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
        <p class="text-neutral-600 text-lg mb-4">Your cart is empty</p>
        <a href="{{ route('products.index') }}" class="bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 px-6 py-2 rounded-md hover:bg-primary-light hover:-translate-y-0.5">
            Continue Shopping
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cart Items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-md-lg shadow-sm overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-neutral-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left w-12">
                                    <input type="checkbox" id="select-all" checked>
                                </th>
                                <th class="px-6 py-3 text-left">Product</th>
                                <th class="px-6 py-3 text-center">Price</th>
                                <th class="px-6 py-3 text-center">Quantity</th>
                                <th class="px-6 py-3 text-center">Total</th>
                                <th class="px-6 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr class="border-b">
                                <td class="px-6 py-4">
                                    <input type="checkbox" name="item_ids[]" value="{{ $item->id }}" data-price="{{ $item->product->getDiscountedPrice() }}" data-quantity="{{ $item->quantity }}" class="item-checkbox" checked>
                                </td>
                                <td class="px-6 py-4 flex items-center">
                                    <a href="{{ route('products.show', $item->product) }}" class="flex items-center text-decoration-none hover:text-primary">
                                        @if($item->product->images->first())
                                        <img src="{{ asset('storage/' . $item->product->images->first()->image) }}" class="w-16 h-16 rounded-md mr-4 object-cover">
                                        @endif
                                        <div>
                                            <strong>{{ $item->product->name }}</strong>
                                            <p class="text-sm text-neutral-600">{{ $item->product->seller->name }}</p>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-center">${{ number_format($item->product->getDiscountedPrice(), 2) }}</td>
                                <td class="px-6 py-4">
                                    <form action="{{ route('cart.update', $item) }}" method="POST" class="flex justify-center">
                                        @csrf
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}" class="w-16 px-2 py-1 border rounded-md">
                                        <button type="submit" class="ml-2 text-primary hover:text-blue-800">Update</button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-center font-bold">${{ number_format(($item->product->getDiscountedPrice() * $item->quantity), 2) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('cart.remove', $item) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Remove</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-md-lg shadow-sm p-6 h-fit">
            <h3 class="font-bold text-lg mb-4">Order Summary</h3>
            <div class="space-y-3 border-b pb-4 mb-4">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span id="summary-subtotal">${{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                <div class="flex justify-between">
                    <span>Tax:</span>
                    <span>-</span>
                </div>
            </div>
            <div class="flex justify-between font-bold text-xl mb-6">
                <span>Total:</span>
                <span id="summary-total">${{ number_format($subtotal, 2) }}</span>
            </div>
            <button id="checkout-btn" type="button" class="block w-full text-center bg-green-600 text-white py-3 rounded-md hover:bg-green-700 font-bold">
                Proceed to Checkout
            </button>
            <a href="{{ route('products.index') }}" class="block w-full text-center border-2 border-neutral-200 text-neutral-700 py-2 mt-3 rounded-md hover:bg-neutral-50">
                Continue Shopping
            </a>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                updateOrderSummary();
            });
        });
    }

    // Update summary when individual items are checked
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateOrderSummary);
    });

    // Update order summary based on selected items
    function updateOrderSummary() {
        const selectedCheckboxes = Array.from(itemCheckboxes).filter(cb => cb.checked);
        let subtotal = 0;
        
        selectedCheckboxes.forEach(cb => {
            const price = parseFloat(cb.getAttribute('data-price'));
            const quantity = parseInt(cb.getAttribute('data-quantity'));
            subtotal += (price * quantity);
        });
        
        const formattedSubtotal = '$' + subtotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        document.getElementById('summary-subtotal').textContent = formattedSubtotal;
        document.getElementById('summary-total').textContent = formattedSubtotal;
        
        if (selectedCheckboxes.length === 0) {
            document.getElementById('checkout-btn').disabled = true;
            document.getElementById('checkout-btn').classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            document.getElementById('checkout-btn').disabled = false;
            document.getElementById('checkout-btn').classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    // Handle checkout button click
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            const selectedCheckboxes = Array.from(itemCheckboxes).filter(cb => cb.checked);
            
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one item to checkout');
                return;
            }
            
            // Create a dynamic form to submit the selected item IDs
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = "{{ route('checkout') }}";
            
            selectedCheckboxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'item_ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        });
    }
});
</script>
@endsection
