@extends('layouts.app')

@section('content')
    <div class="flex h-[calc(100vh-4rem)] bg-stone-50 dark:bg-stone-950 overflow-hidden font-sans w-full max-w-full box-border">

        <!-- CONVERSATION LIST (Hidden on mobile when conversation is open) -->
        <div class="hidden md:block w-80 lg:w-96 flex-shrink-0 border-r border-stone-100 dark:border-stone-800 h-full overflow-hidden">
            @include('messages.partials._list')
        </div>

        <!-- ACTIVE CHAT AREA -->
        <div class="flex-1 flex flex-col bg-white dark:bg-stone-900 h-full relative overflow-hidden">
            @php
                $otherUser = $selectedConversation->participants
                    ->where('id', '!=', auth()->id())
                    ->first();
            @endphp

            <!-- CHAT HEADER -->
            <header class="flex items-center justify-between p-3 md:p-4 border-b border-stone-100 dark:border-stone-800 bg-white/90 dark:bg-stone-900/90 backdrop-blur-xl sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-3">
                    <a href="{{ route('messages.index') }}"
                        class="md:hidden p-2 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-xl transition-colors">
                        <i data-lucide="chevron-left" class="w-5 h-5 text-stone-600 dark:text-stone-400"></i>
                    </a>

                    <div class="relative group cursor-pointer">
                        <img src="{{ $otherUser->profile_photo_url }}"
                            class="w-10 h-10 rounded-2xl object-cover shadow-md group-hover:scale-110 transition-transform duration-300">
                        <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-stone-900
                                {{ $otherUser->is_online ? 'bg-green-500' : 'bg-stone-400 dark:bg-stone-600' }}"></span>
                    </div>

                    <div class="truncate">
                        <h2 class="font-black text-stone-900 dark:text-stone-100 text-sm md:text-base tracking-tight leading-tight">
                            {{ $otherUser->name ?? 'Unknown' }}</h2>
                        <span class="text-[10px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-widest truncate block mt-0.5">
                            {{ $otherUser->is_online ? 'Active Now' : ($otherUser->last_seen ? 'Seen ' . $otherUser->last_seen->diffForHumans() : 'Offline') }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-1">
                    <button class="p-2.5 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-xl text-amber-600 dark:text-amber-400 transition-all active:scale-95 group">
                        <i data-lucide="phone" class="w-4 h-4 group-hover:rotate-12 transition-transform"></i>
                    </button>
                    <button class="p-2.5 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-xl text-amber-600 dark:text-amber-400 transition-all active:scale-95 group">
                        <i data-lucide="video" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                    </button>
                    <button class="p-2.5 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-xl text-stone-400 dark:text-stone-500 transition-all">
                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                    </button>
                </div>
            </header>

            <!-- CHAT MESSAGES -->
            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6 custom-scrollbar bg-stone-50/50 dark:bg-stone-950/20">
                <!-- Message Group Timestamp -->
                <div class="flex justify-center my-4">
                    <span class="px-3 py-1 rounded-full bg-stone-200/50 dark:bg-stone-800/50 text-[10px] font-bold text-stone-500 dark:text-stone-400 uppercase tracking-widest backdrop-blur-sm">
                        Today
                    </span>
                </div>

                @forelse($messages as $msg)
                    <div class="flex {{ $msg->user_id == auth()->id() ? 'justify-end' : 'justify-start' }} animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <div class="flex flex-col {{ $msg->user_id == auth()->id() ? 'items-end' : 'items-start' }} max-w-[85%] md:max-w-[75%] lg:max-w-[65%]">
                            <div class="relative px-4 py-3 rounded-2xl shadow-sm text-sm leading-relaxed group
                                        {{ $msg->user_id == auth()->id() 
                                            ? 'bg-amber-500 text-white rounded-br-none shadow-amber-500/10' 
                                            : 'bg-white dark:bg-stone-800 text-stone-800 dark:text-stone-100 rounded-bl-none border border-stone-100 dark:border-stone-700 shadow-stone-100/50 dark:shadow-none' }}">
                                <p class="break-words font-medium">{{ $msg->content }}</p>
                                
                                <!-- Tooltip/Metadata placeholder -->
                                <div class="absolute invisible group-hover:visible -top-8 bg-stone-900 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50">
                                    {{ $msg->created_at->format('M d, H:i') }}
                                </div>
                            </div>
                            
                            <div class="mt-1.5 flex items-center gap-2 px-1">
                                <span class="text-[9px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-tighter">
                                    {{ $msg->created_at->format('H:i') }}
                                </span>
                                @if($msg->user_id == auth()->id())
                                    <div class="flex items-center">
                                        @if($msg->status == 'sent')
                                            <i data-lucide="check" class="w-3 h-3 text-stone-300 dark:text-stone-600"></i>
                                        @elseif($msg->status == 'read')
                                            <i data-lucide="check-check" class="w-3 h-3 text-amber-500"></i>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center opacity-30 text-stone-400 dark:text-stone-500 space-y-4 py-12">
                        <div class="w-20 h-20 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center">
                            <i data-lucide="sparkles" class="w-10 h-10"></i>
                        </div>
                        <p class="font-bold text-sm uppercase tracking-widest">Start the conversation</p>
                    </div>
                @endforelse
            </div>

            <!-- CHAT INPUT -->
            <div class="p-3 md:p-4 bg-white dark:bg-stone-900 border-t border-stone-100 dark:border-stone-800 sticky bottom-0 z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)]">
                <form id="chat-form" action="{{ route('messages.store', $selectedConversation->id) }}" method="POST"
                    class="flex items-end gap-2 max-w-6xl mx-auto">
                    @csrf
                    <div class="flex-shrink-0 flex gap-1 mb-1">
                        <button type="button" class="p-2 text-stone-400 dark:text-stone-500 hover:text-amber-500 transition-colors rounded-xl hover:bg-amber-50 dark:hover:bg-stone-800">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        </button>
                        <button type="button" class="hidden sm:block p-2 text-stone-400 dark:text-stone-500 hover:text-amber-500 transition-colors rounded-xl hover:bg-amber-50 dark:hover:bg-stone-800">
                            <i data-lucide="image" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="flex-1 relative">
                        <textarea name="content" rows="1" placeholder="Write a message..."
                            class="w-full bg-stone-100 dark:bg-stone-800/80 border-0 rounded-2xl px-4 py-3 pr-10 text-sm md:text-base text-stone-900 dark:text-stone-100 placeholder-stone-500 dark:placeholder-stone-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 resize-none transition-all custom-scrollbar overflow-hidden"
                            oninput="this.style.height = ''; this.style.height = Math.min(this.scrollHeight, 120) + 'px'"></textarea>
                        <button type="button" class="absolute right-3 bottom-3 text-stone-400 dark:text-stone-500 hover:text-amber-500 transition-colors">
                            <i data-lucide="smile" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-white rounded-2xl p-3 shadow-lg shadow-amber-500/30 transition-all hover:scale-105 active:scale-95 flex-shrink-0 mb-1">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
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

        @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slide-up { from { transform: translateY(10px); } to { transform: translateY(0); } }
        .animate-fade-in { animation: fade-in 0.3s ease-out; }
        .animate-slide-up { animation: slide-up 0.3s ease-out; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
            const chatMessages = document.getElementById('chat-messages');
            if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;

            // Handle auto-expanding textarea
            const textarea = document.querySelector('textarea[name="content"]');
            if (textarea) {
                textarea.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.closest('form').submit();
                    }
                });
            }
        });
    </script>
@endsection
