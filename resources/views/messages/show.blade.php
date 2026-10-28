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
                $isLockedInWithOther = $otherUser ? auth()->user()->isLockedInWith($otherUser) : false;
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
                        <div class="flex items-center gap-2">
                            <h2 class="font-black text-stone-900 dark:text-stone-100 text-sm md:text-base tracking-tight leading-tight">
                                {{ $otherUser->name ?? 'Unknown' }}
                            </h2>
                            @if($isLockedInWithOther)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                    Locked-In
                                </span>
                            @endif
                        </div>
                        <span class="text-[10px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-widest truncate block mt-0.5">
                            {{ $otherUser->is_online ? 'Active Now' : ($otherUser->last_seen ? 'Seen ' . $otherUser->last_seen->diffForHumans() : 'Offline') }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-1">
                    <a href="{{ route('profile.user', $otherUser) }}" class="px-3 py-1.5 text-xs font-bold text-stone-600 dark:text-stone-300 hover:text-amber-500 dark:hover:text-amber-400 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-xl transition-colors flex items-center gap-1.5">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Profile</span>
                    </a>
                </div>
            </header>

            <!-- CHAT MESSAGES -->
            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6 custom-scrollbar bg-stone-50/50 dark:bg-stone-950/20">
                <!-- Message Group Timestamp -->
                <div class="flex justify-center my-4">
                    <span class="px-3 py-1 rounded-full bg-stone-200/50 dark:bg-stone-800/50 text-[10px] font-bold text-stone-500 dark:text-stone-400 uppercase tracking-widest backdrop-blur-sm">
                        Conversation
                    </span>
                </div>

                @forelse($messages as $msg)
                    <div data-msg-id="{{ $msg->id }}" class="flex {{ $msg->user_id == auth()->id() ? 'justify-end' : 'justify-start' }} animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <div class="flex flex-col {{ $msg->user_id == auth()->id() ? 'items-end' : 'items-start' }} max-w-[85%] md:max-w-[75%] lg:max-w-[65%]">
                            <div class="relative px-4 py-3 rounded-2xl shadow-sm text-sm leading-relaxed group
                                        {{ $msg->user_id == auth()->id() 
                                            ? 'bg-amber-500 text-white rounded-br-none shadow-amber-500/10' 
                                            : 'bg-white dark:bg-stone-800 text-stone-800 dark:text-stone-100 rounded-bl-none border border-stone-100 dark:border-stone-700 shadow-stone-100/50 dark:shadow-none' }}">
                                <p class="break-words font-medium">{{ $msg->content }}</p>
                                
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
                    <div id="empty-chat-state" class="h-full flex flex-col items-center justify-center opacity-30 text-stone-400 dark:text-stone-500 space-y-4 py-12">
                        <div class="w-20 h-20 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center">
                            <i data-lucide="sparkles" class="w-10 h-10"></i>
                        </div>
                        <p class="font-bold text-sm uppercase tracking-widest">Start the conversation</p>
                    </div>
                @endforelse
            </div>

            <!-- CHAT INPUT -->
            <div class="p-3 md:p-4 bg-white dark:bg-stone-900 border-t border-stone-100 dark:border-stone-800 sticky bottom-0 z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)]">
                @if($isLockedInWithOther)
                    <form id="chat-form" action="{{ route('messages.store', $selectedConversation->id) }}" method="POST"
                        class="flex items-end gap-2 max-w-6xl mx-auto">
                        @csrf
                        
                        <input type="file" id="chat-file-input" name="attachment[]" class="hidden" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx">

                        <div class="flex-shrink-0 flex gap-1 mb-1">
                            <button type="button" onclick="document.getElementById('chat-file-input').click()"
                                class="p-2 text-stone-400 dark:text-stone-500 hover:text-amber-500 transition-colors rounded-xl hover:bg-amber-50 dark:hover:bg-stone-800"
                                title="Attach files">
                                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <div class="flex-1 relative">
                            <textarea id="chat-textarea" name="content" rows="1" placeholder="Write a message..."
                                class="w-full bg-stone-100 dark:bg-stone-800/80 border-0 rounded-2xl px-4 py-3 pr-10 text-sm md:text-base text-stone-900 dark:text-stone-100 placeholder-stone-500 dark:placeholder-stone-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 resize-none transition-all custom-scrollbar overflow-hidden"
                                oninput="this.style.height = ''; this.style.height = Math.min(this.scrollHeight, 120) + 'px'"></textarea>
                        </div>

                        <button type="submit" id="chat-send-btn"
                            class="bg-amber-500 hover:bg-amber-600 text-white rounded-2xl p-3 shadow-lg shadow-amber-500/30 transition-all hover:scale-105 active:scale-95 flex-shrink-0 mb-1 cursor-pointer">
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </button>
                    </form>
                @else
                    <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-center space-y-2">
                        <p class="text-xs font-bold text-stone-800 dark:text-stone-200">
                            Lock-In required to interact with {{ $otherUser->name ?? 'this user' }}
                        </p>
                        <p class="text-[11px] text-stone-500">
                            Direct messages are reserved for members you have Locked-In with.
                        </p>
                        <a href="{{ route('profile.user', $otherUser) }}" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md transition">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                            <span>Lock-In to Connect</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #292524; }
        @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slide-up { from { transform: translateY(10px); } to { transform: translateY(0); } }
        .animate-fade-in { animation: fade-in 0.3s ease-out; }
        .animate-slide-up { animation: slide-up 0.3s ease-out; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const chatMessages = document.getElementById('chat-messages');
            const chatForm = document.getElementById('chat-form');
            const textarea = document.getElementById('chat-textarea');
            const sendBtn = document.getElementById('chat-send-btn');
            const conversationId = @json($selectedConversation->id);
            const updatesUrl = @json(route('messages.updates', $selectedConversation->id));

            function scrollToBottom(smooth = false) {
                if (!chatMessages) return;
                if (smooth) {
                    chatMessages.scrollTo({ top: chatMessages.scrollHeight, behavior: 'smooth' });
                } else {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }

            scrollToBottom();

            // Track highest message ID on page
            let maxMsgId = 0;
            document.querySelectorAll('[data-msg-id]').forEach(el => {
                const id = parseInt(el.getAttribute('data-msg-id'), 10);
                if (id > maxMsgId) maxMsgId = id;
            });

            function escapeHtml(str) {
                return (str || '').replace(/[&<>"']/g, function (m) {
                    switch (m) {
                        case '&': return '&amp;';
                        case '<': return '&lt;';
                        case '>': return '&gt;';
                        case '"': return '&quot;';
                        case "'": return '&#39;';
                        default: return m;
                    }
                });
            }

            function appendMessage(msg) {
                const emptyState = document.getElementById('empty-chat-state');
                if (emptyState) emptyState.remove();

                const isMe = msg.is_me;
                const wrapper = document.createElement('div');
                wrapper.setAttribute('data-msg-id', msg.id);
                wrapper.className = `flex ${isMe ? 'justify-end' : 'justify-start'} animate-in fade-in slide-in-from-bottom-2 duration-300`;

                wrapper.innerHTML = `
                    <div class="flex flex-col ${isMe ? 'items-end' : 'items-start'} max-w-[85%] md:max-w-[75%] lg:max-w-[65%]">
                        <div class="relative px-4 py-3 rounded-2xl shadow-sm text-sm leading-relaxed group
                            ${isMe 
                                ? 'bg-amber-500 text-white rounded-br-none shadow-amber-500/10' 
                                : 'bg-white dark:bg-stone-800 text-stone-800 dark:text-stone-100 rounded-bl-none border border-stone-100 dark:border-stone-700 shadow-stone-100/50 dark:shadow-none'}">
                            <p class="break-words font-medium">${escapeHtml(msg.content)}</p>
                            <div class="absolute invisible group-hover:visible -top-8 bg-stone-900 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50">
                                ${msg.full_time || msg.time}
                            </div>
                        </div>
                        <div class="mt-1.5 flex items-center gap-2 px-1">
                            <span class="text-[9px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-tighter">
                                ${msg.time}
                            </span>
                            ${isMe ? '<div class="flex items-center"><i data-lucide="check" class="w-3 h-3 text-amber-500"></i></div>' : ''}
                        </div>
                    </div>
                `;

                chatMessages.appendChild(wrapper);
                if (msg.id > maxMsgId) maxMsgId = msg.id;
                if (typeof lucide !== 'undefined') lucide.createIcons();
                scrollToBottom(true);
            }

            // AJAX message submit (no page reload)
            async function sendMessage() {
                if (!textarea) return;
                const text = (textarea.value || '').trim();
                const fileInput = document.getElementById('chat-file-input');
                const hasFiles = fileInput && fileInput.files && fileInput.files.length > 0;

                if (!text && !hasFiles) return;

                const formData = new FormData(chatForm);

                // Clear textarea immediately for great responsiveness
                textarea.value = '';
                textarea.style.height = '';
                if (fileInput) fileInput.value = '';

                try {
                    const response = await fetch(chatForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data && data.message) {
                            appendMessage(data.message);
                        }
                    } else {
                        const err = await response.json().catch(() => null);
                        const errMsg = err?.error || err?.message || 'Failed to send message. Please try again.';
                        if (typeof window.showPlatformError === 'function') {
                            window.showPlatformError(errMsg, 'Message Restriction');
                        } else {
                            alert(errMsg);
                        }
                    }
                } catch (e) {
                    console.error('Message send error:', e);
                } finally {
                    textarea.focus();
                }
            }

            if (chatForm) {
                chatForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    sendMessage();
                });
            }

            if (textarea) {
                textarea.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });
            }

            // Background polling for real-time conversation updates without refresh
            async function pollNewMessages() {
                if (document.hidden) return; // Save bandwidth when tab inactive
                try {
                    const response = await fetch(`${updatesUrl}?after_id=${maxMsgId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });
                    if (response.ok) {
                        const data = await response.json();
                        if (data && data.messages && data.messages.length > 0) {
                            data.messages.forEach(msg => {
                                if (!document.querySelector(`[data-msg-id="${msg.id}"]`)) {
                                    appendMessage(msg);
                                }
                            });
                        }
                    }
                } catch (e) {
                    // Silently ignore network blips
                }
            }

            setInterval(pollNewMessages, 3500);
        });
    </script>
@endsection
