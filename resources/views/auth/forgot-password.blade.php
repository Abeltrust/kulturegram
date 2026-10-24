@extends('layouts.app')

@section('title', 'Forgot Password - Kulturegram')

@section('content')
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-red-50 dark:from-stone-900 dark:via-stone-950 dark:to-stone-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-stone-900 p-8 rounded-2xl shadow-lg w-full max-w-md border dark:border-stone-800">
            <div class="text-center mb-6">
                <div class="w-12 h-12 bg-amber-500/10 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="key-round" class="w-6 h-6"></i>
                </div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-stone-100">Forgot Password?</h2>
                <p class="text-sm text-stone-500 dark:text-stone-400 mt-2">
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
