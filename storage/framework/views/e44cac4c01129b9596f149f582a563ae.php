<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'E-Commerce Platform'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: '#0A192F', // Deep Navy
                            light: '#112240',
                            dark: '#020C1B',
                        },
                        gold: {
                            DEFAULT: '#D4AF37',
                            light: '#F3E5AB',
                            dark: '#AA8C2C',
                        },
                        neutral: {
                            50: '#F8F9FA',
                            100: '#F1F3F5',
                            800: '#343A40',
                            900: '#212529',
                        }
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                        'hover': '0 10px 40px -5px rgba(0, 0, 0, 0.08)',
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Font Awesome (Legacy - to be removed gradually) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Reset browser default spacing */
        html, body {
            margin: 0;
            padding: 0;
        }

        /* Fixed Header Styles */
        header {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 9999 !important;
            background-color: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(12px) !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        }

        /* Default padding for all pages to account for the fixed header */
        body {
            padding-top: 64px !important;
        }

        /* Admin Order pages need more space due to sub-headers/filters */
        body.admin-order-page {
            padding-top: 140px !important;
        }

        #scroll-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 40;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease-in-out;
        }
        #scroll-to-top.show {
            opacity: 1;
            visibility: visible;
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-neutral-50 font-sans text-neutral-900 antialiased selection:bg-gold-light selection:text-primary <?php echo $__env->yieldContent('body_class'); ?>">
    <!-- Header -->
    <?php echo $__env->make('layouts.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Scroll to Top Button -->
    <button id="scroll-to-top" class="bg-primary shadow-sm-soft transition-all duration-300 hover:bg-primary-light hover:-translate-y-0.5 text-white rounded-md-full p-3 shadow-sm-lg">
        <i class="fas fa-arrow-up"></i>
    </button>



    <!-- AI Security Toast -->
    <?php if(session('ai_warning')): ?>
        <div id="ai-security-toast" class="fixed top-24 right-5 z-50 transform transition-all duration-500 translate-x-full opacity-0">
            <div class="shadow-lg rounded-r-md px-4 py-3 min-w-[300px] flex items-start gap-4" style="background-color: #0A192F; border-left: 4px solid #DC143C;">
                <div class="mt-1" style="color: #DC143C;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shield-fill-exclamation" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m-.5 5a.5.5 0 0 1 1 0v3a.5.5 0 0 1-1 0zm.5 5.5a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h5 class="text-sm font-bold mb-1" style="color: #D4AF37; font-family: 'Playfair Display', serif; letter-spacing: 0.5px;">Security Advisory</h5>
                    <p class="text-xs mb-0" style="color: #F1F3F5; line-height: 1.4;"><?php echo e(session('ai_warning')); ?></p>
                </div>
                <button onclick="document.getElementById('ai-security-toast').style.display='none'" class="text-gray-400 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-Lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <?php if(auth()->guard()->check()): ?>
        <!-- Location Consent Modal (Client-side controlled) -->
        <div id="location-consent-modal" class="fixed inset-0 z-[10000] flex items-center justify-center bg-primary/40 backdrop-blur-sm px-4 hidden">
            <div class="bg-white rounded-3xl shadow-hover border border-neutral-100 max-w-md w-full overflow-hidden transform transition-all duration-500 scale-95 opacity-0">
                <div class="p-8 text-center">
                    <div class="w-20 h-20 bg-gold/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="map-pin" class="w-10 h-10 text-gold"></i>
                    </div>
                    <h3 class="text-2xl font-serif font-bold text-primary mb-4">Enhance Your Security</h3>
                    <p class="text-neutral-500 leading-relaxed mb-8">
                        To protect your account from unauthorized access, our AI needs to verify your login location. Would you like to enable precise location security?
                    </p>
                    <div class="flex flex-col gap-3">
                        <button onclick="handleLocationConsent(true)" class="w-full bg-primary text-white py-3.5 rounded-xl font-bold hover:bg-primary-light transition-all shadow-soft hover:shadow-hover hover:-translate-y-0.5">
                            Enable & Synchronize
                        </button>
                        <button onclick="handleLocationConsent(false)" class="w-full bg-white text-neutral-500 py-3.5 rounded-xl font-bold hover:bg-neutral-50 transition-all border border-neutral-100">
                            Maybe Later
                        </button>
                    </div>
                </div>
                <div class="bg-neutral-50 px-8 py-4 border-t border-neutral-100 flex items-center gap-2 justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                    <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">AI-Driven Protection Active</span>
                </div>
            </div>
        </div>

        <script>
            function checkLocationConsent() {
                const modal = document.getElementById('location-consent-modal');
                const content = modal.querySelector('div > div');
                const userId = "<?php echo e(auth()->id()); ?>";
                const consentKey = `location_consent_v1_${userId}`;

                if (!localStorage.getItem(consentKey)) {
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        content.classList.remove('scale-95', 'opacity-0');
                        content.classList.add('scale-100', 'opacity-100');
                    }, 100);
                }
            }

            function handleLocationConsent(agreed) {
                const modal = document.getElementById('location-consent-modal');
                const content = modal.querySelector('div > div');
                const userId = "<?php echo e(auth()->id()); ?>";
                const consentKey = `location_consent_v1_${userId}`;

                // Mark as asked for this user
                localStorage.setItem(consentKey, agreed ? 'agreed' : 'dismissed');

                if (agreed) {
                    capturePreciseLocation(true);
                }
                
                // Animate out
                content.classList.add('scale-95', 'opacity-0');
                modal.classList.add('opacity-0');
                setTimeout(() => modal.remove(), 500);
            }

            // Run check on load
            window.addEventListener('DOMContentLoaded', checkLocationConsent);
        </script>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="min-h-screen">
        <!-- Alert Messages -->
        <div id="global-alerts" class="fixed inset-x-0 top-20 z-[10001] flex flex-col items-center gap-3 px-4 pointer-events-none">
            <?php if($errors->any()): ?>
                <div class="toast-alert w-full max-w-3xl pointer-events-auto bg-red-700/95 border border-red-600/80 text-white px-4 py-3 rounded-3xl shadow-2xl backdrop-blur-sm border-opacity-80 auto-hide-alert" role="alert" aria-live="polite">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/15 text-white text-lg">
                            <i data-lucide="alert-circle" class="w-5 h-5"></i>
                        </span>
                        <div class="min-w-0 flex-1 text-sm leading-relaxed">
                            <ul class="list-disc list-inside text-sm">
                                <?php if($errors->count() > 1): ?>
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <?php echo e($errors->first()); ?>

                                <?php endif; ?>
                            </ul>
                        </div>
                        <button type="button" onclick="this.closest('.toast-alert').remove()" class="text-white/80 hover:text-white transition-colors rounded-full p-1.5">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(session('success')): ?>
                <div class="toast-alert w-full max-w-3xl pointer-events-auto bg-emerald-700/95 border border-emerald-600/80 text-white px-4 py-3 rounded-3xl shadow-2xl backdrop-blur-sm border-opacity-80 auto-hide-alert" role="status" aria-live="polite">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/15 text-white text-lg">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                        </span>
                        <div class="min-w-0 flex-1 text-sm leading-relaxed"><?php echo e(session('success')); ?></div>
                        <button type="button" onclick="this.closest('.toast-alert').remove()" class="text-white/80 hover:text-white transition-colors rounded-full p-1.5">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(session('info')): ?>
                <div class="toast-alert w-full max-w-3xl pointer-events-auto bg-sky-700/95 border border-sky-600/80 text-white px-4 py-3 rounded-3xl shadow-2xl backdrop-blur-sm border-opacity-80 auto-hide-alert" role="status" aria-live="polite">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/15 text-white text-lg">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </span>
                        <div class="min-w-0 flex-1 text-sm leading-relaxed"><?php echo e(session('info')); ?></div>
                        <button type="button" onclick="this.closest('.toast-alert').remove()" class="text-white/80 hover:text-white transition-colors rounded-full p-1.5">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="toast-alert w-full max-w-3xl pointer-events-auto bg-red-700/95 border border-red-600/80 text-white px-4 py-3 rounded-3xl shadow-2xl backdrop-blur-sm border-opacity-80 auto-hide-alert" role="alert" aria-live="polite">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/15 text-white text-lg">
                            <i data-lucide="alert-circle" class="w-5 h-5"></i>
                        </span>
                        <div class="min-w-0 flex-1 text-sm leading-relaxed"><?php echo e(session('error')); ?></div>
                        <button type="button" onclick="this.closest('.toast-alert').remove()" class="text-white/80 hover:text-white transition-colors rounded-full p-1.5">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <!-- Footer -->
    <?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // --- GPS Geolocation Capture ---
        function capturePreciseLocation(force = false) {
            if (!("geolocation" in navigator)) {
                console.error("Geolocation is not supported by this browser.");
                return;
            }

            // Secure context check (Geolocation requires HTTPS or localhost)
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                console.warn("Geolocation requires a secure context (HTTPS or localhost). Current: " + location.protocol);
                return;
            }

            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Store in sessionStorage for this session
                sessionStorage.setItem('user_lat', lat);
                sessionStorage.setItem('user_lng', lng);
                
                // Auto-fill any hidden inputs in forms that need GPS data
                updateLocationFields(lat, lng);
                
                console.log("GPS Location Captured:", lat, lng);
                
                // Optional: Show a small toast for success if forced
                if (force) {
                    showLocationStatus("Location synchronized successfully.");
                }
            }, function(error) {
                console.warn("Geolocation Error (" + error.code + "): " + error.message);
                if (force) {
                    let msg = "Could not get location. ";
                    if (error.code === 1) msg += "Please enable location permissions in your browser.";
                    else if (error.code === 2) msg += "Position unavailable.";
                    else if (error.code === 3) msg += "Request timed out.";
                    showLocationStatus(msg, true);
                }
            }, options);
        }

        function updateLocationFields(lat, lng) {
            document.querySelectorAll('input[name="latitude"]').forEach(el => el.value = lat);
            document.querySelectorAll('input[name="longitude"]').forEach(el => el.value = lng);
        }

        function showLocationStatus(message, isError = false) {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-24 right-6 px-6 py-3 rounded-xl shadow-lg text-sm font-bold transition-all duration-500 transform translate-x-full opacity-0 z-50 ${isError ? 'bg-red-600 text-white' : 'bg-green-600 text-white'}`;
            toast.innerHTML = `<div class="flex items-center gap-2"><i data-lucide="${isError ? 'alert-circle' : 'check-circle'}" class="w-4 h-4"></i>${message}</div>`;
            document.body.appendChild(toast);
            lucide.createIcons({attrs: { class: 'w-4 h-4' }});
            
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 100);
            
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 500);
            }, 5000);
        }

        // Try to capture on load
        capturePreciseLocation();

        // Re-check periodically or on form interactions
        document.addEventListener('focusin', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
                 const lat = sessionStorage.getItem('user_lat');
                 const lng = sessionStorage.getItem('user_lng');
                 if (lat && lng) {
                     updateLocationFields(lat, lng);
                 } else {
                     // If we don't have it yet, try capturing again
                     capturePreciseLocation();
                 }
             }
        });

        // Scroll to Top Button
        const scrollBtn = document.getElementById('scroll-to-top');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                scrollBtn.classList.add('show');
            } else {
                scrollBtn.classList.remove('show');
            }
        });

        scrollBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // user menu toggle logic (click-to-open, click-away to close)
        document.addEventListener('click', function(e) {
            const btn = document.getElementById('user-menu-button');
            const menu = document.getElementById('user-menu-dropdown');
            if (!btn || !menu) return;
            
            const isClickInsideBtn = btn.contains(e.target);
            const isClickInsideMenu = menu.contains(e.target);
            
            if (isClickInsideBtn) {
                if (menu.classList.contains('hidden')) {
                    menu.classList.remove('hidden');
                    setTimeout(() => {
                        menu.classList.remove('opacity-0', 'scale-95');
                        menu.classList.add('opacity-100', 'scale-100');
                    }, 10);
                } else {
                    menu.classList.remove('opacity-100', 'scale-100');
                    menu.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => {
                        menu.classList.add('hidden');
                    }, 200);
                }
            } else if (!isClickInsideMenu) {
                if (!menu.classList.contains('hidden')) {
                    menu.classList.remove('opacity-100', 'scale-100');
                    menu.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => {
                        menu.classList.add('hidden');
                    }, 200);
                }
            }
        });

        // Adjust content padding to keep page content clear of the fixed header
        // Removed adjustContentPadding because the header uses sticky positioning which handles the flow natively.

        window.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('ai-security-toast');
            if (toast) {
                setTimeout(() => {
                    toast.classList.remove('translate-x-full', 'opacity-0');
                    toast.classList.add('translate-x-0', 'opacity-100');
                }, 100);
                
                // Auto hide after 8s
                setTimeout(() => {
                    if (toast) {
                        toast.classList.remove('translate-x-0', 'opacity-100');
                        toast.classList.add('translate-x-full', 'opacity-0');
                        setTimeout(() => toast.remove(), 500);
                    }
                }, 8000);
            }

            // Auto hide order notifications and generic alerts
            const autoHideAlerts = document.querySelectorAll('.auto-hide-alert');
            if (autoHideAlerts.length > 0) {
                setTimeout(() => {
                    autoHideAlerts.forEach(alert => {
                        alert.style.transition = 'opacity 0.5s ease-out, margin 0.5s ease-out, height 0.5s ease-out';
                        alert.style.opacity = '0';
                        alert.style.overflow = 'hidden';
                        setTimeout(() => {
                            alert.style.height = '0px';
                            alert.style.marginTop = '0px';
                            alert.style.marginBottom = '0px';
                            alert.style.paddingTop = '0px';
                            alert.style.paddingBottom = '0px';
                        }, 500);
                        setTimeout(() => alert.remove(), 1000);
                    });
                }, 5000);
            }
        });
    </script>
</body>
</html>
<?php /**PATH C:\laragon\www\E-commerce2026\resources\views/layouts/app.blade.php ENDPATH**/ ?>