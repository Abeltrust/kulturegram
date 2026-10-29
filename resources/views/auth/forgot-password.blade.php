@extends('layouts.app')

@section('title', 'Forgot Password - Kulturegram')

@section('content')
    <div class="relative min-h-[calc(100vh-160px)] flex items-center justify-center py-12 px-4 overflow-hidden bg-stone-50 dark:bg-stone-900 transition-colors">
        {{-- Subtle Cultural Geometric Tapestry --}}
        @include('partials.cultural-background')

        <div class="relative w-full max-w-md bg-white dark:bg-stone-900 p-8 sm:p-10 rounded-3xl shadow-xl border border-stone-200 dark:border-stone-800 z-10 transition-all">
            {{-- Cultural Brand Medallion & Logo Header --}}
            <div class="text-center mb-6 pt-1">
                <div class="relative inline-block mb-3.5">
                    <div class="w-20 h-20 mx-auto rounded-2xl bg-amber-500/10 dark:bg-stone-800 border border-amber-500/30 p-2 shadow-sm flex items-center justify-center group hover:scale-105 transition-transform duration-300">
                        <img src="{{ asset('images/logo.png') }}" alt="Kulturegram Logo" class="w-full h-full object-contain rounded-xl">
                    </div>
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
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl transition-all shadow-md">
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
