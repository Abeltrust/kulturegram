@extends('layouts.app')

@section('title', 'Verify Email - Kulturegram')

@section('content')
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-red-50 dark:from-stone-900 dark:via-stone-950 dark:to-stone-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-stone-900 p-8 rounded-2xl shadow-lg w-full max-w-md border dark:border-stone-800">
            <div class="text-center mb-6">
                <div class="w-12 h-12 bg-amber-500/10 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="mail-check" class="w-6 h-6"></i>
                </div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-stone-100">Verify Your Email</h2>
                <p class="text-sm text-stone-500 dark:text-stone-400 mt-2">
                    Thanks for joining Kulturegram! Before getting started, could you verify your email address by clicking the link we just sent to you?
                </p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-5 p-4 rounded-xl bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 text-sm font-medium text-green-800 dark:text-green-300">
                    A new verification link has been sent to the email address you provided during registration.
                </div>
            @endif

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit"
                        class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold rounded-xl hover:from-amber-600 hover:to-orange-700 transition shadow-sm">
                        Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto text-center">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-stone-500 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200 underline">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
