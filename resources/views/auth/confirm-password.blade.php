@extends('layouts.app')

@section('title', 'Confirm Password - Kulturegram')

@section('content')
    <div class="relative min-h-[calc(100vh-160px)] flex items-center justify-center py-12 px-4 overflow-hidden bg-gradient-to-br from-[#fdfbf7] via-[#faf4eb] to-[#f4e9da] dark:from-[#120d0a] dark:via-[#19120e] dark:to-[#0f0c0a] transition-colors">
        {{-- Cultural Geometric Tapestry & Ambient Sunset Glows --}}
        @include('partials.cultural-background')

        <div class="relative w-full max-w-md bg-white/95 dark:bg-[#181310]/95 backdrop-blur-xl p-8 sm:p-10 rounded-3xl shadow-2xl border border-amber-500/30 dark:border-amber-500/25 z-10 transition-all overflow-hidden">
            {{-- Traditional Cultural Geometric Ribbon at Top of Card --}}
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-amber-600 via-yellow-500 via-orange-500 via-amber-500 to-amber-700"></div>

            {{-- Cultural Brand Medallion & Logo Header --}}
            <div class="text-center mb-6 pt-1">
                <div class="relative inline-block mb-3.5">
                    <div class="w-20 h-20 mx-auto rounded-2xl bg-gradient-to-br from-amber-500/20 via-orange-500/10 to-amber-600/20 border-2 border-amber-500/40 p-2 shadow-xl shadow-amber-500/20 flex items-center justify-center backdrop-blur-sm group hover:scale-105 transition-transform duration-300">
                        <img src="{{ asset('images/logo.png') }}" alt="Kulturegram Logo" class="w-full h-full object-contain rounded-xl drop-shadow-sm">
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] shadow-md border-2 border-white dark:border-stone-900" title="Security">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    </div>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/25 mb-2.5">
                    <span>Security Verification</span>
                </div>

                <h2 class="text-2xl sm:text-3xl font-black text-amber-500 tracking-tight">Confirm Password</h2>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 mt-1 leading-relaxed">
                    This is a secure area of Kulturegram. Please confirm your password before continuing.
                </p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input id="password" type="password" name="password" required autofocus
                            class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                            placeholder="Your password">
                    </div>
                    @error('password')
                        <p class="text-sm text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold py-3 rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all duration-200 shadow-md shadow-amber-500/20">
                    Confirm Password
                </button>
            </form>
        </div>
    </div>
@endsection
