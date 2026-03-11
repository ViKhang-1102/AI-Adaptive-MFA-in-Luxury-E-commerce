

<?php $__env->startSection('title', 'Checkout'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8 text-primary font-serif">Checkout</h1>

    <form action="<?php echo e(route('orders.store')); ?>" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <?php echo csrf_field(); ?>

        <!-- Hidden fields for Buy Now -->
        <?php if(request()->has('product_id')): ?>
            <input type="hidden" name="product_id" value="<?php echo e(request('product_id')); ?>">
            <input type="hidden" name="quantity" value="<?php echo e(request('quantity')); ?>">
        <?php endif; ?>

        <!-- Hidden fields for Selected Cart Items -->
        <?php if(request()->has('item_ids')): ?>
            <?php $__currentLoopData = request('item_ids'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <input type="hidden" name="item_ids[]" value="<?php echo e($itemId); ?>">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        <!-- Delivery Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Shipping Address -->
            <div class="bg-white p-6 rounded-md-lg shadow-sm">
                <h2 class="text-xl font-bold mb-4">Delivery Address</h2>

                <?php if($defaultAddress): ?>
                <div class="mb-4 p-4 border-2 border-primary rounded-md bg-neutral-50">
                    <label class="flex items-center">
                        <input type="radio" name="address_id" value="<?php echo e($defaultAddress->id); ?>" checked class="mr-2">
                        <div>
                            <strong><?php echo e($defaultAddress->label ?? 'Default Address'); ?></strong>
                            <p class="text-sm"><?php echo e($defaultAddress->recipient_name); ?> | <?php echo e($defaultAddress->recipient_phone); ?></p>
                            <p class="text-sm"><?php echo e($defaultAddress->address); ?></p>
                        </div>
                    </label>
                </div>
                <?php endif; ?>

                <?php $__currentLoopData = $addresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(!$address->is_default): ?>
                <div class="mb-4 p-4 border rounded-md">
                    <label class="flex items-center">
                        <input type="radio" name="address_id" value="<?php echo e($address->id); ?>" class="mr-2">
                        <div>
                            <strong><?php echo e($address->label); ?></strong>
                            <p class="text-sm"><?php echo e($address->recipient_name); ?> | <?php echo e($address->recipient_phone); ?></p>
                            <p class="text-sm"><?php echo e($address->address); ?></p>
                        </div>
                    </label>
                </div>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <button type="button" class="text-primary hover:underline text-sm mb-4" onclick="toggleAddressForm()">
                    + Add New Address
                </button>

                <div id="newAddressForm" class="hidden space-y-3 p-4 bg-neutral-50 rounded-md border-2 border-dashed">
                    <input type="text" name="label" placeholder="Label (e.g., Home, Office)" 
                        class="w-full px-3 py-2 border rounded-md" maxlength="255">

                    <input type="text" name="recipient_name" placeholder="Recipient Name" 
                        class="w-full px-3 py-2 border rounded-md" required>

                    <input type="text" name="recipient_phone" placeholder="Phone Number" 
                        class="w-full px-3 py-2 border rounded-md" required>

                    <div class="border-t pt-3">
                        <label class="block text-sm font-bold mb-2">Province/City *</label>
                        <select id="checkoutProvince" class="w-full px-3 py-2 border rounded-md" required>
                            <option value="">Select Province/City</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-2">District *</label>
                        <select id="checkoutDistrict" class="w-full px-3 py-2 border rounded-md" required>
                            <option value="">Select District</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-2">Ward/Commune *</label>
                        <select id="checkoutWard" class="w-full px-3 py-2 border rounded-md" required>
                            <option value="">Select Ward/Commune</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-2">Street/House Number</label>
                        <input type="text" id="checkoutStreet" placeholder="e.g., 123 Nguyen Hue" 
                            class="w-full px-3 py-2 border rounded-md">
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-2">Full Address</label>
                        <div id="checkoutFullAddress" class="w-full px-3 py-2 bg-white border rounded-md text-neutral-600">
                            Please select address
                        </div>
                    </div>

                    <input type="hidden" name="delivery_address" id="checkoutAddress">
                    <p class="text-sm text-neutral-500 italic mt-2"><i class="fas fa-info-circle"></i> This address will be saved securely when placing your order.</p>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white p-6 rounded-md-lg shadow-sm">
                <h2 class="text-xl font-bold mb-4">Payment Method</h2>

                <div class="space-y-3">
                    <label class="flex items-center p-4 border rounded-md hover:bg-neutral-50">
                        <input type="radio" name="payment_method" value="cod" checked class="mr-2">
                        <div>
                            <strong>Cash on Delivery (COD)</strong>
                            <p class="text-sm text-neutral-600">Pay when you receive your order</p>
                        </div>
                    </label>

                    <label class="flex items-center p-4 border rounded-md hover:bg-neutral-50">
                        <input type="radio" name="payment_method" value="online" class="mr-2">
                        <div>
                            <strong>Online Payment (PayPal)</strong>
                            <p class="text-sm text-neutral-600">Secure payment via PayPal Sandbox</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white p-6 rounded-md-lg shadow-sm h-fit">
            <h2 class="text-xl font-bold mb-4">Order Summary</h2>

            <div class="space-y-4 mb-6">
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between text-sm border-b pb-2">
                    <span><?php echo e($item->product->name); ?> x <?php echo e($item->quantity); ?></span>
                    <span>$<?php echo e(number_format(($item->product->getDiscountedPrice() * $item->quantity), 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="space-y-3 border-t pt-4">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>$<?php echo e(number_format($subtotal, 2)); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Shipping:</span>
                    <span>$<?php echo e(number_format($shippingFee, 2)); ?></span>
                </div>
                <div class="flex justify-between text-xl font-bold border-t pt-3">
                    <span>Total:</span>
                    <span>$<?php echo e(number_format($total, 2)); ?></span>
                </div>
            </div>

            <button type="submit" onclick="validateCheckoutForm(event)" class="w-full bg-primary text-white shadow-sm-soft hover:shadow-sm-hover hover:-translate-y-0.5 transition-all duration-300 py-3 rounded-md hover:bg-primary-light font-bold mt-6">
                Place Order
            </button>
        </div>
    </form>
</div>

<script src="<?php echo e(asset('js/vietnam-addresses.js')); ?>"></script>
<script>
let checkoutAddressInitialized = false;

function validateCheckoutForm(event) {
    event.preventDefault();
    
    const form = document.querySelector('form[action="<?php echo e(route("orders.store")); ?>"]');
    
    const isNewAddressOpen = !document.getElementById('newAddressForm').classList.contains('hidden');
    let selectedAddressId = null;
    
    if (!isNewAddressOpen) {
        selectedAddressId = form.querySelector('input[name="address_id"]:checked');
        if (!selectedAddressId) {
            alert('Please select or add a delivery address');
            return false;
        }
    } else {
        // Uncheck existing if creating a new form submission
        const addressRadios = document.querySelectorAll('input[name="address_id"]');
        addressRadios.forEach(radio => radio.checked = false);
        
        const province = document.getElementById('checkoutProvince').value;
        const district = document.getElementById('checkoutDistrict').value;
        const ward = document.getElementById('checkoutWard').value;
        const street = document.getElementById('checkoutStreet').value.trim();
        const recipientName = document.querySelector('#newAddressForm input[name="recipient_name"]').value;
        const recipientPhone = document.querySelector('#newAddressForm input[name="recipient_phone"]').value;
        
        if (!province || !district || !ward || !recipientName || !recipientPhone) {
            alert('Please enter all required delivery information (Name, Phone, Province/City, District, Ward/Commune)');
            return false;
        }
        
        const fullAddress = [street, ward, district, province].filter(x => x).join(', ');
        
        let recipientNameInput = form.querySelector('input[name="recipient_name"][type="hidden"]');
        let recipientPhoneInput = form.querySelector('input[name="recipient_phone"][type="hidden"]');
        let deliveryAddressInput = form.querySelector('input[name="delivery_address"]');
        
        if (!recipientNameInput) { recipientNameInput = document.createElement('input'); recipientNameInput.type='hidden'; recipientNameInput.name='recipient_name'; form.appendChild(recipientNameInput); }
        if (!recipientPhoneInput) { recipientPhoneInput = document.createElement('input'); recipientPhoneInput.type='hidden'; recipientPhoneInput.name='recipient_phone'; form.appendChild(recipientPhoneInput); }
        if (!deliveryAddressInput) { deliveryAddressInput = document.createElement('input'); deliveryAddressInput.type='hidden'; deliveryAddressInput.name='delivery_address'; form.appendChild(deliveryAddressInput); }
        
        recipientNameInput.value = recipientName;
        recipientPhoneInput.value = recipientPhone;
        deliveryAddressInput.value = fullAddress;
    }
    
    // Form is valid, submit it
    form.submit();
}

function toggleAddressForm() {
    const form = document.getElementById('newAddressForm');
    form.classList.toggle('hidden');
    
    if(!form.classList.contains('hidden')) {
        const addressRadios = document.querySelectorAll('input[name="address_id"]');
        addressRadios.forEach(radio => radio.checked = false);
    }

    if (!checkoutAddressInitialized) {
        initCheckoutAddress();
        checkoutAddressInitialized = true;
    }
}

function initCheckoutAddress() {
    const provinceSelect = document.getElementById('checkoutProvince');
    const districtSelect = document.getElementById('checkoutDistrict');
    const wardSelect = document.getElementById('checkoutWard');
    const streetInput = document.getElementById('checkoutStreet');
    const addressDisplay = document.getElementById('checkoutFullAddress');

    // Populate provinces
    const provinces = getProvinces();
    provinceSelect.innerHTML = '<option value="">Select Province/City</option>' + 
        provinces.map(p => `<option value="${p}">${p}</option>`).join('');

    // Update districts
    provinceSelect.addEventListener('change', function() {
        const districts = getDistricts(this.value);
        districtSelect.innerHTML = '<option value="">Select District</option>' + 
            districts.map(d => `<option value="${d}">${d}</option>`).join('');
        wardSelect.innerHTML = '<option value="">Select Ward/Commune</option>';
        updateCheckoutAddress();
    });

    // Update wards
    districtSelect.addEventListener('change', function() {
        const province = provinceSelect.value;
        const wards = getWards(province, this.value);
        wardSelect.innerHTML = '<option value="">Select Ward/Commune</option>' + 
            wards.map(w => `<option value="${w}">${w}</option>`).join('');
        updateCheckoutAddress();
    });

    wardSelect.addEventListener('change', updateCheckoutAddress);
    streetInput.addEventListener('input', updateCheckoutAddress);

    function updateCheckoutAddress() {
        const province = provinceSelect.value;
        const district = districtSelect.value;
        const ward = wardSelect.value;
        const street = streetInput.value;

        let fullAddress = [];
        if (street) fullAddress.push(street);
        if (ward) fullAddress.push(ward);
        if (district) fullAddress.push(district);
        if (province) fullAddress.push(province);

        addressDisplay.textContent = fullAddress.length > 0 
            ? fullAddress.join(', ') 
            : 'Please select an address';
    }
}

function saveCheckoutAddress() {
    const province = document.getElementById('checkoutProvince').value;
    const district = document.getElementById('checkoutDistrict').value;
    const ward = document.getElementById('checkoutWard').value;
    const recipientName = document.querySelector('#newAddressForm input[name="recipient_name"]').value;
    const recipientPhone = document.querySelector('#newAddressForm input[name="recipient_phone"]').value;
    const street = document.getElementById('checkoutStreet').value.trim();

    if (!province) {
        alert('Please select a Province/City');
        return;
    }
    if (!district) {
        alert('Please select a District');
        return;
    }
    if (!ward) {
        alert('Please select a Ward/Commune');
        return;
    }
    if (!recipientName) {
        alert('Please enter recipient name');
        return;
    }
    if (!recipientPhone) {
        alert('Please enter phone number');
        return;
    }

    const fullAddress = [street, ward, district, province].filter(x => x).join(', ');
    
    // Update main form with new address values
    const form = document.querySelector('form[action="<?php echo e(route("orders.store")); ?>"]');
    
    // Create or update hidden inputs for new address
    let recipientNameInput = form.querySelector('input[name="recipient_name"][type="hidden"]');
    let recipientPhoneInput = form.querySelector('input[name="recipient_phone"][type="hidden"]');
    let deliveryAddressInput = form.querySelector('input[name="delivery_address"]');
    
    if (!recipientNameInput) {
        recipientNameInput = document.createElement('input');
        recipientNameInput.type = 'hidden';
        recipientNameInput.name = 'recipient_name';
        form.appendChild(recipientNameInput);
    }
    
    if (!recipientPhoneInput) {
        recipientPhoneInput = document.createElement('input');
        recipientPhoneInput.type = 'hidden';
        recipientPhoneInput.name = 'recipient_phone';
        form.appendChild(recipientPhoneInput);
    }
    
    if (!deliveryAddressInput) {
        deliveryAddressInput = document.createElement('input');
        deliveryAddressInput.type = 'hidden';
        deliveryAddressInput.name = 'delivery_address';
        form.appendChild(deliveryAddressInput);
    }
    
    recipientNameInput.value = recipientName;
    recipientPhoneInput.value = recipientPhone;
    deliveryAddressInput.value = fullAddress;

    // Uncheck any existing address radio buttons
    const addressRadios = document.querySelectorAll('input[name="address_id"]');
    addressRadios.forEach(radio => radio.checked = false);

    alert('Address saved! Click "Place Order" to continue.');
    toggleAddressForm();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/checkout/index.blade.php ENDPATH**/ ?>