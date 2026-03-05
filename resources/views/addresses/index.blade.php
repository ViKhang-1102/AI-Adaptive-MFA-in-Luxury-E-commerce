@extends('layouts.app')

@section('title', 'My Addresses')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">My Addresses</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add New Address -->
        <div class="bg-white p-6 rounded-2xl shadow-soft border border-neutral-100 h-fit">
            <h3 class="font-serif font-bold text-xl text-primary mb-6">Add New Address</h3>
            <form action="{{ route('addresses.store') }}" method="POST" onsubmit="return validateAddressForm(event)" class="space-y-4">
                @csrf
                
                <div>
                    <input type="text" name="label" placeholder="Label (e.g., Home, Office)" 
                        class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors" maxlength="255">
                </div>

                <div>
                    <input type="text" name="recipient_name" placeholder="Recipient Name" 
                        class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors" required>
                </div>

                <div>
                    <input type="text" name="recipient_phone" placeholder="Phone Number" 
                        class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors" required>
                </div>

                <div class="border-t border-neutral-100 pt-4">
                    <label class="block text-sm font-bold text-neutral-700 mb-2">Province/City *</label>
                    <select id="province" class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors" required>
                        <option value="">Select Province/City</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-neutral-700 mb-2">District *</label>
                    <select id="district" class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors" required>
                        <option value="">Select District</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-neutral-700 mb-2">Ward/Commune *</label>
                    <select id="ward" class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors" required>
                        <option value="">Select Ward/Commune</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-neutral-700 mb-2">Street/House Number</label>
                    <input type="text" id="street" placeholder="e.g., 123 Nguyen Hue" 
                        class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                </div>

                <div class="hidden">
                    <label class="block text-sm font-bold text-neutral-700 mb-2">Full Address</label>
                    <div id="fullAddress" class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-xl text-neutral-600">
                        Please select address
                    </div>
                </div>

                <input type="hidden" name="address" id="address">

                <label class="flex items-center">
                    <input type="checkbox" name="is_default" value="1" class="mr-2">
                    <span>Set as default address</span>
                </label>

                <button type="submit" class="w-full bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 py-2 rounded-md hover:bg-primary-light hover:-translate-y-0.5">
                    Add Address
                </button>
            </form>
        </div>

        <!-- Address List -->
        <div class="lg:col-span-2 space-y-6">
            @if($addresses->isEmpty())
            <div class="bg-white p-8 rounded-2xl shadow-soft border border-neutral-100 text-center">
                <div class="w-16 h-16 bg-neutral-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="map-pin" class="w-8 h-8 text-neutral-400"></i>
                </div>
                <p class="text-neutral-500 font-medium">No addresses yet. Add one to the left to speed up checkout.</p>
            </div>
            @else
                @foreach($addresses as $address)
                <div class="bg-white p-6 rounded-2xl shadow-soft border border-neutral-100 hover:shadow-md transition-shadow relative overflow-hidden">
                    @if($address->is_default)
                    <div class="absolute top-0 right-0 bg-primary text-white text-xs font-bold px-4 py-1 rounded-bl-xl shadow-sm">
                        Default
                    </div>
                    @endif
                    
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="{{ $address->label === 'Home' ? 'home' : ($address->label === 'Office' ? 'briefcase' : 'map-pin') }}" class="w-6 h-6 text-primary"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-serif font-bold text-lg text-primary mb-1">{{ $address->label ?? 'Address' }}</h3>
                            <div class="space-y-1 text-sm text-neutral-600 mb-4">
                                <p><strong class="text-neutral-800">{{ $address->recipient_name }}</strong> &bull; {{ $address->recipient_phone }}</p>
                                <p class="leading-relaxed">{{ $address->address }}</p>
                            </div>

                            <div class="flex items-center gap-3 pt-4 border-t border-neutral-100">
                                <button type="button" class="text-sm font-bold text-neutral-600 hover:text-primary transition-colors flex items-center gap-1 edit-address-btn" data-id="{{ $address->id }}">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i> Edit
                                </button>
                                <div class="w-px h-4 bg-neutral-200"></div>
                                <form action="{{ route('addresses.destroy', $address) }}" method="POST" class="inline" 
                                    onsubmit="return confirm('Delete this address? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-bold text-red-500 hover:text-red-700 transition-colors flex items-center gap-1">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<script src="{{ asset('js/vietnam-addresses.js') }}"></script>
<script>
// Handle edit address button clicks
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-address-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Redirect to edit page instead of complex inline editing
            const id = this.dataset.id;
            window.location.href = '/addresses/' + id + '/edit';
        });
    });
});
</script>
@endsection
