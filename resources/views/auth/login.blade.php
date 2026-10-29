@extends('layouts.app')

@section('title', 'Login - Kulturegram')

@section('content')
<div class="relative min-h-[calc(100vh-160px)] flex items-center justify-center py-12 px-4 overflow-hidden bg-gradient-to-br from-[#fdfbf7] via-[#faf4eb] to-[#f4e9da] dark:from-[#120d0a] dark:via-[#19120e] dark:to-[#0f0c0a] transition-colors">
    {{-- Cultural Geometric Tapestry & Ambient Sunset Glows --}}
    @include('partials.cultural-background')

    <div class="relative w-full max-w-md bg-white/95 dark:bg-[#181310]/95 backdrop-blur-xl p-8 sm:p-10 rounded-3xl shadow-2xl border border-amber-500/30 dark:border-amber-500/25 z-10 transition-all overflow-hidden">
        {{-- Traditional Cultural Geometric Ribbon at Top of Card --}}
        <!-- <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-amber-600 via-yellow-500 via-orange-500 via-amber-500 to-amber-700"></div> -->

        {{-- Cultural Brand Medallion & Logo Header --}}
        <div class="text-center mb-6 pt-1">
            <div class="relative inline-block mb-3.5">
                <div class="w-20 h-20 mx-auto rounded-2xl bg-gradient-to-br from-amber-500/20 via-orange-500/10 to-amber-600/20 border-2 border-amber-500/40 p-2 shadow-xl shadow-amber-500/20 flex items-center justify-center backdrop-blur-sm group hover:scale-105 transition-transform duration-300">
                    <img src="{{ asset('images/logo.png') }}" alt="Kulturegram Logo" class="w-full h-full object-contain rounded-xl drop-shadow-sm">
                </div>
                <!-- <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] shadow-md border-2 border-white dark:border-stone-900" title="Living Heritage">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                </div> -->
            </div>

            <!-- <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/25 mb-2.5">
                <span>Living Heritage & Storytelling</span>
            </div> -->

            <h2 class="text-2xl sm:text-3xl font-black text-amber-500 tracking-tight">
                Welcome Back
            </h2>
            <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 mt-1 leading-relaxed">
                Enter your credentials to reconnect with your culture and circle.
            </p>
        </div>

        {{-- Session Status / Flash Alert --}}
        @if (session('status'))
            <div class="mb-5 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs font-semibold flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-amber-500 shrink-0"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <label for="email" class="block text-xs sm:text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1.5">
                    Email Address
                </label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-400 group-focus-within:text-amber-500 transition-colors pointer-events-none">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500 transition focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500"
                        placeholder="your@email.com">
                </div>
                @error('email')
                    <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1 font-medium">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-5" x-data="{ show: false }">
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs sm:text-sm font-semibold text-stone-700 dark:text-stone-300">
                        Password
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs text-amber-600 dark:text-amber-400 hover:underline font-semibold transition-colors">
                            Forgot password?
                        </a>
                    @endif
                </div>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-400 group-focus-within:text-amber-500 transition-colors pointer-events-none">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input id="password" :type="show ? 'text' : 'password'" name="password" required
                        class="pl-10 pr-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500 transition focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500"
                        placeholder="Enter your password">
                    <button type="button" @click="show = !show; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); })"
                        class="absolute inset-y-0 right-3.5 flex items-center text-stone-400 hover:text-amber-600 transition"
                        tabindex="-1" aria-label="Toggle password visibility">
                        <span x-show="!show"><i data-lucide="eye" class="w-4 h-4"></i></span>
                        <span x-show="show" x-cloak><i data-lucide="eye-off" class="w-4 h-4"></i></span>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1 font-medium">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Remember Me Checkbox -->
            <div class="flex items-center justify-between mb-6">
                <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="w-4 h-4 rounded text-amber-500 focus:ring-amber-400 border-stone-300 dark:border-stone-600 dark:bg-stone-800 cursor-pointer">
                    <span class="text-xs text-stone-600 dark:text-stone-400 font-medium">Keep me signed in</span>
                </label>
            </div>

            <!-- Sign In Submit Button -->
            <button type="submit"
                class="w-full bg-gradient-to-r from-amber-500 via-amber-600 to-orange-600 hover:from-amber-600 hover:to-orange-700 active:scale-[0.99] text-white font-extrabold py-3.5 rounded-xl shadow-lg shadow-amber-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer text-sm">
                <span>Sign In to Legacy</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <!-- Switch to Registration -->
        <div class="mt-6 pt-6 border-t border-amber-500/15 dark:border-amber-500/15 text-center space-y-3">
            <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-bold text-amber-600 dark:text-amber-400 hover:underline">
                    Join Kulturegram
                </a>
            </p>

            <div class="flex items-center justify-center gap-3 text-[11px] text-stone-500 dark:text-stone-400 pt-1">
                <a href="{{ route('terms') }}" class="hover:text-amber-600 transition">Terms of Service</a>
                <span>&bull;</span>
                <a href="{{ route('privacy') }}" class="hover:text-amber-600 transition">Privacy Policy</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endpush
