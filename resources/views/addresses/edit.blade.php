@extends('layouts.app')

@section('title', 'Edit Address')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('addresses.index') }}" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-soft text-neutral-500 hover:text-primary transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="text-3xl font-serif font-bold text-primary">Edit Address</h1>
    </div>

    <div class="bg-white p-8 rounded-2xl shadow-soft border border-neutral-100">
        <form action="{{ route('addresses.update', $address) }}" method="POST" onsubmit="return validateAddressForm(event)" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-neutral-700 mb-2">Address Label</label>
                    <input type="text" name="label" value="{{ old('label', $address->label) }}" placeholder="e.g., Home, Office" 
                        class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors" maxlength="255">
                </div>

                <div>
                    <label class="block text-sm font-bold text-neutral-700 mb-2">Recipient Name *</label>
                    <input type="text" name="recipient_name" value="{{ old('recipient_name', $address->recipient_name) }}" placeholder="Recipient Name" 
                        class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-neutral-700 mb-2">Phone Number *</label>
                    <input type="text" name="recipient_phone" value="{{ old('recipient_phone', $address->recipient_phone) }}" placeholder="Phone Number" 
                        class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors" required>
                </div>

                <div class="border-t border-neutral-100 pt-6 md:col-span-2">
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

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-neutral-700 mb-2">Street/House Number</label>
                    <input type="text" id="street" placeholder="e.g., 123 Nguyen Hue" 
                        class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors">
                </div>
            </div>

            <div class="hidden">
                <input type="hidden" name="address" id="address" value="{{ old('address', $address->address) }}">
            </div>

            <div class="pt-4 border-t border-neutral-100">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative flex items-center">
                        <input type="checkbox" name="is_default" value="1" {{ $address->is_default ? 'checked' : '' }} class="w-5 h-5 border-2 border-neutral-300 rounded text-primary focus:ring-primary transition-all peer cursor-pointer">
                    </div>
                    <span class="text-neutral-700 font-medium group-hover:text-primary transition-colors">Set as default delivery address</span>
                </label>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-soft hover:shadow-hover hover:-translate-y-0.5 transition-all duration-300">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/vietnam-addresses.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Try to parse the existing address to pre-fill the dropdowns
    // Format expected: "Street, Ward, District, Province"
    const fullAddress = document.getElementById('address').value;
    if (fullAddress) {
        const parts = fullAddress.split(',').map(p => p.trim());
        
        if (parts.length >= 4) {
            const provinceName = parts[parts.length - 1];
            const districtName = parts[parts.length - 2];
            const wardName = parts[parts.length - 3];
            
            // Reconstruct street from any remaining parts elements
            const streetParts = parts.slice(0, parts.length - 3);
            const streetName = streetParts.join(', ');

            // Set street
            document.getElementById('street').value = streetName;

            // Wait for JS file to fully populate logic
            setTimeout(() => {
                const provinceSelect = document.getElementById('province');
                const districtSelect = document.getElementById('district');
                const wardSelect = document.getElementById('ward');

                // Simulate selecting Province
                for(let i = 0; i < provinceSelect.options.length; i++) {
                    if(provinceSelect.options[i].value === provinceName) {
                        provinceSelect.selectedIndex = i;
                        provinceSelect.dispatchEvent(new Event('change'));
                        break;
                    }
                }

                // Wait for districts to populate, then select
                setTimeout(() => {
                    for(let i = 0; i < districtSelect.options.length; i++) {
                        if(districtSelect.options[i].value === districtName) {
                            districtSelect.selectedIndex = i;
                            districtSelect.dispatchEvent(new Event('change'));
                            break;
                        }
                    }

                    // Wait for wards to populate, then select
                    setTimeout(() => {
                        for(let i = 0; i < wardSelect.options.length; i++) {
                            if(wardSelect.options[i].value === wardName) {
                                wardSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }, 100);
                }, 100);
            }, 500);
        }
    }
});
</script>
@endsection
