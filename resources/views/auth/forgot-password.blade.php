@extends('layouts.app')

@section('title', 'Forgot Password - Kulturegram')

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
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] shadow-md border-2 border-white dark:border-stone-900" title="Living Heritage">
                        <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                    </div>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/25 mb-2.5">
                    <span>Account Security</span>
                </div>

                <h2 class="text-2xl sm:text-3xl font-black text-amber-500 tracking-tight">Forgot Password?</h2>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 mt-1 leading-relaxed">
                    No problem. Enter your email address and we'll send you a password reset link to choose a new one.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-4 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-sm font-medium text-amber-800 dark:text-amber-300">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email -->
                <div class="mb-5">
                    <label for="email" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                            placeholder="your@email.com">
                    </div>
                    @error('email')
                        <p class="text-sm text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold py-3 rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all duration-200 shadow-md shadow-amber-500/20">
                    Email Password Reset Link
                </button>
            </form>

            <div class="mt-6 text-center border-t border-stone-200 dark:border-stone-800 pt-4">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-600 dark:text-stone-400 hover:text-amber-600">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to Sign In
                </a>
            </div>
        </div>
    </div>
@endsection
