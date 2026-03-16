@extends('layouts.app')

@section('title', 'Timeline Meet - ' . $event->title)

@section('content')
<div class="fixed inset-0 z-[60] bg-stone-950 flex flex-col pt-16 md:ml-20">
    <div class="flex items-center justify-between px-6 py-4 border-b border-stone-800 bg-stone-900/50 backdrop-blur-md">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                <i data-lucide="video" class="w-6 h-6"></i>
            </div>
            <div>
                <h2 class="text-white font-black text-lg leading-tight uppercase tracking-tight">{{ $event->title }}</h2>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></span>
                    <p class="text-stone-400 text-xs font-bold uppercase tracking-widest">Live Virtual Classroom</p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-stone-800 rounded-lg border border-stone-700">
                <i data-lucide="users" class="w-4 h-4 text-stone-400"></i>
                <span class="text-stone-300 text-xs font-bold uppercase tracking-widest" id="participant-count">Initializing...</span>
            </div>
            <a href="{{ route('events.show', $event) }}" class="px-5 py-2.5 bg-stone-800 hover:bg-stone-700 text-stone-300 font-bold text-sm uppercase tracking-widest rounded-xl transition-all border border-stone-700">
                Leave Room
            </a>
        </div>
    </div>

    <div id="timeline-meet-container" class="flex-1 w-full bg-stone-950"></div>
</div>

@push('scripts')
<script src='https://meet.jit.si/external_api.js'></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const domain = "meet.jit.si";
        const options = {
            roomName: "{{ $event->virtual_room_id }}",
            width: '100%',
            height: '100%',
            parentNode: document.querySelector('#timeline-meet-container'),
            userInfo: {
                displayName: "{{ auth()->user()->name }}",
                email: "{{ auth()->user()->email }}"
            },
            configOverwrite: {
                startWithAudioMuted: true,
                startWithVideoMuted: true,
                prejoinPageEnabled: false,
                disableInviteFunctions: true,
                whiteboard: {
                    enabled: true
                }
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                    'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                    'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone',
                    'security'
                ],
                BRAND_WATERMARK_LINK: '{{ url("/") }}',
                SHOW_JITSI_WATERMARK: false,
                DEFAULT_REMOTE_DISPLAY_NAME: 'Attendee',
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
        
        // Listen for participants
        api.addEventListener('participantJoined', (participant) => {
            updateParticipantCount(api.getParticipantsInfo().length + 1);
        });
        
        api.addEventListener('participantLeft', (participant) => {
             updateParticipantCount(api.getParticipantsInfo().length + 1);
        });

        function updateParticipantCount(count) {
            document.getElementById('participant-count').innerText = `${count} Participants`;
        }
        
        // Initial count
        setTimeout(() => {
             updateParticipantCount(api.getParticipantsInfo().length + 1);
        }, 3000);
    });
</script>
<style>
    /* Hide the default layout footer and extra space when in meeting */
    body { overflow: hidden; }
</style>
@endpush
@endsection
