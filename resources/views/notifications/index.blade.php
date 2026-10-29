@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- HEADER -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h1 class="text-lg sm:text-xl font-bold page-title text-amber-500">Notifications</h1>
            <button onclick="markAllAsRead()" class="text-sm sm:text-base text-amber-500 hover:underline">
                Mark all as read
            </button>
        </div>

        <!-- FILTER TABS -->
        <div class="flex flex-wrap gap-2 sm:gap-3 mb-6">
            @php
                $filters = [
                    'all' => 'All',
                    'unread' => 'Unread',
                    'tap' => 'TAPs',
                    'lock-in' => 'Lock-Ins',
                    'resonance' => 'Resonance',
                    'mention' => 'Mentions'
                ];
            @endphp

            @foreach($filters as $key => $label)
                <a href="{{ route('notifications.index', ['filter' => $key]) }}"
                    class="px-3 sm:px-4 py-1.5 rounded-full text-xs sm:text-sm font-medium transition-colors
                                {{ $filter === $key ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-stone-800 text-gray-600 dark:text-stone-400 hover:bg-gray-200 dark:hover:bg-stone-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- NOTIFICATION LIST -->
        <div class="space-y-3">
            @forelse($notifications as $notification)
                <div
                    class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 p-4 bg-white dark:bg-stone-900 rounded-xl border border-stone-100 dark:border-stone-800 shadow-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition">

                    <!-- LEFT SIDE -->
                    <div class="flex items-start gap-3 min-w-0">
                        <!-- AVATAR / ICON -->
                        @if($notification->fromUser)
                            <a href="{{ $notification->fromUser->profile_url }}" class="shrink-0 relative block group" title="View {{ $notification->fromUser->name }}'s profile">
                                <img src="{{ $notification->fromUser->profile_photo_url }}" 
                                     alt="{{ $notification->fromUser->name }}"
                                     class="w-10 h-10 rounded-full object-cover border border-stone-200 dark:border-stone-700 group-hover:ring-2 group-hover:ring-amber-500/50 transition">
                                @if($notification->type === 'mention')
                                    <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-blue-500 text-white rounded-full flex items-center justify-center text-[9px] font-bold shadow-sm">@</span>
                                @elseif($notification->type === 'tap')
                                    <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-red-500 text-white rounded-full flex items-center justify-center text-[9px] shadow-sm"><i data-lucide="heart" class="w-2.5 h-2.5 fill-current"></i></span>
                                @elseif($notification->type === 'resonance' || $notification->type === 'resonance_reply')
                                    <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-purple-500 text-white rounded-full flex items-center justify-center text-[9px] shadow-sm"><i data-lucide="message-circle" class="w-2.5 h-2.5"></i></span>
                                @elseif($notification->type === 'lockin')
                                    <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-amber-500 text-white rounded-full flex items-center justify-center text-[9px] shadow-sm"><i data-lucide="shield-check" class="w-2.5 h-2.5"></i></span>
                                @endif
                            </a>
                        @else
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 relative {{ $notification->read ? 'bg-gray-200 dark:bg-stone-800 text-gray-500 dark:text-stone-500' : 'bg-amber-500 text-white' }}">
                                <span class="font-bold text-sm">
                                    <i data-lucide="bell" class="w-4 h-4"></i>
                                </span>
                            </div>
                        @endif

                        <!-- MESSAGE -->
                        <div class="min-w-0">
                            <p class="text-sm sm:text-base text-gray-800 dark:text-stone-200 leading-snug">
                                {!! $notification->formatted_message !!}
                            </p>
                            @if(isset($notification->data['post_id']))
                                <a href="{{ route('timeline.index') }}#post-{{ $notification->data['post_id'] }}" class="inline-block mt-1 text-xs text-amber-600 dark:text-amber-400 font-semibold hover:underline">
                                    View Story &rarr;
                                </a>
                            @endif
                            <p class="text-xs text-stone-500 dark:text-stone-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div class="flex flex-row sm:flex-col items-center sm:items-end gap-2">
                        @if(!$notification->read)
                            <button onclick="markAsRead('{{ $notification->id }}')" class="text-xs text-amber-500 hover:underline">
                                Mark as read
                            </button>
                        @endif
                        <button onclick="deleteNotification('{{ $notification->id }}')"
                            class="text-xs text-red-500 hover:underline">
                            Dismiss
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-stone-500 dark:text-stone-500 text-center">No notifications found.</p>
            @endforelse
        </div>

        <!-- PAGINATION -->
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    </div>

    <script>
        function markAsRead(id) {
            fetch(`/notifications/${id}/read`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(() => location.reload());
        }

        function markAllAsRead() {
            fetch(`/notifications/read-all`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(() => location.reload());
        }

        function deleteNotification(id) {
            fetch(`/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(() => location.reload());
        }
    </script>
@endsection
