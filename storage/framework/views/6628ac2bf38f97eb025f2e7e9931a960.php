

<?php $__env->startSection('title', 'My Profile'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-8">
        <a href="<?php echo e(route('home')); ?>" class="px-4 py-2 bg-neutral-500 text-white rounded-md hover:bg-gray-600 font-semibold">
            ← Home
        </a>
        <h1 class="text-3xl font-bold">My Profile</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar -->
        <div class="bg-white p-6 rounded-md-lg shadow-sm h-fit">
            <h3 class="font-bold mb-4">Profile Menu</h3>
            <div class="space-y-2">
                <button onclick="showSection('info')" class="w-full text-left px-4 py-2 rounded-md bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5">
                    Personal Information
                </button>
                <button onclick="showSection('password')" class="w-full text-left px-4 py-2 rounded-md hover:bg-neutral-100">
                    Change Password
                </button>
                <?php if(auth()->user()->isCustomer()): ?>
                <a href="<?php echo e(route('addresses.index')); ?>" class="block px-4 py-2 rounded-md hover:bg-neutral-100">
                    My Addresses
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div id="info-section" class="bg-white p-6 rounded-md-lg shadow-sm">
                <h2 class="text-xl font-bold mb-4">Personal Information</h2>

                <form action="<?php echo e(route('profile.update')); ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label class="block font-bold mb-2">Avatar</label>
                        <?php if(auth()->user()->avatar): ?>
                        <img src="<?php echo e(asset('storage/' . auth()->user()->avatar)); ?>" alt="Avatar" class="w-24 h-24 rounded-md mb-2 object-cover" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22%3E%3Ccircle cx=%2212%22 cy=%2212%22 r=%2212%22 fill=%22%23ddd%22/%3E%3C/svg%3E'">
                        <?php else: ?>
                        <div class="w-24 h-24 rounded-md mb-2 bg-gray-300 flex items-center justify-center">
                            <i class="fas fa-user text-2xl text-neutral-600"></i>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="avatar" accept="image/*" class="block mt-2 px-3 py-2 border rounded-md">
                    </div>

                    <div>
                        <label class="block font-bold mb-2">Full Name</label>
                        <input type="text" name="name" value="<?php echo e(auth()->user()->name); ?>" 
                            class="w-full px-4 py-2 border rounded-md" required>
                    </div>

                    <div>
                        <label class="block font-bold mb-2">Email</label>
                        <input type="email" value="<?php echo e(auth()->user()->email); ?>" 
                            class="w-full px-4 py-2 border rounded-md bg-neutral-100" disabled>
                    </div>

                    <div>
                        <label class="block font-bold mb-2">Phone</label>
                        <input type="text" name="phone" value="<?php echo e(auth()->user()->phone); ?>" 
                            class="w-full px-4 py-2 border rounded-md">
                    </div>

                    <?php if(auth()->user()->isSeller()): ?>
                    <div>
                        <label class="block font-bold mb-2">PayPal Email (for payouts)</label>
                        <input type="email" name="paypal_email" value="<?php echo e(auth()->user()->paypal_email); ?>"
                            class="w-full px-4 py-2 border rounded-md" placeholder="seller@example.com">
                        <p class="text-xs text-neutral-500 mt-1">This email is used by admin to send payouts via PayPal.</p>
                    </div>
                    <?php endif; ?>

                    <div>
                        <label class="block font-bold mb-2">Address</label>
                        <textarea name="address" rows="3" 
                            class="w-full px-4 py-2 border rounded-md"><?php echo e(auth()->user()->address); ?></textarea>
                    </div>

                    <div>
                        <label class="block font-bold mb-2">Bio</label>
                        <textarea name="bio" rows="3" maxlength="500"
                            class="w-full px-4 py-2 border rounded-md"><?php echo e(auth()->user()->bio); ?></textarea>
                    </div>

                    <button type="submit" class="bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 px-6 py-2 rounded-md hover:bg-primary-light hover:-translate-y-0.5">
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div id="password-section" class="bg-white p-6 rounded-md-lg shadow-sm hidden">
                <h2 class="text-xl font-bold mb-4">Change Password</h2>

                <form action="<?php echo e(route('profile.password')); ?>" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label class="block font-bold mb-2">Current Password</label>
                        <input type="password" name="current_password" 
                            class="w-full px-4 py-2 border rounded-md" required>
                    </div>

                    <div>
                        <label class="block font-bold mb-2">New Password</label>
                        <input type="password" name="password" 
                            class="w-full px-4 py-2 border rounded-md" required>
                    </div>

                    <div>
                        <label class="block font-bold mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" 
                            class="w-full px-4 py-2 border rounded-md" required>
                    </div>

                    <button type="submit" class="bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 px-6 py-2 rounded-md hover:bg-primary-light hover:-translate-y-0.5">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showSection(section) {
    document.getElementById('info-section').classList.toggle('hidden', section !== 'info');
    document.getElementById('password-section').classList.toggle('hidden', section !== 'password');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/profile/show.blade.php ENDPATH**/ ?>