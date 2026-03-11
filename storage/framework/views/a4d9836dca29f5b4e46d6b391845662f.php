<footer class="bg-primary text-neutral-300 mt-20 border-t border-primary-light">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
            <!-- Brand -->
            <div class="col-span-1 md:col-span-2 lg:col-span-1">
                <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-2 mb-6 group inline-flex">
                    <i data-lucide="gem" class="w-8 h-8 text-gold group-hover:scale-110 transition-transform duration-300"></i>
                    <span class="text-2xl font-serif font-bold text-white tracking-tight">LuxGuard</span>
                </a>
                <p class="text-sm leading-relaxed text-neutral-400 mb-6">
                    Curating the world's finest luxury authentic pieces. Elevate your lifestyle with our exclusive collections and unparalleled service.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="w-10 h-10 rounded-md-full bg-primary-light flex items-center justify-center text-neutral-400 hover:text-gold hover:bg-white/5 transition-all">
                        <i data-lucide="facebook" class="w-5 h-5"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-md-full bg-primary-light flex items-center justify-center text-neutral-400 hover:text-gold hover:bg-white/5 transition-all">
                        <i data-lucide="twitter" class="w-5 h-5"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-md-full bg-primary-light flex items-center justify-center text-neutral-400 hover:text-gold hover:bg-white/5 transition-all">
                        <i data-lucide="instagram" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>

            <!-- Client Services -->
            <div>
                <h3 class="text-white font-serif font-semibold mb-6 text-lg tracking-wide uppercase">Client Services</h3>
                <ul class="space-y-4 text-sm">
                    <li><a href="#" class="hover:text-gold transition-colors inline-flex items-center gap-2 group"><i data-lucide="chevron-right" class="w-3 h-3 group-hover:text-gold text-neutral-600 transition-colors"></i> Contact Us</a></li>
                    <li><a href="#" class="hover:text-gold transition-colors inline-flex items-center gap-2 group"><i data-lucide="chevron-right" class="w-3 h-3 group-hover:text-gold text-neutral-600 transition-colors"></i> Shipping & Returns</a></li>
                    <li><a href="#" class="hover:text-gold transition-colors inline-flex items-center gap-2 group"><i data-lucide="chevron-right" class="w-3 h-3 group-hover:text-gold text-neutral-600 transition-colors"></i> Track Order</a></li>
                    <li><a href="#" class="hover:text-gold transition-colors inline-flex items-center gap-2 group"><i data-lucide="chevron-right" class="w-3 h-3 group-hover:text-gold text-neutral-600 transition-colors"></i> FAQ</a></li>
                </ul>
            </div>

            <!-- The Maison -->
            <div>
                <h3 class="text-white font-serif font-semibold mb-6 text-lg tracking-wide uppercase">The Maison</h3>
                <ul class="space-y-4 text-sm">
                    <li><a href="#" class="hover:text-gold transition-colors inline-flex items-center gap-2 group"><i data-lucide="chevron-right" class="w-3 h-3 group-hover:text-gold text-neutral-600 transition-colors"></i> About LuxGuard</a></li>
                    <li><a href="#" class="hover:text-gold transition-colors inline-flex items-center gap-2 group"><i data-lucide="chevron-right" class="w-3 h-3 group-hover:text-gold text-neutral-600 transition-colors"></i> Sustainability</a></li>
                    <li><a href="#" class="hover:text-gold transition-colors inline-flex items-center gap-2 group"><i data-lucide="chevron-right" class="w-3 h-3 group-hover:text-gold text-neutral-600 transition-colors"></i> Boutiques</a></li>
                    <li><a href="#" class="hover:text-gold transition-colors inline-flex items-center gap-2 group"><i data-lucide="chevron-right" class="w-3 h-3 group-hover:text-gold text-neutral-600 transition-colors"></i> Careers</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h3 class="text-white font-serif font-semibold mb-6 text-lg tracking-wide uppercase">Exclusive Updates</h3>
                <p class="text-sm text-neutral-400 mb-4">Subscribe to receive early access to new collections and personalized offers.</p>
                <form class="relative group">
                    <input type="email" placeholder="Email Address" class="w-full bg-primary-light border border-transparent pl-4 pr-12 py-3 rounded-md-md focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold text-white text-sm transition-all focus:bg-primary-light/80 placeholder-neutral-500">
                    <button type="button" class="absolute right-0 top-0 bottom-0 px-4 text-neutral-400 hover:text-gold transition-colors">
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Bottom -->
        <div class="border-t border-primary-light pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-neutral-500">
                &copy; <?php echo e(date('Y')); ?> LuxGuard E-Commerce. All rights reserved.
            </p>
            <div class="flex gap-6 text-sm text-neutral-500">
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                <a href="#" class="hover:text-white transition-colors">Legal Area</a>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH C:\laragon\www\E-commerce2026 - Copy (6)\resources\views/layouts/footer.blade.php ENDPATH**/ ?>