@if(session('registered_flyer'))
@php
    $flyer = session('registered_flyer');
    $profileUrl = url('/@' . ($flyer['username'] ?? ''));
    $shareText = "Hello, I'm from " . ($flyer['location'] ?? 'Nigeria') . "! This is my culture, follow me on Kulturegram! " . $profileUrl;
@endphp
<div x-data="welcomeFlyerModal(@js([
        'shareText' => $shareText,
        'profileUrl' => $profileUrl,
        'userName' => $flyer['name'] ?? 'Cultural Preserver',
        'flyerUrl' => $flyer['flyer_url'] ?? null,
    ]))" 
    x-show="open" 
    x-cloak 
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-md animate-backdrop-fade"
    @keydown.escape.window="open = false">

    <div class="relative w-full max-w-md bg-white dark:bg-stone-900 rounded-3xl shadow-2xl border border-stone-200 dark:border-stone-800 overflow-hidden animate-modal-pop-up max-h-[95vh] flex flex-col"
        @click.outside="open = false">
        
        {{-- Modal Top Bar --}}
        <div class="flex items-center justify-between px-5 pt-4 pb-2 border-b border-stone-100 dark:border-stone-800 shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg overflow-hidden border border-amber-500/30 bg-amber-500/10 p-0.5 shadow-sm">
                    <img src="{{ asset('images/logo.png') }}" alt="Kulturegram" class="w-full h-full object-contain rounded-md">
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300">
                    Welcome to Kulturegram
                </span>
            </div>
            <button @click="open = false" 
                class="p-1.5 rounded-full text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800 transition"
                title="Close">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Scrollable Container for Flyer Card --}}
        <div class="p-4 sm:p-5 overflow-y-auto space-y-4 flex-1">
            
            {{-- THE CULTURAL FLYER CARD (Target for canvas/sharing) --}}
            <div id="cultural-flyer-card" 
                class="relative bg-white dark:bg-stone-950 rounded-2xl border-2 border-amber-500/40 shadow-xl overflow-hidden p-6 text-center space-y-4">
                
                {{-- Decorative Cultural Header Strip --}}
                <div class="absolute top-0 left-0 right-0 h-3 bg-amber-500"></div>

                {{-- Stamp / Badge --}}
                <div class="pt-2 flex justify-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20">
                        <i data-lucide="award" class="w-3.5 h-3.5 text-amber-500"></i>
                        Official Cultural Passport
                    </span>
                </div>

                {{-- Avatar Photo Frame --}}
                <div class="flex justify-center pt-1">
                    <div class="relative">
                        <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-full overflow-hidden border-4 border-amber-500 shadow-lg bg-amber-50 dark:bg-stone-800 mx-auto">
                            <img src="{{ $flyer['avatar_url'] }}" alt="{{ $flyer['name'] }}" class="w-full h-full object-cover">
                        </div>
                        <span class="absolute bottom-1 right-1 text-2xl filter drop-shadow">
                            {{ $flyer['country_flag'] ?? '🌍' }}
                        </span>
                    </div>
                </div>

                {{-- Identity --}}
                <div class="space-y-0.5">
                    <h3 class="text-xl sm:text-2xl font-black text-stone-900 dark:text-white leading-tight">
                        {{ $flyer['name'] }}
                    </h3>
                    <p class="text-xs sm:text-sm font-semibold text-amber-700 dark:text-amber-400">
                        {{ '@' . ($flyer['username'] ?? 'kulturegram') }}
                    </p>
                </div>

                {{-- The User's Cultural Declaration --}}
                <div class="px-4 py-3 rounded-2xl bg-amber-500/10 border border-amber-500/20">
                    <p class="text-sm sm:text-base font-extrabold text-stone-900 dark:text-stone-100 leading-snug">
                        &ldquo;Hello, I'm from <span class="text-amber-600 dark:text-amber-400">{{ $flyer['location'] }}</span>! This is my culture, follow me on Kulturegram!&rdquo;
                    </p>
                </div>

                {{-- Origin Badges --}}
                <div class="flex flex-wrap items-center justify-center gap-1.5 pt-1 text-xs font-semibold">
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>{{ $flyer['location'] }}</span>
                    </span>

                    @if(!empty($flyer['languages']))
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300">
                            <i data-lucide="languages" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>{{ implode(', ', $flyer['languages']) }}</span>
                        </span>
                    @endif
                </div>

                {{-- Footer Watermark --}}
                <div class="pt-3 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between text-[10px] text-stone-600 dark:text-stone-400 font-bold uppercase tracking-widest">
                    <span>kulturegram.com</span>
                    <span>Living Heritage</span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="space-y-2 pt-1">
                {{-- Download Flyer Button --}}
                <button type="button" @click="downloadFlyer()"
                    class="w-full py-3 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition active:scale-[0.99] cursor-pointer shadow-md">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span x-text="downloading ? 'Preparing Image...' : 'Download Cultural Flyer'"></span>
                </button>

                {{-- Copy / Native Share Button --}}
                <button type="button" @click="copyShare()"
                    class="w-full py-3 px-4 rounded-xl bg-stone-900 dark:bg-stone-100 hover:bg-stone-800 dark:hover:bg-white text-white dark:text-stone-900 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition active:scale-[0.99] cursor-pointer shadow-sm">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                    <span x-text="copied ? '✓ Copied Invitation!' : 'Share Invitation & Link'"></span>
                </button>

                {{-- Continue to Timeline --}}
                <button type="button" @click="open = false"
                    class="w-full py-2.5 px-4 rounded-xl text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition">
                    <span>Continue to Timeline</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>

        </div>

    </div>
</div>

<script>
    function welcomeFlyerModal(config) {
        return {
            open: true,
            copied: false,
            downloading: false,
            async copyShare() {
                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: 'Welcome to Kulturegram!',
                            text: config.shareText,
                            url: config.profileUrl
                        });
                        return;
                    } catch (e) {
                        // User cancelled or share failed, fallback to clipboard
                    }
                }
                try {
                    await navigator.clipboard.writeText(config.shareText);
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 3000);
                } catch (e) {
                    const el = document.createElement('textarea');
                    el.value = config.shareText;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 3000);
                }
            },
            async downloadFlyer() {
                this.downloading = true;
                if (config.flyerUrl) {
                    const a = document.createElement('a');
                    a.href = config.flyerUrl;
                    a.download = (config.userName || 'kulturegram').toLowerCase().replace(/\s+/g, '_') + '_cultural_flyer.svg';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    this.downloading = false;
                    return;
                }

                const card = document.getElementById('cultural-flyer-card');
                if (!card) {
                    this.downloading = false;
                    return;
                }

                // If html2canvas is not loaded, dynamically inject it
                if (typeof html2canvas === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                    script.onload = () => { this.captureCard(card); };
                    document.head.appendChild(script);
                } else {
                    this.captureCard(card);
                }
            },
            captureCard(card) {
                html2canvas(card, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: null
                }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = (config.userName || 'kulturegram').toLowerCase().replace(/\s+/g, '_') + '_culture_flyer.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    this.downloading = false;
                }).catch(err => {
                    console.error('Error rendering flyer canvas:', err);
                    this.downloading = false;
                    alert('Unable to generate flyer image directly. You can take a screenshot or copy the invite text!');
                });
            }
        };
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endif
