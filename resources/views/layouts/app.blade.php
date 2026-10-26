<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Kulturegram - Cultural Preservation Platform')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Resource Hints: warm up CDN connections early -->
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://cdn.tailwindcss.com">

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        body { font-family: 'Inter', sans-serif; }
        
        /* Mobile footprint reduction */
        @media (max-width: 640px) {
            html { font-size: 13px; }
            .h-16 { @apply h-14; } /* Tighter header */
            .p-8 { @apply p-6; }
            .p-10 { @apply p-7; }
            .gap-8 { @apply gap-4; }
        }

        /* Standardized Input Styling */
        .stnd-input {
            @apply rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none placeholder-stone-400 dark:placeholder-stone-500 transition-colors;
        }

        /* Modal Animations */
        @keyframes modal-pop-up {
            0% { opacity: 0; transform: scale(0.95) translateY(10px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        .animate-modal-pop-up {
            animation: modal-pop-up 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes backdrop-fade {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        .animate-backdrop-fade {
            animation: backdrop-fade 0.3s ease-out forwards;
        }

        /* Custom Modern Scrollbar */
        .modern-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        .modern-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .modern-scrollbar::-webkit-scrollbar-thumb {
            background: #d6d3d1; /* stone-300 */
            border-radius: 10px;
            transition: background 0.2s ease;
        }

        .modern-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a8a29e; /* stone-400 */
        }

        .dark .modern-scrollbar::-webkit-scrollbar-thumb {
            background: #44403c; /* stone-700 */
        }

        .dark .modern-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #57534e; /* stone-600 */
        }

        /* Firefox Support */
        .modern-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #d6d3d1 transparent;
        }

        .dark .modern-scrollbar {
            scrollbar-color: #44403c transparent;
        }

        /* Hide Scrollbar Utilities */
        .no-scrollbar::-webkit-scrollbar,
        .scrollbar-none::-webkit-scrollbar,
        .scrollbar-hide::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        .no-scrollbar,
        .scrollbar-none,
        .scrollbar-hide {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        /* Premium Tooltip Animation */
        @keyframes tooltip-scale {
            0% { opacity: 0; transform: translateY(-50%) scale(0.95); }
            100% { opacity: 1; transform: translateY(-50%) scale(1); }
        }
        .animate-tooltip {
            animation: tooltip-scale 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        /* ─── Navigation Progress Bar ─────────────────────── */
        #nav-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0%;
            background: #f97316;
            z-index: 9999;
            transition: width 0.4s ease, opacity 0.4s ease;
            opacity: 0;
            pointer-events: none;
            border-radius: 0 2px 2px 0;
            box-shadow: 0 0 10px rgba(249, 115, 22, 0.8), inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }
        #nav-progress.active {
            opacity: 1;
        }

        /* Glassy & Shiny Single Orange Styling */
        .glass-orange {
            background-color: #f97316 !important;
            background-image: none !important;
            box-shadow: inset 0 1px 0 0 rgba(255, 255, 255, 0.4), 0 4px 14px 0 rgba(249, 115, 22, 0.28) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.35) !important;
            transition: all 0.2s ease !important;
        }
        .glass-orange:hover {
            background-color: #ea580c !important;
            box-shadow: inset 0 1px 0 0 rgba(255, 255, 255, 0.5), 0 6px 18px 0 rgba(234, 88, 12, 0.38) !important;
        }
        .glass-orange:active {
            transform: scale(0.98);
        }
        /* ────────────────────────────────────────────────── */
    </style>
    
    <script>
        // FOUC Prevention Script
        const savedTheme = @json(auth()->check() ? (auth()->user()->preferences['theme'] ?? null) : null);
        const localTheme = localStorage.getItem('theme');
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        
        const theme = savedTheme || localTheme || systemTheme;
        
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        
        // Sync local storage if it differs from backend
        if (savedTheme && savedTheme !== localTheme) {
            localStorage.setItem('theme', savedTheme);
        }
    </script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        stone: {
                            50: '#fafaf9',
                            100: '#f5f5f4',
                            200: '#e7e5e4',
                            300: '#d6d3d1',
                            400: '#a8a29e',
                            500: '#78716c',
                            600: '#57534e',
                            700: '#44403c',
                            800: '#292524',
                            900: '#1c1917',
                        }
                    }
                }
            }
        }
    </script>
</head>
    <body class="h-full bg-stone-50 dark:bg-stone-900 antialiased text-stone-800 dark:text-gray-100 overflow-x-hidden w-full max-w-full"
          @php
              $skeletonRoutes = ['timeline.index', 'cultural-hub.index', 'discover.index', 'education.index', 'communities.index', 'events.index'];
              $showSkeleton = collect($skeletonRoutes)->filter(fn($route) => request()->routeIs($route))->isNotEmpty();
          @endphp
          x-data="{ pageLoading: false, isMainPage: {{ $showSkeleton ? 'true' : 'false' }} }" 
          x-init="
            if (isMainPage) {
                // Only show skeleton on genuinely poor connections.
                // Network Information API: effectiveType can be 'slow-2g', '2g', '3g', '4g'
                const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                const isSlowNetwork = conn && (
                    conn.saveData === true ||
                    conn.effectiveType === 'slow-2g' ||
                    conn.effectiveType === '2g'
                );

                if (isSlowNetwork) {
                    pageLoading = true;
                    // Dismiss as soon as the HTML structure is ready (not waiting for images)
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', () => pageLoading = false);
                    } else {
                        pageLoading = false;
                    }
                }
            }
            // Always instantly clear on bfcache restore (back/forward navigation)
            window.addEventListener('pageshow', () => pageLoading = false);
          ">
    <!-- Navigation Progress Bar (appears instantly on link click) -->
    <div id="nav-progress"></div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 h-16 bg-white/95 dark:bg-stone-900/95 backdrop-blur-sm border-b border-stone-200 dark:border-stone-800 z-50 flex transition-colors duration-200">
        <div class="w-full flex items-center h-full px-4 md:px-6 gap-4">
            <!-- Left: Logo -->
            <div class="flex items-center space-x-3 flex-shrink-0">
               <div class="w-6 h-6 sm:w-8 sm:h-8 bg-orange-500 shadow-md shadow-orange-500/25 border-t border-white/30 rounded-lg flex items-center justify-center">
                    <i data-lucide="book-open" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
                </div>
                <a href="{{ route('timeline.index') }}" class="text-base sm:text-xl font-black text-stone-900 dark:text-white hover:text-orange-500 transition-colors duration-200">
                    Kulturegram
                </a>
            </div>

            <!-- Center: Search Bar (Hidden on Mobile) -->
            @auth
            <div class="hidden md:flex flex-1 max-w-2xl mx-auto items-center">
                <form action="{{ route('timeline.index') }}" method="GET" class="relative w-full group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-stone-400 group-focus-within:text-amber-500 transition-colors"></i>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search stories, traditions, legacy..." 
                           class="w-full pl-11 pr-4 py-2.5 bg-stone-100/50 dark:bg-stone-800/50 backdrop-blur-md border border-stone-200 dark:border-stone-700 rounded-2xl text-sm font-bold placeholder:text-stone-400 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 focus:bg-white dark:focus:bg-stone-900 transition-all duration-300 shadow-sm text-stone-800 dark:text-stone-100">
                    <button type="submit" class="hidden"></button>
                </form>
            </div>
            @endauth

            <!-- Right: Icons & User Menu -->
            <div class="flex items-center space-x-2 md:space-x-4 flex-shrink-0 ml-auto">
                    @auth
                        <!-- Create Post Button (Desktop Header) -->
                        <button onclick="document.getElementById('createPostModal').classList.remove('hidden'); document.getElementById('createPostModal').classList.add('flex');"
                            class="hidden md:flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-orange-500 hover:bg-orange-600 text-white shadow-md shadow-orange-500/25 border-t border-white/30 hover:scale-105 transition-all duration-200 cursor-pointer"
                            title="Create Post">
                            <i data-lucide="plus" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </button>

                        <!-- Live Button -->
                        <a href="{{ route('live-stream.index') }}" 
                        class="w-8 h-8 sm:w-9 sm:h-9 relative p-1.5 sm:p-2 text-stone-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all duration-200" 
                        title="Go Live">
                            <i data-lucide="video" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </a>

                        <!-- Notifications -->
                        <a href="{{ route('notifications.index') }}" 
                        class="w-8 h-8 sm:w-9 sm:h-9 relative p-1.5 sm:p-2 text-stone-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all duration-200" 
                        title="Notifications">
                            <i data-lucide="bell" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            @if(auth()->user()->notifications()->unread()->count() > 0)
                                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 sm:w-3 sm:h-3 bg-red-500 rounded-full"></span>
                            @endif
                        </a>

                        <!-- Direct Profile Link -->
                        <a href="{{ route('profile.show') }}" 
                            class="flex items-center space-x-1 sm:space-x-2 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-lg p-1 transition-colors"
                            title="My Profile & Story">
                            <img src="{{ auth()->user()->profile_photo_url }}" 
                                 class="w-8 h-8 rounded-full object-cover border border-stone-200 dark:border-stone-700">
                            <span class="hidden lg:block text-sm font-black text-stone-700 dark:text-stone-300">{{ auth()->user()->name }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm sm:text-base text-stone-600 hover:text-stone-800 font-medium transition-colors">Sign In</a>
                    @endauth
                </div>
        </div>

    </header>

    <div class="flex flex-1 w-full max-w-full overflow-x-hidden">
        <aside class="hidden md:block md:fixed md:left-0 md:top-16 md:w-20 md:h-[calc(100vh-4rem)] md:bg-white dark:md:bg-stone-900 md:border-r md:border-stone-200 dark:md:border-stone-800 md:overflow-y-auto md:overflow-x-hidden modern-scrollbar z-40 transition-colors duration-200">
            <div class="p-4">
                <!-- Main Navigation -->
                <nav class="space-y-3">
                    @php
                    $menuItems = [
                        ['route' => 'timeline.index', 'icon' => 'home', 'label' => 'Timeline', 'tooltip' => 'Your chronological feed'],
                        ['route' => 'discover.index', 'icon' => 'compass', 'label' => 'Discover', 'tooltip' => 'Find new stories & cultures', 'auth' => true],
                        ['route' => 'cultural-hub.index', 'icon' => 'globe', 'label' => 'Cultural Hub', 'tooltip' => 'Explore global heritage'],
                        ['route' => 'communities.index', 'icon' => 'users', 'label' => 'Communities', 'tooltip' => 'Join cultural groups', 'auth' => true],
                        ['route' => 'events.index', 'icon' => 'calendar', 'label' => 'Events', 'tooltip' => 'Cultural events & meetups', 'auth' => true],
                        ['route' => 'education.index', 'icon' => 'graduation-cap', 'label' => 'Edu Hub', 'tooltip' => 'Classes, rituals & trainings'],
                        ['route' => 'profile.show', 'icon' => 'user', 'label' => 'My Story', 'tooltip' => 'Your life journey', 'auth' => true],
                        ['route' => 'vault.index', 'icon' => 'archive', 'label' => 'Vault', 'tooltip' => 'Private memories', 'auth' => true],
                        ['route' => 'notifications.index', 'icon' => 'bell', 'label' => 'Notifications', 'tooltip' => 'Stay updated', 'auth' => true],
                        ['route' => 'messages.index', 'icon' => 'message-circle', 'label' => 'Messages', 'tooltip' => 'Connect with others', 'auth' => true],
                    ];
                    @endphp
                    
                    @foreach($menuItems as $item)
                        @if(!isset($item['auth']) || auth()->check())
                        <div class="relative group">
                            <a href="{{ route($item['route']) }}" 
                               x-data
                               @mouseenter="$dispatch('show-tooltip', { text: '{{ $item['tooltip'] }}', x: $el.getBoundingClientRect().right + 12, y: $el.getBoundingClientRect().top + ($el.offsetHeight / 2) })"
                               @mouseleave="$dispatch('hide-tooltip')"
                            class="w-12 h-12 flex items-center justify-center rounded-xl transition-all duration-200 {{ request()->routeIs($item['route']) ? 'bg-orange-500 text-white shadow-md shadow-orange-500/25 border-t border-white/30' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 hover:text-stone-800 dark:hover:text-amber-500' }}">
                                <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                            </a>
                        </div>
                        @endif
                    @endforeach

                </nav>

                <!-- Secondary Navigation -->
                @auth
                <div class="mt-6 pt-4 border-t border-stone-200">
                    <nav class="space-y-3">
                        @php
                        $secondaryItems = [
                            ['route' => 'notifications.index', 'params' => [], 'icon' => 'bell', 'tooltip' => 'Your activity updates'],
                            ['route' => 'analytics.index', 'params' => [], 'icon' => 'bar-chart-3', 'tooltip' => 'Story insights'],
                            ['route' => 'settings.index', 'params' => [auth()->id()], 'icon' => 'settings', 'tooltip' => 'Account preferences'],
                        ];
                        @endphp
                        @foreach($secondaryItems as $item)
                        <div class="relative group">
                            <a href="{{ route($item['route'], $item['params']) }}" 
                               x-data
                               @mouseenter="$dispatch('show-tooltip', { text: '{{ $item['tooltip'] }}', x: $el.getBoundingClientRect().right + 12, y: $el.getBoundingClientRect().top + ($el.offsetHeight / 2) })"
                               @mouseleave="$dispatch('hide-tooltip')"
                            class="w-12 h-12 flex items-center justify-center rounded-xl transition-all duration-200 {{ request()->routeIs($item['route']) ? 'bg-orange-500 text-white shadow-md shadow-orange-500/25 border-t border-white/30' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 hover:text-stone-800 dark:hover:text-amber-500' }}">
                                <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                            </a>
                        </div>
                        @endforeach


                    </nav>
                </div>
                @endauth

                <!-- Brand Section (Hidden as requested) -->
                {{-- 
                <div class="mt-6 pt-4 border-t border-stone-200 dark:border-stone-800">
                    <div 
                         x-data
                         @mouseenter="$dispatch('show-tooltip', { text: 'Kulturegram preserves your authentic journey', x: $el.getBoundingClientRect().right + 12, y: $el.getBoundingClientRect().top + ($el.offsetHeight / 2) })"
                         @mouseleave="$dispatch('hide-tooltip')"
                         class="w-12 h-12 flex items-center justify-center rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-400 dark:text-stone-500 cursor-help">
                         <i data-lucide="activity" class="w-5 h-5"></i>
                    </div>
                    @guest
                    <a href="{{ route('register') }}" class="mt-3 w-12 h-8 glass-orange text-white text-xs rounded-lg transition-all duration-200 flex items-center justify-center font-medium">
                        Start
                    </a>
                    @endguest
                </div>
                --}}
            </div>
        </aside>


        <!-- Main Content -->
        <main class="flex-1 w-full max-w-full min-w-0 overflow-x-hidden pt-16 md:ml-20 {{ (request()->routeIs('messages.*')) ? 'pb-0' : 'pb-16' }} md:pb-0 min-h-screen relative">
            
            <!-- Global Skeleton Loader -->
            <div x-show="pageLoading" 
                 x-transition.opacity.duration.200ms
                 class="absolute inset-0 z-40 bg-stone-50 dark:bg-stone-900 p-4 sm:p-6 lg:p-8">
                <div class="max-w-7xl mx-auto animate-pulse space-y-8">
                    <!-- Header Skeleton -->
                    <div class="flex items-center justify-between">
                        <div class="w-1/3 lg:w-1/4 h-10 bg-stone-200 dark:bg-stone-800 rounded-xl"></div>
                        <div class="w-1/4 h-10 bg-stone-200 dark:bg-stone-800 rounded-xl hidden sm:block"></div>
                    </div>
                    
                    <!-- Content Skeleton -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="h-72 bg-stone-200 dark:bg-stone-800 rounded-3xl"></div>
                        <div class="h-72 bg-stone-200 dark:bg-stone-800 rounded-3xl"></div>
                        <div class="h-72 bg-stone-200 dark:bg-stone-800 rounded-3xl hidden lg:block"></div>
                        <div class="h-72 bg-stone-200 dark:bg-stone-800 rounded-3xl hidden lg:block"></div>
                        <div class="h-72 bg-stone-200 dark:bg-stone-800 rounded-3xl hidden lg:block"></div>
                        <div class="h-72 bg-stone-200 dark:bg-stone-800 rounded-3xl hidden lg:block"></div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div x-show="!pageLoading" x-cloak x-transition.opacity.duration.300ms>
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Global Tooltip (AlpineJS) -->
    <div x-data="{ tooltipVisible: false, tooltipText: '', tooltipX: 0, tooltipY: 0 }"
         @show-tooltip.window="tooltipVisible = true; tooltipText = $event.detail.text; tooltipX = $event.detail.x; tooltipY = $event.detail.y"
         @hide-tooltip.window="tooltipVisible = false"
         x-show="tooltipVisible"
         class="fixed z-[100] bg-stone-900/90 dark:bg-stone-800/90 backdrop-blur-md text-white px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap pointer-events-none shadow-xl border border-white/10 dark:border-stone-700/50 animate-tooltip"
         x-bind:style="`left: ${tooltipX}px; top: ${tooltipY}px;`"
         x-cloak>
        <span x-text="tooltipText"></span>
        <div class="absolute left-[-4px] top-1/2 transform -translate-y-1/2 w-2 h-2 bg-stone-900/90 dark:bg-stone-800/90 border-l border-b border-white/10 dark:border-stone-700/50 rotate-45"></div>
    </div>

    <!-- Fixed Mobile Bottom Navigation & Pop-up Drawer -->
    @if(!request()->routeIs('messages.show') && !request()->routeIs('live-stream.show'))
    <div x-data="{ openMoreNav: false }">

        <!-- Fixed 5-Slot Bottom Bar -->
        <nav class="fixed bottom-0 left-0 right-0 h-16 sm:h-18 bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border-t border-stone-200 dark:border-stone-800 md:hidden z-40 shadow-[0_-4px_16px_rgba(0,0,0,0.06)] transition-colors duration-200">
            <div class="grid grid-cols-5 h-full items-center px-1">
                
                <!-- 1. Home / Timeline -->
                <a href="{{ route('timeline.index') }}" 
                   class="flex flex-col items-center justify-center h-full transition-all {{ request()->routeIs('timeline.*') ? 'text-amber-600 dark:text-amber-500' : 'text-stone-500 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200' }}">
                    <i data-lucide="home" class="w-5 h-5 {{ request()->routeIs('timeline.*') ? 'scale-110 drop-shadow-[0_0_8px_rgba(217,119,6,0.3)]' : '' }}"></i>
                    <span class="text-[10px] mt-1 font-bold tracking-tight">Home</span>
                </a>

                <!-- 2. Search / Discover -->
                <a href="{{ route('discover.index') }}" 
                   class="flex flex-col items-center justify-center h-full transition-all {{ request()->routeIs('discover.*') ? 'text-amber-600 dark:text-amber-500' : 'text-stone-500 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200' }}">
                    <i data-lucide="compass" class="w-5 h-5 {{ request()->routeIs('discover.*') ? 'scale-110 drop-shadow-[0_0_8px_rgba(217,119,6,0.3)]' : '' }}"></i>
                    <span class="text-[10px] mt-1 font-bold tracking-tight">Search</span>
                </a>

                <!-- 3. Fixed Center Post (+) Button Anchor -->
                <div class="flex items-center justify-center h-full relative">
                    @auth
                        @if(request()->routeIs('cultural-hub.index'))
                            <a href="{{ route('cultural-hub.create') }}" 
                               class="flex flex-col items-center justify-center -mt-7 bg-orange-500 hover:bg-orange-600 w-14 h-14 rounded-full shadow-lg shadow-orange-500/30 border-4 border-white dark:border-stone-900 border-t-white/40 text-white transform active:scale-95 hover:scale-105 transition-all z-50 cursor-pointer"
                               title="Contribute Culture">
                                <i data-lucide="plus" class="w-7 h-7"></i>
                            </a>
                        @else
                            <button type="button" onclick="openCreatePostModal()" 
                                class="flex flex-col items-center justify-center -mt-7 bg-orange-500 hover:bg-orange-600 w-14 h-14 rounded-full shadow-lg shadow-orange-500/30 border-4 border-white dark:border-stone-900 border-t-white/40 text-white transform active:scale-95 hover:scale-105 transition-all z-50 cursor-pointer"
                                title="Share Your Story">
                                <i data-lucide="plus" class="w-7 h-7"></i>
                            </button>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="flex flex-col items-center justify-center text-stone-600 dark:text-stone-400">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                            <span class="text-[10px] mt-1 font-bold uppercase tracking-tight">Login</span>
                        </a>
                    @endauth
                </div>

                <!-- 4. Chat / Messages -->
                <a href="{{ route('messages.index') }}" 
                   class="flex flex-col items-center justify-center h-full transition-all {{ request()->routeIs('messages.*') ? 'text-amber-600 dark:text-amber-500' : 'text-stone-500 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200' }}">
                    <div class="relative">
                        <i data-lucide="message-circle" class="w-5 h-5 {{ request()->routeIs('messages.*') ? 'scale-110 drop-shadow-[0_0_8px_rgba(217,119,6,0.3)]' : '' }}"></i>
                    </div>
                    <span class="text-[10px] mt-1 font-bold tracking-tight">Chat</span>
                </a>

                <!-- 5. More Nav Pop-up Button -->
                @php
                    $isMoreActive = request()->routeIs('cultural-hub.*', 'education.*', 'communities.*', 'events.*', 'vault.*', 'notifications.*', 'profile.*', 'live-stream.*');
                @endphp
                <button type="button" 
                    @click="openMoreNav = true; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); })" 
                    class="flex flex-col items-center justify-center h-full transition-all cursor-pointer {{ $isMoreActive ? 'text-amber-600 dark:text-amber-500 font-black' : 'text-stone-500 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200' }}"
                    title="More Portals">
                    <div class="relative">
                        <i data-lucide="layout-grid" class="w-5 h-5 {{ $isMoreActive ? 'scale-110 drop-shadow-[0_0_8px_rgba(217,119,6,0.3)]' : '' }}"></i>
                        @auth
                            @if(auth()->user()->notifications()->unread()->count() > 0)
                                <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                            @endif
                        @endauth
                    </div>
                    <span class="text-[10px] mt-1 font-bold tracking-tight">More</span>
                </button>

            </div>
        </nav>

        <!-- Slide-up Bottom Sheet Modal for More Nav -->
        <div x-show="openMoreNav" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 md:hidden flex flex-col justify-end"
             @click.self="openMoreNav = false"
             @keydown.escape.window="openMoreNav = false">

            <div x-show="openMoreNav"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="bg-white dark:bg-stone-900 rounded-t-3xl border-t border-stone-200 dark:border-stone-800 shadow-2xl p-5 pb-8 max-h-[85vh] overflow-y-auto modern-scrollbar space-y-4">
                 
                 <!-- Grab Handle -->
                 <div class="w-12 h-1.5 bg-stone-300 dark:bg-stone-700 rounded-full mx-auto -mt-1 mb-1"></div>

                 <!-- Sheet Header -->
                 <div class="flex items-center justify-between pb-3 border-b border-stone-100 dark:border-stone-800">
                     <div class="flex items-center gap-2.5">
                         <div class="w-9 h-9 rounded-xl bg-orange-500 text-white flex items-center justify-center shadow-md shadow-orange-500/20 border-t border-white/30">
                             <i data-lucide="layout-grid" class="w-5 h-5"></i>
                         </div>
                         <div>
                             <h3 class="text-base font-black text-stone-900 dark:text-white leading-tight">Explore Portals</h3>
                             <p class="text-[11px] text-stone-500 dark:text-stone-400">Navigate all sections and cultural archives</p>
                         </div>
                     </div>
                     <button type="button" @click="openMoreNav = false" 
                         class="p-2 rounded-xl text-stone-400 hover:text-stone-700 dark:hover:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                         <i data-lucide="x" class="w-5 h-5"></i>
                     </button>
                 </div>

                 <!-- Grid of Additional Portals -->
                 <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                     
                     <!-- Cultural Hub -->
                     <a href="{{ route('cultural-hub.index') }}" 
                        class="p-3 sm:p-3.5 rounded-2xl border transition-all flex items-start gap-3 {{ request()->routeIs('cultural-hub.*') ? 'bg-amber-500/10 border-amber-500/30 text-amber-600 dark:text-amber-400' : 'bg-stone-50 dark:bg-stone-800/60 border-stone-200/70 dark:border-stone-700/60 hover:border-amber-400' }}">
                         <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                             <i data-lucide="globe" class="w-5 h-5"></i>
                         </div>
                         <div class="min-w-0">
                             <h4 class="font-bold text-xs sm:text-sm text-stone-900 dark:text-white truncate">Cultural Hub</h4>
                             <p class="text-[10px] text-stone-500 dark:text-stone-400 truncate">Global heritage</p>
                         </div>
                     </a>

                     <!-- Edu Hub -->
                     <a href="{{ route('education.index') }}" 
                        class="p-3 sm:p-3.5 rounded-2xl border transition-all flex items-start gap-3 {{ request()->routeIs('education.*') ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400' : 'bg-stone-50 dark:bg-stone-800/60 border-stone-200/70 dark:border-stone-700/60 hover:border-emerald-400' }}">
                         <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                             <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                         </div>
                         <div class="min-w-0">
                             <h4 class="font-bold text-xs sm:text-sm text-stone-900 dark:text-white truncate">Edu Hub</h4>
                             <p class="text-[10px] text-stone-500 dark:text-stone-400 truncate">Courses & rituals</p>
                         </div>
                     </a>

                     <!-- Communities -->
                     <a href="{{ route('communities.index') }}" 
                        class="p-3 sm:p-3.5 rounded-2xl border transition-all flex items-start gap-3 {{ request()->routeIs('communities.*') ? 'bg-blue-500/10 border-blue-500/30 text-blue-600 dark:text-blue-400' : 'bg-stone-50 dark:bg-stone-800/60 border-stone-200/70 dark:border-stone-700/60 hover:border-blue-400' }}">
                         <div class="w-9 h-9 rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                             <i data-lucide="users" class="w-5 h-5"></i>
                         </div>
                         <div class="min-w-0">
                             <h4 class="font-bold text-xs sm:text-sm text-stone-900 dark:text-white truncate">Communities</h4>
                             <p class="text-[10px] text-stone-500 dark:text-stone-400 truncate">Cultural groups</p>
                         </div>
                     </a>

                     <!-- Events -->
                     <a href="{{ route('events.index') }}" 
                        class="p-3 sm:p-3.5 rounded-2xl border transition-all flex items-start gap-3 {{ request()->routeIs('events.*') ? 'bg-purple-500/10 border-purple-500/30 text-purple-600 dark:text-purple-400' : 'bg-stone-50 dark:bg-stone-800/60 border-stone-200/70 dark:border-stone-700/60 hover:border-purple-400' }}">
                         <div class="w-9 h-9 rounded-xl bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                             <i data-lucide="calendar" class="w-5 h-5"></i>
                         </div>
                         <div class="min-w-0">
                             <h4 class="font-bold text-xs sm:text-sm text-stone-900 dark:text-white truncate">Events</h4>
                             <p class="text-[10px] text-stone-500 dark:text-stone-400 truncate">Festivals & meets</p>
                         </div>
                     </a>

                     <!-- Notifications / Alerts -->
                     <a href="{{ route('notifications.index') }}" 
                        class="p-3 sm:p-3.5 rounded-2xl border transition-all flex items-start gap-3 {{ request()->routeIs('notifications.*') ? 'bg-rose-500/10 border-rose-500/30 text-rose-600 dark:text-rose-400' : 'bg-stone-50 dark:bg-stone-800/60 border-stone-200/70 dark:border-stone-700/60 hover:border-rose-400' }}">
                         <div class="w-9 h-9 rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 relative">
                             <i data-lucide="bell" class="w-5 h-5"></i>
                             @auth
                                 @if(auth()->user()->notifications()->unread()->count() > 0)
                                     <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                                 @endif
                             @endauth
                         </div>
                         <div class="min-w-0">
                             <div class="flex items-center gap-1.5">
                                 <h4 class="font-bold text-xs sm:text-sm text-stone-900 dark:text-white truncate">Alerts</h4>
                                 @auth
                                     @if(auth()->user()->notifications()->unread()->count() > 0)
                                         <span class="px-1.5 py-0.2 bg-red-500 text-white text-[9px] font-black rounded-full">{{ auth()->user()->notifications()->unread()->count() }}</span>
                                     @endif
                                 @endauth
                             </div>
                             <p class="text-[10px] text-stone-500 dark:text-stone-400 truncate">Updates & mentions</p>
                         </div>
                     </a>

                     <!-- Private Vault -->
                     <a href="{{ route('vault.index') }}" 
                        class="p-3 sm:p-3.5 rounded-2xl border transition-all flex items-start gap-3 {{ request()->routeIs('vault.*') ? 'bg-indigo-500/10 border-indigo-500/30 text-indigo-600 dark:text-indigo-400' : 'bg-stone-50 dark:bg-stone-800/60 border-stone-200/70 dark:border-stone-700/60 hover:border-indigo-400' }}">
                         <div class="w-9 h-9 rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                             <i data-lucide="archive" class="w-5 h-5"></i>
                         </div>
                         <div class="min-w-0">
                             <h4 class="font-bold text-xs sm:text-sm text-stone-900 dark:text-white truncate">Vault</h4>
                             <p class="text-[10px] text-stone-500 dark:text-stone-400 truncate">Private archives</p>
                         </div>
                     </a>

                     <!-- My Profile & Story -->
                     <a href="{{ route('profile.show') }}" 
                        class="p-3 sm:p-3.5 rounded-2xl border transition-all flex items-start gap-3 {{ request()->routeIs('profile.*') ? 'bg-amber-500/10 border-amber-500/30 text-amber-600 dark:text-amber-400' : 'bg-stone-50 dark:bg-stone-800/60 border-stone-200/70 dark:border-stone-700/60 hover:border-amber-400' }}">
                         <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                             <i data-lucide="user" class="w-5 h-5"></i>
                         </div>
                         <div class="min-w-0">
                             <h4 class="font-bold text-xs sm:text-sm text-stone-900 dark:text-white truncate">My Story</h4>
                             <p class="text-[10px] text-stone-500 dark:text-stone-400 truncate">Heritage timeline</p>
                         </div>
                     </a>

                     <!-- Live Streams -->
                     <a href="{{ route('live-stream.index') }}" 
                        class="p-3 sm:p-3.5 rounded-2xl border transition-all flex items-start gap-3 {{ request()->routeIs('live-stream.*') ? 'bg-red-500/10 border-red-500/30 text-red-600 dark:text-red-400' : 'bg-stone-50 dark:bg-stone-800/60 border-stone-200/70 dark:border-stone-700/60 hover:border-red-400' }}">
                         <div class="w-9 h-9 rounded-xl bg-red-500/15 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                             <i data-lucide="video" class="w-5 h-5"></i>
                         </div>
                         <div class="min-w-0">
                             <h4 class="font-bold text-xs sm:text-sm text-stone-900 dark:text-white truncate">Live Streams</h4>
                             <p class="text-[10px] text-stone-500 dark:text-stone-400 truncate">Broadcasts</p>
                         </div>
                     </a>

                 </div>

                 @auth
                     @if(auth()->user()->is_admin)
                         <div class="pt-1">
                             <a href="{{ route('admin.cultures.index') }}" 
                                class="w-full p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-between">
                                 <div class="flex items-center gap-2.5">
                                     <i data-lucide="shield-alert" class="w-5 h-5 text-amber-500"></i>
                                     <span class="font-bold text-xs sm:text-sm">Admin Moderation Center</span>
                                 </div>
                                 <i data-lucide="arrow-right" class="w-4 h-4"></i>
                             </a>
                         </div>
                     @endif
                 @endauth

            </div>
        </div>

    </div>
    @endif

    <!-- Create Post Modal -->
    @auth
    @include('partials.image-compression')
    <div id="createPostModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 animate-backdrop-fade">
        <div class="animate-modal-pop-up bg-white dark:bg-stone-900 rounded-none md:rounded-2xl w-[95%] sm:w-[90%] md:w-full h-[90vh] md:h-auto md:max-w-2xl md:max-h-[90vh] overflow-y-auto mx-auto shadow-lg">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-stone-900 border-b border-stone-200 dark:border-stone-800 p-3 md:p-4 flex items-center justify-between">
            <h3 class="text-base md:text-lg font-bold text-stone-800 dark:text-stone-100">Share Your Story</h3>
            <button onclick="closeCreatePostModal()" class="p-2 text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('timeline.store') }}" method="POST" enctype="multipart/form-data" 
              @submit.prevent="if(await handleFormImageCompression($el)) $el.submit()"
              class="p-4 md:p-6 space-y-4 text-sm md:text-base">
            @csrf

            <!-- User Info -->
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                    <span class="text-white font-semibold text-sm md:text-base">{{ auth()->user()->initials }}</span>
                </div>
                <div>
                    <p class="font-semibold text-stone-800 dark:text-stone-200 text-sm md:text-base">{{ auth()->user()->name }}</p>
                    <p class="text-xs md:text-sm text-stone-500 dark:text-stone-400">@ {{ auth()->user()->username }}</p>
                </div>
            </div>

            <!-- Content -->
            <textarea 
                name="content" 
                required 
                placeholder="What's happening in your story today? Share your cultural insights, daily experiences, or heritage discoveries... (Tip: use @username to tag people and #hashtag to categorize!)" 
                class="w-full h-24 md:h-32 resize-none border-2 border-stone-200 dark:border-stone-700 bg-transparent text-stone-800 dark:text-stone-100 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:focus:border-amber-500 hover:border-amber-500 dark:hover:border-stone-600 placeholder-stone-400 dark:placeholder-stone-500 leading-relaxed p-2 md:p-3 transition-all text-sm md:text-base"></textarea>

            <!-- Life Chapter -->
            <div>
                <label class="block text-xs md:text-sm font-medium text-stone-700 dark:text-stone-300 mb-1 md:mb-2">Life Chapter</label>
                <select 
                    name="chapter" 
                    class="w-full border-2 border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-800 dark:text-stone-200 rounded-lg px-2 md:px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 hover:border-amber-500 dark:hover:border-stone-600 transition-all text-sm md:text-base">
                    <option value="">Select a chapter...</option>
                    <option value="Cultural Tech Innovation">Cultural Tech Innovation</option>
                    <option value="Software Architecture Mastery">Software Architecture Mastery</option>
                    <option value="University & Cultural Awakening">University & Cultural Awakening</option>
                    <option value="Roots & Foundation">Roots & Foundation</option>
                    <option value="Community Building">Community Building</option>
                    <option value="Heritage Preservation">Heritage Preservation</option>
                </select>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-xs md:text-sm font-medium text-stone-700 dark:text-stone-300 mb-1 md:mb-2">Location (Optional)</label>
                <div class="relative">
                    <i data-lucide="map-pin" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-stone-400 dark:text-stone-500"></i>
                    <input 
                        type="text" 
                        name="location" 
                        placeholder="Where are you?" 
                        class="w-full pl-9 pr-3 py-2 border-2 border-stone-200 dark:border-stone-700 bg-transparent text-stone-800 dark:text-stone-100 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:focus:border-amber-500 hover:border-amber-500 dark:hover:border-stone-600 transition-all text-sm md:text-base">
                </div>
            </div>

            <!-- Privacy -->
            <div>
                <label class="block text-xs md:text-sm font-medium text-stone-700 dark:text-stone-300 mb-1 md:mb-2">Privacy</label>
                <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-3">
                    <label class="flex-1 flex items-center justify-center space-x-2 py-2 px-3 rounded-lg border-2 border-stone-200 dark:border-stone-700 cursor-pointer hover:border-amber-500 dark:hover:border-stone-500 focus-within:border-amber-500 transition-all text-stone-800 dark:text-stone-200">
                        <input type="radio" name="privacy" value="public" checked class="text-amber-500 dark:text-amber-500 bg-white dark:bg-stone-900 border-stone-300 dark:border-stone-600">
                        <i data-lucide="globe" class="w-4 h-4"></i>
                        <span class="text-xs md:text-sm font-medium">Public Timeline</span>
                    </label>
                    <label class="flex-1 flex items-center justify-center space-x-2 py-2 px-3 rounded-lg border-2 border-stone-200 dark:border-stone-700 cursor-pointer hover:border-amber-500 dark:hover:border-stone-500 focus-within:border-amber-500 transition-all text-stone-800 dark:text-stone-200">
                        <input type="radio" name="privacy" value="private" class="text-amber-500 dark:text-amber-500 bg-white dark:bg-stone-900 border-stone-300 dark:border-stone-600">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        <span class="text-xs md:text-sm font-medium">Private Story</span>
                    </label>
                    <label class="flex-1 flex items-center justify-center space-x-2 py-2 px-3 rounded-lg border-2 border-stone-200 dark:border-stone-700 cursor-pointer hover:border-amber-500 dark:hover:border-stone-500 focus-within:border-amber-500 transition-all text-stone-800 dark:text-stone-200">
                        <input type="radio" name="privacy" value="vault" class="text-amber-500 dark:text-amber-500 bg-white dark:bg-stone-900 border-stone-300 dark:border-stone-600">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        <span class="text-xs md:text-sm font-medium">Personal Vault</span>
                    </label>
                </div>
            </div>

            <!-- Media Upload (Photo, Video, Audio) -->
            <div x-data="{ mediaType: 'image' }">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs md:text-sm font-medium text-stone-700 dark:text-stone-300">Media Attachment (Optional)</label>
                    <div class="flex items-center space-x-1 text-xs">
                        <button type="button" @click="mediaType = 'image'" :class="mediaType === 'image' ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-bold' : 'text-stone-500 hover:text-stone-700'" class="px-2 py-0.5 rounded transition">Photo</button>
                        <button type="button" @click="mediaType = 'video'" :class="mediaType === 'video' ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-bold' : 'text-stone-500 hover:text-stone-700'" class="px-2 py-0.5 rounded transition">Video</button>
                        <button type="button" @click="mediaType = 'audio'" :class="mediaType === 'audio' ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-bold' : 'text-stone-500 hover:text-stone-700'" class="px-2 py-0.5 rounded transition">Audio</button>
                    </div>
                </div>

                <!-- Image Dropzone -->
                <div x-show="mediaType === 'image'" id="dropzone" class="w-full border-2 border-dashed border-stone-300 dark:border-stone-700 rounded-lg p-3 md:p-5 text-center cursor-pointer hover:border-amber-500 dark:hover:border-stone-500 transition-colors">
                    <input type="file" name="image" accept="image/*" id="fileInput" class="hidden">
                    <i data-lucide="image" class="mx-auto w-7 h-7 text-stone-400 mb-1"></i>
                    <p class="text-xs md:text-sm text-stone-500 dark:text-stone-400">Drag & drop a photo or click to select</p>
                    <p id="fileName" class="mt-1 text-xs text-amber-600 dark:text-amber-400 font-medium"></p>
                </div>

                <!-- Video Dropzone -->
                <div x-show="mediaType === 'video'" class="w-full border-2 border-dashed border-stone-300 dark:border-stone-700 rounded-lg p-3 md:p-5 text-center cursor-pointer hover:border-amber-500 dark:hover:border-stone-500 transition-colors" onclick="document.getElementById('videoInput').click()">
                    <input type="file" name="video" accept="video/mp4,video/webm,video/quicktime" id="videoInput" class="hidden" onchange="document.getElementById('videoFileName').textContent = this.files[0]?.name || ''">
                    <i data-lucide="video" class="mx-auto w-7 h-7 text-stone-400 mb-1"></i>
                    <p class="text-xs md:text-sm text-stone-500 dark:text-stone-400">Select or drop a video clip (MP4, WebM)</p>
                    <p id="videoFileName" class="mt-1 text-xs text-amber-600 dark:text-amber-400 font-medium"></p>
                </div>

                <!-- Audio Dropzone -->
                <div x-show="mediaType === 'audio'" class="w-full border-2 border-dashed border-stone-300 dark:border-stone-700 rounded-lg p-3 md:p-5 text-center cursor-pointer hover:border-amber-500 dark:hover:border-stone-500 transition-colors" onclick="document.getElementById('audioInput').click()">
                    <input type="file" name="audio" accept="audio/mp3,audio/wav,audio/ogg,audio/m4a" id="audioInput" class="hidden" onchange="document.getElementById('audioFileName').textContent = this.files[0]?.name || ''">
                    <i data-lucide="mic" class="mx-auto w-7 h-7 text-stone-400 mb-1"></i>
                    <p class="text-xs md:text-sm text-stone-500 dark:text-stone-400">Select or drop an audio recording (MP3, WAV, M4A)</p>
                    <p id="audioFileName" class="mt-1 text-xs text-amber-600 dark:text-amber-400 font-medium"></p>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="mt-4 md:mt-6 pt-4 md:pt-6 border-t border-stone-200 dark:border-stone-800 flex flex-col md:flex-row items-start md:items-center justify-between space-y-3 md:space-y-0">
                <div class="text-xs md:text-sm text-stone-500 dark:text-stone-400 flex flex-col md:flex-row items-start md:items-center space-y-1 md:space-y-0 md:space-x-4">
                    <span class="flex items-center space-x-1">
                        <i data-lucide="heart" class="w-4 h-4 text-red-500"></i>
                        <span>TAP to appreciate</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
                        <span>CHECK-IN to attend</span>
                    </span>
                </div>
                <div class="flex space-x-2 md:space-x-3">
                    <button 
                        type="button" 
                        onclick="closeCreatePostModal()" 
                        class="px-3 md:px-4 py-2 text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200 font-medium transition-colors text-sm md:text-base">
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="bg-orange-500 hover:bg-orange-600 text-white px-4 md:px-6 py-2 rounded-xl shadow-md shadow-orange-500/25 border-t border-white/30 transition-all duration-200 font-bold text-sm md:text-base cursor-pointer">
                        Share Story
                    </button>
                </div>
            </div>
        </form>
    </div>


    <script>
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');

        dropzone.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', () => {
            if(fileInput.files.length > 0) {
                fileName.textContent = fileInput.files[0].name;
            }
        });

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('border-amber-500', 'bg-amber-50/20');
        });

        dropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-amber-500', 'bg-amber-50/20');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-amber-500', 'bg-amber-50/20');
            if(e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                fileName.textContent = e.dataTransfer.files[0].name;
            }
        });
    </script>
    @endauth


    <script>
        lucide.createIcons();
        function openCreatePostModal() {
            document.getElementById('createPostModal').classList.remove('hidden');
            document.getElementById('createPostModal').classList.add('flex');
        }
        function closeCreatePostModal() {
            document.getElementById('createPostModal').classList.add('hidden');
            document.getElementById('createPostModal').classList.remove('flex');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeCreatePostModal();
        });
    </script>
    
    <script>
        // ─── Navigation Progress Bar ────────────────────────────────────────────
        (function() {
            const bar = document.getElementById('nav-progress');
            let timer = null;
            let width = 0;

            function start() {
                clearTimeout(timer);
                width = 0;
                bar.style.width = '0%';
                bar.style.opacity = '1';
                bar.classList.add('active');
                // Animate to ~80% quickly, then slow down (simulate loading)
                step();
            }

            function step() {
                if (width < 70) {
                    width += Math.random() * 12 + 4;
                } else if (width < 90) {
                    width += Math.random() * 2 + 0.5;
                } else {
                    return; // Hold at ~90% until page finishes
                }
                if (width > 90) width = 90;
                bar.style.width = width + '%';
                timer = setTimeout(step, 250);
            }

            function finish() {
                clearTimeout(timer);
                bar.style.transition = 'width 0.2s ease, opacity 0.4s ease 0.2s';
                bar.style.width = '100%';
                setTimeout(() => {
                    bar.style.opacity = '0';
                    setTimeout(() => {
                        bar.style.width = '0%';
                        bar.style.transition = 'width 0.4s ease, opacity 0.4s ease';
                    }, 400);
                }, 200);
            }

            // Fire on every internal link click
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (!link) return;
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript') ||
                    href.startsWith('mailto') || link.getAttribute('target') === '_blank') return;
                // Only same-origin navigations
                try {
                    const url = new URL(href, window.location.origin);
                    if (url.origin === window.location.origin) start();
                } catch(ex) {}
            });

            // Fire on form submissions too
            document.addEventListener('submit', function() { start(); });

            // Complete when the new page is ready
            window.addEventListener('DOMContentLoaded', finish);
            window.addEventListener('pageshow', finish);
        })();
        // ────────────────────────────────────────────────────────────────────────
    </script>

    @include('partials.welcome-flyer')

    @stack('scripts')
</body>
</html>
