

<?php $__env->startSection('title', $product->name); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Product Images Section -->
        <div class="md:col-span-1">
            <?php if($product->images->count() > 0): ?>
                <!-- Main Image -->
                <div class="bg-gray-100 rounded-lg overflow-hidden mb-4">
                    <img id="main-image" src="<?php echo e(asset('storage/' . $product->images->first()->image)); ?>" class="w-full h-auto object-cover rounded-lg" alt="<?php echo e($product->name); ?>">
                </div>
                
                <!-- Thumbnails Grid -->
                <?php if($product->images->count() > 1): ?>
                <div class="grid grid-cols-4 gap-2">
                    <?php $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button class="image-thumbnail relative overflow-hidden rounded-lg border-2 aspect-square <?php echo e($key === 0 ? 'border-blue-600' : 'border-gray-300'); ?> hover:border-blue-600 transition" data-index="<?php echo e($key); ?>" data-src="<?php echo e(asset('storage/' . $image->image)); ?>">
                        <img src="<?php echo e(asset('storage/' . $image->image)); ?>" class="w-full h-full object-cover" alt="Thumbnail <?php echo e($key + 1); ?>">
                    </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="bg-gray-200 rounded-lg flex items-center justify-center aspect-square">
                    <div class="text-center text-gray-500">
                        <i class="fas fa-image text-4xl mb-2"></i>
                        <p>No images available</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Details -->
        <div class="md:col-span-2">
            <h1 class="text-3xl font-bold mb-2"><?php echo e($product->name); ?></h1>
            
            <div class="mb-4">
                <span class="text-gray-600">By <strong><?php echo e($product->seller->name); ?></strong></span>
            </div>

            <!-- Rating -->
            <div class="mb-4 flex items-center">
                <div class="text-yellow-400">
                    <?php
                    $rating = $product->getAverageRating();
                    for($i = 1; $i <= 5; $i++):
                        if($i <= $rating): echo '<i class="fas fa-star"></i>';
                        else: echo '<i class="far fa-star"></i>';
                        endif;
                    endfor;
                    ?>
                </div>
                <span class="ml-2 text-gray-600">(<?php echo e($product->getReviewCount()); ?> reviews)</span>
            </div>

            <!-- Price -->
            <div class="mb-6 text-2xl font-bold">
                <?php if($product->hasDiscount()): ?>
                <span class="text-red-600">$<?php echo e(number_format($product->getDiscountedPrice(), 2)); ?></span>
                <span class="text-gray-400 line-through text-lg">$<?php echo e(number_format($product->price, 2)); ?></span>
                <span class="text-red-600 bg-red-100 px-2 py-1 rounded text-sm ml-2">Save <?php echo e($product->discount_percent); ?>%</span>
                <?php else: ?>
                <span>$<?php echo e(number_format($product->price, 2)); ?></span>
                <?php endif; ?>
            </div>

            <!-- Stock -->
            <div class="mb-6">
                <?php if($product->stock > 0): ?>
                <span class="text-green-600 font-bold">In Stock (<?php echo e($product->stock); ?> available)</span>
                <?php else: ?>
                <span class="text-red-600 font-bold">Out of Stock</span>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <?php if(auth()->guard()->check()): ?>
            <?php if(auth()->user()->isCustomer()): ?>
            <div class="space-y-3 mb-6">
                <div class="flex gap-3 items-center mb-3">
                    <label for="quantity" class="font-semibold">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo e($product->stock); ?>" class="w-20 px-3 py-2 border rounded">
                </div>

                <form action="<?php echo e(route('cart.add')); ?>" method="POST" class="flex space-x-2">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
                    <input type="hidden" name="quantity" id="cartQuantity" value="1">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </form>

                <form action="<?php echo e(route('checkout')); ?>" method="GET">
                    <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
                    <input type="hidden" name="quantity" id="buyNowQuantity" value="1">
                    <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
                        Buy Now
                    </button>
                </form>

                <?php if(auth()->user()->wishlist->where('product_id', $product->id)->first()): ?>
                <form action="<?php echo e(route('wishlist.remove', $product->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
                        <i class="fas fa-heart"></i> Remove from Wishlist
                    </button>
                </form>
                <?php else: ?>
                <form action="<?php echo e(route('wishlist.add', $product->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="w-full bg-gray-600 text-white py-2 rounded hover:bg-gray-700">
                        <i class="far fa-heart"></i> Add to Wishlist
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Description -->
            <div class="border-t pt-6">
                <h3 class="font-bold text-lg mb-2">Description</h3>
                <p class="text-gray-700"><?php echo e($product->description); ?></p>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if($relatedProducts->count() > 0): ?>
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Related Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php $__currentLoopData = $relatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                <?php if($related->images->first()): ?>
                <img src="<?php echo e(asset('storage/' . $related->images->first()->image)); ?>" class="w-full h-48 object-cover" alt="<?php echo e($related->name); ?>">
                <?php else: ?>
                <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
                <?php endif; ?>
                <div class="p-4">
                    <h3 class="font-bold truncate"><?php echo e($related->name); ?></h3>
                    <p class="text-gray-600 text-sm mb-2"><?php echo e($related->seller->name); ?></p>
                    <div class="flex justify-between items-center">
                        <span class="font-bold">$<?php echo e(number_format($related->getDiscountedPrice(), 2)); ?></span>
                        <a href="<?php echo e(route('products.show', $related)); ?>" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Reviews Section -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>
        <div class="bg-white p-6 rounded-lg shadow">
            <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="border-b pb-4 mb-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <strong><?php echo e($review->customer->name); ?></strong>
                        <div class="text-yellow-400 text-sm">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php if($i <= $review->rating): ?>
                                    <i class="fas fa-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <span class="text-gray-600 text-sm"><?php echo e($review->created_at->format('M d, Y')); ?></span>
                </div>
                <p class="text-gray-700"><?php echo e($review->comment); ?></p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php echo e($reviews->links()); ?>

        </div>
    </div>
</div>

<script>
    const thumbnails = document.querySelectorAll('.image-thumbnail');
    const mainImage = document.getElementById('main-image');
    const quantityInput = document.getElementById('quantity');
    const cartQuantityInput = document.getElementById('cartQuantity');
    const buyNowQuantityInput = document.getElementById('buyNowQuantity');
    
    if (quantityInput) {
        quantityInput.addEventListener('change', function() {
            if (cartQuantityInput) cartQuantityInput.value = this.value;
            if (buyNowQuantityInput) buyNowQuantityInput.value = this.value;
        });
    }
    
    if (thumbnails.length > 0 && mainImage) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function(e) {
                e.preventDefault();
                const newSrc = this.dataset.src;
                mainImage.src = newSrc;
                
                // Update border styling
                thumbnails.forEach(t => t.classList.remove('border-blue-600'));
                thumbnails.forEach(t => t.classList.add('border-gray-300'));
                this.classList.remove('border-gray-300');
                this.classList.add('border-blue-600');
            });
        });
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/products/show.blade.php ENDPATH**/ ?>