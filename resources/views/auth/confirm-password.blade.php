@extends('layouts.app')

@section('title', 'Confirm Password - Kulturegram')

@section('content')
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-red-50 dark:from-stone-900 dark:via-stone-950 dark:to-stone-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-stone-900 p-8 rounded-2xl shadow-lg w-full max-w-md border dark:border-stone-800">
            <div class="text-center mb-6">
                <div class="w-12 h-12 bg-amber-500/10 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-stone-100">Confirm Password</h2>
                <p class="text-sm text-stone-500 dark:text-stone-400 mt-2">
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
