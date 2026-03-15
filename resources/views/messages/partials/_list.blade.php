<!-- INBOX COLUMN -->
<div class="flex flex-col bg-white dark:bg-stone-900 h-full w-full border-r border-stone-100 dark:border-stone-800 shadow-sm overflow-hidden box-border">

    <!-- HEADER -->
    <div class="p-4 flex items-center justify-between border-b border-stone-100 dark:border-stone-800 bg-white/80 dark:bg-stone-900/80 backdrop-blur-md sticky top-0 z-20">
        <h1 class="text-xl font-black text-stone-900 dark:text-stone-100 tracking-tight">
            Messages
        </h1>
        <div class="p-2 bg-amber-50 dark:bg-amber-900/20 rounded-xl text-amber-600 dark:text-amber-400 cursor-pointer hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-all hover:scale-105 active:scale-95">
            <i data-lucide="message-square-plus" class="w-5 h-5"></i>
        </div>
    </div>

    <!-- SEARCH -->
    <div class="p-3 border-b border-stone-50 dark:border-stone-800/50">
        <div class="relative group">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-stone-500 group-focus-within:text-amber-500 transition-colors"></i>
            <input type="text" placeholder="Search conversations..."
                class="w-full pl-10 pr-4 py-2 bg-stone-50 dark:bg-stone-800/50 dark:text-stone-100 dark:placeholder-stone-500 border-0 rounded-2xl text-sm focus:ring-2 focus:ring-amber-500/50 transition-all box-border">
        </div>
    </div>

    <!-- USER LIST -->
    <div class="flex-1 overflow-y-auto space-y-0.5 p-2 custom-scrollbar box-border">
        <div class="px-3 py-2 text-[10px] font-bold text-stone-400 dark:text-stone-500 uppercase tracking-[0.2em]">
            Recent Chats
        </div>

        @forelse($activeUsers as $user)
            @php
                $isActive = isset($selectedConversation) && $selectedConversation->participants->contains('id', $user->id);
            @endphp
            <a href="{{ route('messages.start', $user->id) }}"
                class="group flex items-center gap-3 p-3 rounded-2xl transition-all duration-300 {{ $isActive ? 'bg-amber-500 shadow-lg shadow-amber-500/20' : 'hover:bg-amber-50 dark:hover:bg-stone-800/50' }} min-w-0">

                <div class="relative flex-shrink-0">
                    <img src="{{ $user->profile_photo_url }}"
                        class="w-12 h-12 rounded-2xl object-cover shadow-sm group-hover:scale-110 transition-transform duration-300 {{ $isActive ? 'ring-2 ring-white/50' : '' }}">
                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 {{ $isActive ? 'border-amber-500' : 'border-white dark:border-stone-900' }} 
                                            {{ $user->status == 'online' ? 'bg-green-500' : 'bg-stone-300 dark:bg-stone-700' }}">
                    </span>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-baseline mb-0.5">
                        <h3 class="font-bold truncate text-sm {{ $isActive ? 'text-white' : 'text-stone-900 dark:text-stone-100' }}">
                            {{ $user->name }}
                        </h3>
                        <span class="text-[10px] font-semibold uppercase tracking-tighter truncate {{ $isActive ? 'text-white/80' : 'text-stone-400 dark:text-stone-500' }}">
                            {{ $user->last_seen ? $user->last_seen->diffForHumans(null, true) : '' }}
                        </span>
                    </div>
                    <p class="text-xs truncate leading-snug font-medium max-w-[90%] {{ $isActive ? 'text-white/90' : 'text-stone-500 dark:text-stone-400' }}">
                        {{ $user->last_message ? Str::limit($user->last_message, 40) : 'Start a new conversation' }}
                    </p>
                </div>

                @if($user->last_status && !$isActive)
                    <div class="flex-shrink-0">
                        @if($user->last_status == 'sent')
                            <i data-lucide="check" class="w-3.5 h-3.5 text-stone-300 dark:text-stone-600"></i>
                        @elseif($user->last_status == 'read' || $user->last_status == 'delivered')
                            <i data-lucide="check-check"
                                class="w-3.5 h-3.5 {{ $user->last_status == 'read' ? 'text-amber-500 dark:text-amber-400' : 'text-stone-300 dark:text-stone-600' }}"></i>
                        @endif
                    </div>
                @endif
            </a>
        @empty
            <div class="h-40 flex flex-col items-center justify-center opacity-40 text-stone-400 dark:text-stone-500 p-8 space-y-3">
                <i data-lucide="ghost" class="w-10 h-10"></i>
                <p class="font-bold text-xs uppercase tracking-widest">No chats yet</p>
            </div>
        @endforelse
    </div>
</div>
