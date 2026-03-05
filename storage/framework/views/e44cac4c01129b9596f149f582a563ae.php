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
    <?php echo $__env->yieldPushContent('styles'); ?>
    <style>
        header {
            position: sticky;
            top: 0;
            z-index: 50;
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
</head>
<body class="bg-neutral-50 font-sans text-neutral-900 antialiased selection:bg-gold-light selection:text-primary">
    <!-- Header -->
    <?php echo $__env->make('layouts.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Scroll to Top Button -->
    <button id="scroll-to-top" class="bg-primary shadow-sm-soft transition-all duration-300 hover:bg-primary-light hover:-translate-y-0.5 text-white rounded-md-full p-3 shadow-sm-lg">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Alert Messages -->
    <?php if($errors->any()): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md m-4">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md m-4">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md m-4">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="min-h-screen">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <!-- Footer -->
    <?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

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
    </script>
</body>
</html>
<?php /**PATH C:\laragon\www\E-commerce2026\resources\views/layouts/app.blade.php ENDPATH**/ ?>