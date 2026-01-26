

<?php $__env->startSection('title', 'My Addresses'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">My Addresses</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add New Address -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold text-lg mb-4">Add New Address</h3>
            <form action="<?php echo e(route('addresses.store')); ?>" method="POST" onsubmit="return validateAddressForm()" class="space-y-3">
                <?php echo csrf_field(); ?>
                
                <input type="text" name="label" placeholder="Label (e.g., Home, Office)" 
                    class="w-full px-3 py-2 border rounded" maxlength="255">

                <input type="text" name="recipient_name" placeholder="Recipient Name" 
                    class="w-full px-3 py-2 border rounded" required>

                <input type="text" name="recipient_phone" placeholder="Phone Number" 
                    class="w-full px-3 py-2 border rounded" required>

                <div class="border-t pt-3">
                    <label class="block text-sm font-bold mb-2">Province/City *</label>
                    <select id="province" class="w-full px-3 py-2 border rounded" required>
                        <option value="">Select Province/City</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2">District *</label>
                    <select id="district" class="w-full px-3 py-2 border rounded" required>
                        <option value="">Select District</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2">Ward/Commune *</label>
                    <select id="ward" class="w-full px-3 py-2 border rounded" required>
                        <option value="">Select Ward/Commune</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2">Street/House Number</label>
                    <input type="text" id="street" placeholder="e.g., 123 Nguyen Hue" 
                        class="w-full px-3 py-2 border rounded">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2">Full Address</label>
                    <div id="fullAddress" class="w-full px-3 py-2 bg-gray-50 border rounded text-gray-600">
                        Please select address
                    </div>
                </div>

                <input type="hidden" name="address" id="address">

                <label class="flex items-center">
                    <input type="checkbox" name="is_default" value="1" class="mr-2">
                    <span>Set as default address</span>
                </label>

                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    Add Address
                </button>
            </form>
        </div>

        <!-- Address List -->
        <div class="lg:col-span-2 space-y-4">
            <?php if($addresses->isEmpty()): ?>
            <div class="bg-white p-8 rounded-lg shadow text-center">
                <p class="text-gray-600">No addresses yet. Add one above.</p>
            </div>
            <?php else: ?>
                <?php $__currentLoopData = $addresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white p-6 rounded-lg shadow">
                    <?php if($address->is_default): ?>
                    <div class="mb-3 inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm font-bold">
                        Default Address
                    </div>
                    <?php endif; ?>
                    
                    <h3 class="font-bold text-lg"><?php echo e($address->label ?? 'Address'); ?></h3>
                    <p class="text-gray-700"><?php echo e($address->recipient_name); ?></p>
                    <p class="text-gray-600 text-sm"><?php echo e($address->recipient_phone); ?></p>
                    <p class="text-gray-600 mt-2"><?php echo e($address->address); ?></p>

                    <div class="mt-4 space-x-2">
                        <button type="button" class="text-blue-600 hover:underline edit-address-btn" data-id="<?php echo e($address->id); ?>" data-name="<?php echo e($address->recipient_name); ?>" data-phone="<?php echo e($address->recipient_phone); ?>" data-address="<?php echo e($address->address); ?>" data-label="<?php echo e($address->label ?? ''); ?>" data-default="<?php echo e($address->is_default ? 'true' : 'false'); ?>">
                            Edit
                        </button>
                        <form action="<?php echo e(route('addresses.destroy', $address)); ?>" method="POST" class="inline" 
                            onsubmit="return confirm('Delete this address?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?php echo e(asset('js/vietnam-addresses.js')); ?>"></script>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/addresses/index.blade.php ENDPATH**/ ?>