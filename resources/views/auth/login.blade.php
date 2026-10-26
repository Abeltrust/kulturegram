@extends('layouts.app')

@section('title', 'Login - Kulturegram')

@section('content')
    <div class="flex items-center justify-center min-h-screen bg-stone-50 dark:bg-stone-950 py-12 px-4">
        <div class="bg-white dark:bg-stone-900 p-8 rounded-2xl shadow-lg w-full max-w-md border border-stone-200 dark:border-stone-800">
            <h2 class="text-2xl font-bold text-center mb-6 text-amber-500">Welcome Back</h2>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-stone-700 dark:text-stone-300">Email</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                            placeholder="your@email.com">
                    </div>
                    @error('email')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password"
                        class="block text-sm font-medium text-stone-700 dark:text-stone-300">Password</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input id="password" type="password" name="password" required
                            class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                            placeholder="Your password">
                    </div>
                    @error('password')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit (Solid Brand Color, No Gradients) -->
                <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl transition-colors duration-200 shadow-sm active:scale-[0.99] cursor-pointer">
                    Sign In
                </button>
            </form>

            <!-- Links -->
            <div class="mt-4 text-center">
                <a href="{{ route('password.request') }}" class="text-sm text-stone-500 hover:text-amber-600 transition-colors">Forgot your
                    password?</a>
            </div>
            <div class="mt-2 text-center">
                <p class="text-sm text-stone-600 dark:text-stone-400">
                    Don’t have an account?
                    <a href="{{ route('register') }}" class="font-medium text-amber-600 hover:underline">Join Kulturegram</a>
                </p>
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