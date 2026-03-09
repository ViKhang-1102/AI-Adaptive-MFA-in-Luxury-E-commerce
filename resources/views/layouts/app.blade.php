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
        /* Reset browser default spacing */
        html, body {
            margin: 0;
            padding: 0;
            /* The header is fixed-position; content padding is handled by `.content-wrapper` so it stays below the header. */
        }

        /* Keep header pinned at the top and ensure it is visible above other content */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            width: 100%;
            background-color: rgba(255,255,255,0.92); /* ensure header stays readable over content */
            backdrop-filter: blur(12px);
        }

        /* Content padding is adjusted via JS based on the header's height (and this acts as a fallback). */
        .content-wrapper {
            padding-top: 6rem;
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

    @if (session('info'))
        <div class="bg-blue-50 border border-blue-300 text-blue-700 px-4 py-3 rounded-md m-4">
            {{ session('info') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md m-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- AI Security Toast -->
    @if (session('ai_warning'))
        <div id="ai-security-toast" class="fixed top-24 right-5 z-50 transform transition-all duration-500 translate-x-full opacity-0">
            <div class="shadow-lg rounded-r-md px-4 py-3 min-w-[300px] flex items-start gap-4" style="background-color: #0A192F; border-left: 4px solid #DC143C;">
                <div class="mt-1" style="color: #DC143C;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shield-fill-exclamation" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m-.5 5a.5.5 0 0 1 1 0v3a.5.5 0 0 1-1 0zm.5 5.5a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h5 class="text-sm font-bold mb-1" style="color: #D4AF37; font-family: 'Playfair Display', serif; letter-spacing: 0.5px;">Security Advisory</h5>
                    <p class="text-xs mb-0" style="color: #F1F3F5; line-height: 1.4;">{{ session('ai_warning') }}</p>
                </div>
                <button onclick="document.getElementById('ai-security-toast').style.display='none'" class="text-gray-400 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-Lg" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="min-h-screen content-wrapper">
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

        // Adjust content padding to keep page content clear of the fixed header
        function adjustContentPadding() {
            const header = document.querySelector('header');
            const content = document.querySelector('.content-wrapper');
            if (!header || !content) return;

            const headerHeight = header.getBoundingClientRect().height;
            content.style.paddingTop = `${headerHeight + 16}px`;
        }

        window.addEventListener('DOMContentLoaded', adjustContentPadding);
        window.addEventListener('resize', adjustContentPadding);

        // Trigger AI Toast animation
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
        });
    </script>
</body>
</html>
