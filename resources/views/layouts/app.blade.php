<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Commerce Platform')</title>
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
    @stack('styles')
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
    @include('layouts.header')

    <!-- Scroll to Top Button -->
    <button id="scroll-to-top" class="bg-primary shadow-sm-soft transition-all duration-300 hover:bg-primary-light hover:-translate-y-0.5 text-white rounded-md-full p-3 shadow-sm-lg">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md m-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md m-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md m-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Main Content -->
    <div class="min-h-screen">
        @yield('content')
    </div>

    <!-- Footer -->
    @include('layouts.footer')

    @stack('scripts')

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
