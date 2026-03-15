@extends('layouts.app')

@section('content')
    <div class="flex h-[calc(100vh-4rem)] bg-stone-50 dark:bg-stone-950 overflow-hidden font-sans w-full max-w-full box-border">

        <!-- CONVERSATION LIST (Hidden on mobile when conversation is selected, but this is the index so it's always visible) -->
        <div class="w-full md:w-80 lg:w-96 flex-shrink-0 border-r border-stone-100 dark:border-stone-800 h-full overflow-hidden block">
            @include('messages.partials._list')
        </div>

        <!-- PLACEHOLDER (Hidden on mobile) -->
        <div class="hidden md:flex flex-1 flex-col items-center justify-center bg-white/50 dark:bg-stone-900/50 backdrop-blur-sm relative overflow-hidden">
            <!-- Decorative Background -->
            <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05] pointer-events-none">
                <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            </div>

            <div class="relative z-10 flex flex-col items-center text-center p-8 max-w-md">
                <div class="w-24 h-24 mb-6 rounded-3xl bg-amber-100 dark:bg-amber-900/20 flex items-center justify-center text-amber-500 shadow-xl shadow-amber-500/10 animate-pulse">
                    <i data-lucide="messages-square" class="w-12 h-12"></i>
                </div>
                <h2 class="text-2xl font-black text-stone-900 dark:text-stone-100 mb-2 tracking-tight">Your Messages</h2>
                <p class="text-stone-500 dark:text-stone-400 font-medium leading-relaxed">
                    Select a conversation from the sidebar to start chatting or create a new message.
                </p>
                <button class="mt-8 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-2xl shadow-lg shadow-amber-500/30 transition-all hover:scale-105 active:scale-95 flex items-center gap-2">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    New Conversation
                </button>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #292524;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
        });
    </script>
@endsection
