@extends('layouts.app')

@section('title', 'Register - Kulturegram')

@section('content')
    <div class="max-w-md mx-auto mt-10 bg-white dark:bg-stone-900 shadow-lg rounded-2xl p-6 border dark:border-stone-800">
        <h2 class="text-2xl font-bold mb-6 text-center text-amber-500 ">Register</h2>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="mb-4 relative">
                <label for="name" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Name</label>
                <div class="mt-1 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                        <i data-lucide="circle-user-round" class="w-5 h-5"></i>
                    </span>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                        placeholder="name">
                    @error('name')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Username -->
            <div class="mb-4 relative">
                <label for="username"
                    class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Username</label>
                <div class="mt-1 relative">
                    <span class="mb-1 absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </span>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" required
                        class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                        placeholder="username">
                    @error('username')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Email -->
            <div class="mb-4 relative">
                <label for="email" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Email</label>
                <div class="mt-1 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                    </span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                        placeholder="your@email.com">
                    @error('email')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4 relative" x-data="{ show: false }">
                <label for="password"
                    class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Password</label>
                <div class="mt-1 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </span>
                    <input id="password" :type="show ? 'text' : 'password'" name="password" required
                        class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                        placeholder="Your password">
                    <button type="button" class="absolute inset-y-0 right-3 text-sm text-gray-500" @click="show = !show"
                        tabindex="-1">
                        <span x-show="!show">👁</span>
                        <span x-show="show">🙈</span>
                    </button>
                    @error('password')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-6 relative" x-data="{ show: false }">
                <label for="password_confirmation"
                    class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Confirm
                    Password</label>
                <div class="mt-1 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </span>
                    <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation"
                        required
                        class="pl-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500"
                        placeholder="confirmation password">
                    <button type="button" class="absolute inset-y-0 right-3 text-sm text-gray-500" @click="show = !show"
                        tabindex="-1">
                        <span x-show="!show">👁</span>
                        <span x-show="show">🙈</span>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit"
                class="w-full bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold py-3 rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all duration-200">
                Sign Up
            </button>

            <!-- Extra links -->
            <div class="mt-4 text-center">
                <a href="{{ route('password.request') }}" class="text-sm text-stone-500 hover:text-amber-600">Forgot your
                    password?</a>
            </div>
            <div class="mt-2 text-center">
                <p class="text-sm text-stone-600 dark:text-stone-400">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-medium text-amber-600 hover:underline">Login</a>
                </p>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
        });
    </script>
@endpush