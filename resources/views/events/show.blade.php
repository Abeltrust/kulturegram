@extends('layouts.app')

@section('title', $event->title . ' - Kulturegram Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

    {{-- Back Link --}}
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('events.index') }}" 
       class="inline-flex items-center text-sm font-semibold text-stone-500 hover:text-amber-600 transition mb-6">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back
    </a>

    {{-- Main Card --}}
    <div class="bg-white dark:bg-stone-900 rounded-3xl overflow-hidden shadow-xl border border-stone-100 dark:border-stone-800">
        
        {{-- Hero Image --}}
        @if($event->image)
            <div class="w-full min-h-[320px] max-h-[620px] relative bg-stone-950 flex items-center justify-center overflow-hidden">
                <div class="absolute inset-0 bg-cover bg-center filter blur-2xl opacity-35 scale-110 pointer-events-none"
                     style="background-image: url('{{ asset('storage/' . $event->image) }}');"></div>
                <img src="{{ asset('storage/' . $event->image) }}" class="relative z-10 w-auto h-auto max-w-full max-h-[580px] object-contain mx-auto" alt="{{ $event->title }}">
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-transparent to-black/30 z-10 pointer-events-none"></div>
                
                {{-- Floating Badges --}}
                <div class="absolute top-4 left-4 flex gap-2 z-20">
                    <span class="bg-white/90 dark:bg-stone-900/90 backdrop-blur-md px-3 py-1 rounded-full text-xs font-black uppercase text-stone-800 dark:text-stone-100 shadow-sm border border-stone-200 dark:border-stone-700">
                        {{ str_replace('_', ' ', ucfirst($event->type)) }}
                    </span>
                    @if($event->is_online)
                        <span class="bg-blue-500/90 backdrop-blur-md px-3 py-1 rounded-full text-xs font-black uppercase text-white shadow-sm border border-blue-400">
                            Online Event
                        </span>
                    @endif
                </div>

                {{-- Download Flyer Action --}}
                <div class="absolute top-4 right-4 z-20">
                    <a href="{{ asset('storage/' . $event->image) }}"
                       download="{{ \Illuminate\Support\Str::slug($event->title ?: 'event-flyer') }}-flyer.{{ pathinfo($event->image, PATHINFO_EXTENSION) ?: 'png' }}"
                       class="px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-stone-950 text-xs font-black shadow-lg transition flex items-center gap-1.5">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Download Flyer</span>
                    </a>
                </div>

                <div class="absolute bottom-6 left-6 right-6 z-20">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-white leading-tight mb-2 drop-shadow-md">
                        {{ $event->title }}
                    </h1>
                </div>
            </div>
        @else
            <div class="p-6 sm:p-8 border-b border-stone-100 dark:border-stone-800 pb-0">
                <div class="flex gap-2 mb-4">
                    <span class="bg-stone-100 dark:bg-stone-800 px-3 py-1 rounded-full text-xs font-black uppercase text-stone-600 dark:text-stone-300 border border-stone-200 dark:border-stone-700">
                        {{ ucfirst($event->type) }}
                    </span>
                    @if($event->is_online)
                        <span class="bg-blue-50 dark:bg-blue-900/20 px-3 py-1 rounded-full text-xs font-black uppercase text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                            Online Event
                        </span>
                    @endif
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-stone-900 dark:text-white leading-tight mb-6 mt-2">
                    {{ $event->title }}
                </h1>
            </div>
        @endif

        <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12">
            
            {{-- Left Column: Details --}}
            <div class="md:col-span-2 space-y-8">
                
                {{-- Organizer Info --}}
                <div class="flex items-center gap-4 bg-stone-50 dark:bg-stone-800/50 p-4 rounded-2xl border border-stone-100 dark:border-stone-800">
                    <img src="{{ $event->organizer->profile_photo_url ?? asset('images/default-avatar.png') }}" alt="{{ $event->organizer->name }}" class="w-14 h-14 rounded-full object-cover">
                    <div>
                        <p class="text-xs uppercase font-bold text-stone-500 dark:text-stone-400 tracking-wider mb-0.5">Organized by</p>
                        <p class="font-bold text-stone-900 dark:text-stone-100 text-lg">{{ $event->organizer->name }}</p>
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <h3 class="text-xl font-bold text-stone-900 dark:text-stone-100 mb-4 flex items-center gap-2">
                        <i data-lucide="info" class="w-5 h-5 text-amber-500"></i> About this Event
                    </h3>
                    <div class="prose prose-stone dark:prose-invert max-w-none text-stone-700 dark:text-stone-300 whitespace-pre-line leading-relaxed">
                        {{ $event->description }}
                    </div>
                </div>
                
                {{-- If it belongs to a community --}}
                @if($event->community_id)
                <div class="pt-6 border-t border-stone-100 dark:border-stone-800">
                    <h3 class="text-lg font-bold text-stone-900 dark:text-stone-100 mb-4 flex items-center gap-2">
                        <i data-lucide="users" class="w-5 h-5 text-amber-500"></i> Community Hosted
                    </h3>
                    <a href="{{ route('communities.show', $event->community_id) }}" class="flex items-center gap-4 group p-4 rounded-xl hover:bg-stone-50 dark:hover:bg-stone-800 transition border border-transparent hover:border-stone-200 dark:hover:border-stone-700">
                        <div class="w-12 h-12 rounded-lg bg-stone-200 dark:bg-stone-700 flex items-center justify-center overflow-hidden">
                            @if($event->community->image)
                                <img src="{{ asset('storage/' . $event->community->image) }}" class="w-full h-full object-cover">
                            @else
                                <i data-lucide="users" class="w-6 h-6 text-stone-400"></i>
                            @endif
                        </div>
                        <div>
                            <p class="font-bold text-stone-900 dark:text-stone-100 group-hover:text-amber-600 transition">{{ $event->community->name }}</p>
                            <p class="text-sm text-stone-500">View Community</p>
                        </div>
                    </a>
                </div>
                @endif

                {{-- Hosted Content Section --}}
                @if($event->is_hosted)
                <div class="pt-8 border-t-4 border-amber-100 dark:border-amber-900/30">
                    <h3 class="text-2xl font-black text-stone-900 dark:text-stone-100 mb-6 flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                            <i data-lucide="play" class="w-6 h-6 fill-current"></i>
                        </div>
                        Class Content
                    </h3>

                    @if(auth()->check() && (auth()->id() == $event->organizer_id || $event->attendees->contains(auth()->id())))
                        <div class="bg-stone-50 dark:bg-stone-800/40 rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-stone-700 shadow-inner overflow-hidden">
                            <div class="prose prose-stone dark:prose-invert max-w-none prose-img:rounded-2xl prose-video:rounded-2xl">
                                {!! $event->hosted_content !!}
                            </div>
                        </div>
                    @else
                        <div class="bg-gradient-to-br from-stone-100 to-stone-50 dark:from-stone-800 dark:to-stone-900 p-10 rounded-3xl border-2 border-dashed border-stone-200 dark:border-stone-700 text-center">
                            <i data-lucide="lock" class="w-12 h-12 mx-auto mb-4 text-stone-300 dark:text-stone-600"></i>
                            <h4 class="text-xl font-black text-stone-800 dark:text-stone-200 mb-2">Exclusive Content</h4>
                            <p class="text-stone-500 dark:text-stone-400 mb-6 max-w-sm mx-auto">Register or purchase a ticket to access the hosted videos, lessons, and resources for this class.</p>
                            
                            @if(!auth()->check())
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-stone-900 dark:bg-white text-white dark:text-stone-900 px-6 py-3 rounded-xl font-bold transition hover:scale-105">
                                    Login to Access
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Right Column: Sidebar (Date, Time, Location, Registration) --}}
            <div class="space-y-6">
                
                {{-- Quick Info Card --}}
                <div class="bg-stone-50 dark:bg-stone-800/80 rounded-2xl p-6 border border-stone-100 dark:border-stone-700 space-y-6">
                    
                    {{-- Date & Time --}}
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white dark:bg-stone-900 shadow-sm flex items-center justify-center text-amber-500 shrink-0">
                            <i data-lucide="calendar" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="font-bold text-stone-900 dark:text-stone-100">{{ $event->event_date->format('l, F j, Y') }}</p>
                            <p class="text-stone-600 dark:text-stone-400 text-sm mt-0.5">{{ $event->event_time->format('g:i A') }}</p>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white dark:bg-stone-900 shadow-sm flex items-center justify-center text-orange-500 shrink-0">
                            <i data-lucide="{{ $event->is_online ? 'video' : 'map-pin' }}" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="font-bold text-stone-900 dark:text-stone-100">{{ $event->is_online ? 'Online Event' : 'Location' }}</p>
                            <p class="text-stone-600 dark:text-stone-400 text-sm mt-0.5">{{ $event->location }}</p>
                            
                            @if($event->virtual_room_id && (auth()->check() && (auth()->id() == $event->organizer_id || $event->attendees->contains(auth()->id()))))
                                <a href="{{ route('events.meet', $event) }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                                    <i data-lucide="video" class="w-4 h-4"></i> Enter Classroom
                                </a>
                            @elseif($event->is_online && $event->meeting_link && (auth()->check() && (auth()->id() == $event->organizer_id || $event->attendees->contains(auth()->id()))))
                                <a href="{{ $event->meeting_link }}" target="_blank" class="inline-block mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline font-semibold flex items-center gap-1">
                                    Join Meeting <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Price --}}
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white dark:bg-stone-900 shadow-sm flex items-center justify-center text-green-500 shrink-0">
                            <i data-lucide="tag" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="font-bold text-stone-900 dark:text-stone-100">Price</p>
                            <p class="text-stone-600 dark:text-stone-400 text-sm mt-0.5">
                                {{ $event->price == 0 ? 'Free' : '$' . number_format($event->price, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Action Area --}}
                <div class="space-y-3 pt-2">
                    @auth
                        @if($event->organizer_id == auth()->id())
                            <div class="bg-stone-100 dark:bg-stone-800 p-4 rounded-xl text-center space-y-3">
                                <div>
                                    <p class="text-xs uppercase font-black text-stone-400 tracking-widest mb-1">Host Controls</p>
                                    <p class="text-stone-600 dark:text-stone-300 font-bold">You are the Organizer</p>
                                </div>
                                <a href="{{ route('events.edit', $event) }}" class="inline-flex items-center justify-center gap-2 w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl transition shadow-sm">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i> Edit Event Details
                                </a>
                            </div>
                        @elseif($event->attendees->contains(auth()->id()))
                            <div class="text-center">
                                <div class="w-full py-4 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800 font-bold rounded-xl mb-3 flex items-center justify-center gap-2">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i> 
                                    {{ $event->price > 0 ? 'Ticket Active' : "You're Attending" }}
                                </div>
                                <form action="{{ route('events.leave', $event) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel? Refunds are handled by the host.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-stone-500 hover:text-red-500 transition underline underline-offset-2">Cancel Registration</button>
                                </form>
                            </div>
                        @else
                            @if($event->isFull())
                                <button disabled class="w-full py-4 bg-stone-100 dark:bg-stone-800 text-stone-500 dark:text-stone-400 font-bold rounded-xl cursor-not-allowed text-center uppercase tracking-wider text-sm flex items-center justify-center gap-2">
                                    <i data-lucide="slash" class="w-4 h-4"></i> Event is Full
                                </button>
                            @else
                                <form action="{{ $event->price > 0 ? route('events.ticket', $event) : route('events.join', $event) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white shadow-lg shadow-amber-500/30 transition-all font-black rounded-xl text-center uppercase tracking-widest active:scale-95 flex items-center justify-center gap-2">
                                        {{ $event->price > 0 ? 'Buy Ticket' : 'Register Now' }} 
                                        <i data-lucide="{{ $event->price > 0 ? 'credit-card' : 'arrow-right' }}" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @if($event->price > 0)
                                    <p class="text-[10px] text-center text-stone-400 font-bold uppercase tracking-tight mt-2">Secure Ticketing Powered by Kulturegram Education</p>
                                @endif
                            @endif
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block w-full py-4 bg-stone-800 dark:bg-stone-100 hover:bg-stone-900 dark:hover:bg-white text-white dark:text-stone-900 transition-all font-bold rounded-xl text-center uppercase tracking-widest text-sm text-nowrap">
                            Log in to Join
                        </a>
                    @endauth

                    <p class="text-center text-xs font-semibold text-stone-500 dark:text-stone-400 mt-4 flex items-center justify-center gap-1.5">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        {{ $event->attendees_count }} {{ Str::plural('person', $event->attendees_count) }} attending 
                        @if($event->max_attendees)
                            (Max: {{ $event->max_attendees }})
                        @endif
                    </p>
                </div>

                {{-- Contribution Section --}}
                @if($event->accepts_contributions)
                <div class="mt-8 bg-amber-50 dark:bg-amber-900/10 rounded-2xl p-6 border border-amber-100 dark:border-amber-900/30">
                    <h4 class="text-sm font-black text-amber-800 dark:text-amber-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i data-lucide="heart" class="w-4 h-4"></i> Support Educator
                    </h4>
                    <p class="text-xs text-amber-900/60 dark:text-amber-500/60 mb-4 leading-relaxed">
                        The organizer is open to financial contributions to support this work. Every bit helps preserve our traditions.
                    </p>
                    
                    <form action="{{ route('events.contribute', $event) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-amber-600 dark:text-amber-500 font-bold">$</span>
                            <input type="number" name="amount" min="1" step="1" required
                                class="w-full bg-white dark:bg-stone-800 border-amber-200 dark:border-amber-900/50 rounded-xl pl-7 pr-4 py-2 text-sm focus:ring-amber-500 focus:border-amber-500 dark:text-stone-100"
                                placeholder="Amount">
                        </div>
                        <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-black text-xs uppercase tracking-widest rounded-xl transition shadow-sm">
                            Send Contribution
                        </button>
                    </form>
                </div>
                @endif
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        if(typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush
