<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Timeline - Cultural Preservation Platform')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
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
            @apply rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-800 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 focus:outline-none placeholder-stone-400 dark:placeholder-stone-500;
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
<body class="h-full bg-stone-50 dark:bg-stone-900 antialiased text-stone-800 dark:text-gray-100"
      x-data="{ pageLoading: true }" 
      x-init="window.addEventListener('load', () => pageLoading = false); window.addEventListener('beforeunload', () => pageLoading = true)">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 h-16 bg-white/95 dark:bg-stone-900/95 backdrop-blur-sm border-b border-stone-200 dark:border-stone-800 z-50 flex transition-colors duration-200">
        <div class="w-full flex items-center h-full px-4 md:px-6 gap-4">
            <!-- Left: Logo -->
            <div class="flex items-center space-x-3 flex-shrink-0">
               <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg flex items-center justify-center">
                    <i data-lucide="book-open" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
                </div>
                <a href="{{ route('timeline.index') }}" class="text-base sm:text-xl font-bold bg-gradient-to-r from-stone-800 to-stone-600 bg-clip-text text-transparent hover:from-amber-600 hover:to-orange-600 transition-all duration-200">
                    Timeline
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
                            class="hidden md:flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-sm hover:shadow hover:scale-105 transition-all duration-200"
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

                        <!-- User Menu -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" 
                                class="flex items-center space-x-1 sm:space-x-2 hover:bg-stone-100 rounded-lg p-1 transition-colors">
                                <img src="{{ auth()->user()->profile_photo_url }}" 
                                     class="w-8 h-8 rounded-full object-cover border border-stone-200">
                                <span class="hidden lg:block text-sm font-black text-stone-700">{{ auth()->user()->name }}</span>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                class="absolute right-0 mt-2 w-44 sm:w-48 bg-white rounded-lg shadow-lg border border-stone-200 py-2">
                                <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-xs sm:text-sm text-stone-700 hover:bg-stone-50">My Profile</a>
                                <a href="{{ route('settings.index',auth()->user()->id) }}" class="block px-4 py-2 text-xs sm:text-sm text-stone-700 hover:bg-stone-50">Settings</a>
                                <hr class="my-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-xs sm:text-sm text-red-600 hover:bg-red-50">Sign Out</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm sm:text-base text-stone-600 hover:text-stone-800 font-medium transition-colors">Sign In</a>
                    @endauth
                </div>
        </div>

    </header>

    <div class="flex flex-1">
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
                            class="w-12 h-12 flex items-center justify-center rounded-xl transition-all duration-200 {{ request()->routeIs($item['route']) ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 hover:text-stone-800 dark:hover:text-amber-500' }}">
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
                            class="w-12 h-12 flex items-center justify-center rounded-xl transition-all duration-200 {{ request()->routeIs($item['route']) ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-md' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 hover:text-stone-800 dark:hover:text-amber-500' }}">
                                <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                            </a>
                        </div>
                        @endforeach
                    </nav>
                </div>
                @endauth

                <!-- Brand Section -->
                <div class="mt-6 pt-4 border-t border-stone-200 dark:border-stone-800">
                    <div 
                         x-data
                         @mouseenter="$dispatch('show-tooltip', { text: 'Timeline preserves your authentic journey', x: $el.getBoundingClientRect().right + 12, y: $el.getBoundingClientRect().top + ($el.offsetHeight / 2) })"
                         @mouseleave="$dispatch('hide-tooltip')"
                         class="w-12 h-12 flex items-center justify-center rounded-xl bg-stone-100 dark:bg-stone-800 text-stone-400 dark:text-stone-500 cursor-help">
                         <i data-lucide="activity" class="w-5 h-5"></i>
                    </div>
                    @guest
                    <a href="{{ route('register') }}" class="mt-3 w-12 h-8 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all duration-200 flex items-center justify-center font-medium">
                        Start
                    </a>
                    @endguest
                </div>
            </div>
        </aside>


        <!-- Main Content -->
        <main class="flex-1 pt-16 md:ml-20 {{ (request()->routeIs('messages.*')) ? 'pb-0' : 'pb-16' }} md:pb-0 min-h-screen relative">
            
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
         x-transition.opacity.duration.200ms
         class="fixed z-[100] bg-stone-800 text-white px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap pointer-events-none shadow-lg"
         x-bind:style="`left: ${tooltipX}px; top: ${tooltipY}px; transform: translateY(-50%)`"
         x-cloak>
        <span x-text="tooltipText"></span>
        <div class="absolute left-0 top-1/2 transform -translate-x-1 -translate-y-1/2 w-2 h-2 bg-stone-800 rotate-45"></div>
    </div>

    <!-- Dynamic Reel Mobile Navigation (Double Gap Illusion) -->
    @if(!request()->routeIs('messages.show') && !request()->routeIs('live-stream.show'))
    <div class="fixed bottom-0 left-0 right-0 h-20 bg-white/95 dark:bg-stone-900/95 backdrop-blur-md border-t border-stone-200 dark:border-stone-800 md:hidden z-50 shadow-[0_-4px_12px_rgba(0,0,0,0.05)] transition-colors duration-200">
        
        <!-- Fixed Center Post Button Anchor -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="w-[20vw] flex items-center justify-center">
                @auth
                    @if(request()->routeIs('cultural-hub.index'))
                        <a href="{{ route('cultural-hub.create') }}" 
                           class="pointer-events-auto flex flex-col items-center justify-center -mt-10 bg-gradient-to-tr from-amber-500 to-orange-600 w-16 h-16 rounded-full shadow-lg border-4 border-white dark:border-stone-900 text-white transform active:scale-95 transition-all z-[60]">
                            <i data-lucide="plus" class="w-8 h-8"></i>
                        </a>
                    @else
                        <button onclick="openCreatePostModal()" 
                            class="pointer-events-auto flex flex-col items-center justify-center -mt-10 bg-gradient-to-tr from-amber-500 to-orange-600 w-16 h-16 rounded-full shadow-lg border-4 border-white dark:border-stone-900 text-white transform active:scale-95 transition-all z-[60]">
                            <i data-lucide="plus" class="w-8 h-8"></i>
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="pointer-events-auto flex flex-col items-center justify-center text-stone-600 dark:text-stone-400">
                        <i data-lucide="log-in" class="w-7 h-7"></i>
                        <span class="text-[11px] mt-1 font-bold uppercase tracking-tighter">Login</span>
                    </a>
                @endauth
            </div>
        </div>

        <!-- Scrollable Icon Reel -->
        <div class="h-full overflow-x-auto scrollbar-hide snap-x snap-mandatory flex items-center">
            @php
                $reelItems = [
                    ['route' => 'timeline.index', 'icon' => 'home', 'label' => 'Home'],
                    ['route' => 'discover.index', 'icon' => 'compass', 'label' => 'Search'],
                    ['type' => 'gap'], // Alignment for Slide 1
                    ['route' => 'cultural-hub.index', 'icon' => 'globe', 'label' => 'Hub'],
                    ['route' => 'education.index', 'icon' => 'graduation-cap', 'label' => 'Edu'],
                    ['route' => 'messages.index', 'icon' => 'message-circle', 'label' => 'Chat'],
                    ['type' => 'gap'], // Alignment for Slide 2
                     ['route' => 'communities.index', 'icon' => 'users', 'label' => 'Communities'],
                    ['route' => 'notifications.index', 'icon' => 'bell', 'label' => 'Alerts'],
                    ['type' => 'gap'], // Alignment for Slide 3
                    ['route' => 'events.index', 'icon' => 'calendar', 'label' => 'Events'],
                    ['route' => 'profile.show', 'icon' => 'user', 'label' => 'Profile'],
                    ];
            @endphp

            @foreach($reelItems as $item)
                @if(isset($item['type']) && $item['type'] === 'gap')
                    <div class="min-w-[20vw] h-full flex items-center justify-center snap-center"></div>
                @else
                    <a href="{{ route($item['route']) }}" 
                       class="min-w-[20vw] h-full flex flex-col items-center justify-center snap-center transition-all {{ request()->routeIs($item['route']) ? 'text-amber-600' : 'text-stone-500 dark:text-stone-400' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-6 h-6 {{ request()->routeIs($item['route']) ? 'drop-shadow-[0_0_8px_rgba(217,119,6,0.3)] scale-110' : '' }}"></i>
                        <span class="text-[11px] mt-1 font-bold uppercase tracking-tighter">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
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
                placeholder="What's happening in your story today? Share your cultural insights, daily experiences, or heritage discoveries..." 
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

            <!-- Drag & Drop Image Upload -->
            <div>
                <label class="block text-xs md:text-sm font-medium text-stone-700 dark:text-stone-300 mb-1 md:mb-2">Add Image (Optional)</label>
                <div 
                    id="dropzone" 
                    class="w-full border-2 border-dashed border-stone-300 dark:border-stone-700 rounded-lg p-4 md:p-6 text-center cursor-pointer hover:border-amber-500 dark:hover:border-stone-500 focus-within:border-amber-500 transition-colors">
                    <input type="file" name="image" accept="image/*" id="fileInput" class="hidden">
                    <i data-lucide="image" class="mx-auto w-8 h-8 md:w-10 md:h-10 text-stone-400 dark:text-stone-500 mb-2"></i>
                    <p class="text-xs md:text-sm text-stone-500 dark:text-stone-400">Drag & drop an image here or click to select</p>
                    <p id="fileName" class="mt-2 text-xs md:text-sm text-stone-700 dark:text-stone-300 font-medium"></p>
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
                        class="bg-gradient-to-r from-amber-500 to-orange-600 text-white px-4 md:px-6 py-2 rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all duration-200 font-medium text-sm md:text-base">
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
    
    @stack('scripts')
</body>
</html>
