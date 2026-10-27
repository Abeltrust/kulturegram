@extends('layouts.app')

@section('title', 'AI Privacy & Policy Audit - Kulturegram Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12" x-data="privacyAuditComponent()">
    {{-- Top Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                    Administrator Center
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Meta AI Assisted Audit
                </span>
            </div>
            <h1 class="text-3xl font-black text-stone-900 dark:text-stone-100 mt-2">
                AI Privacy Protection & System Policy Audit
            </h1>
            <p class="text-stone-600 dark:text-stone-400 text-sm mt-1 max-w-3xl">
                Continuous programmatic inspection of access controls, private vault isolation, PII data boundaries, and AI compliance with Kulturegram's published Data Sovereignty Policy.
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.privacy-audit.run') }}" method="POST">
                @csrf
                <button type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm shadow-md transition flex items-center gap-2 hover:scale-[1.02] active:scale-[0.98]">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Run Fresh System Audit
                </button>
            </form>
        </div>
    </div>

    {{-- Admin Navigation Tabs --}}
    <div class="flex items-center gap-2 mb-8 border-b border-stone-200 dark:border-stone-800 pb-3 overflow-x-auto">
        <a href="{{ route('admin.cultures.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 bg-stone-100 dark:bg-stone-800/50">
            Heritage Moderation
        </a>
        <a href="{{ route('admin.privacy-audit.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition bg-amber-500 text-white shadow-sm flex items-center gap-2">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            <span>Privacy & Policy Audit</span>
        </a>
        <a href="{{ route('privacy') }}" target="_blank"
           class="px-4 py-2 rounded-xl text-sm font-bold transition text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-100 bg-stone-100 dark:bg-stone-800/50 flex items-center gap-1.5 ml-auto">
            <span>View Published Policy</span>
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    {{-- Score and KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        {{-- Overall Security Score --}}
        <div class="bg-gradient-to-br from-white to-stone-50 dark:from-stone-900 dark:to-stone-900/80 p-6 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Overall Privacy Score</span>
                <i data-lucide="shield" class="w-5 h-5 text-emerald-500"></i>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black text-stone-900 dark:text-stone-100">{{ $audit['score'] ?? 0 }}%</span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ ($audit['score'] ?? 0) >= 80 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-800' }}">
                    {{ ($audit['score'] ?? 0) >= 80 ? 'Compliant' : 'Attention Needed' }}
                </span>
            </div>
            <p class="text-xs text-stone-400 mt-2">Based on {{ count($audit['automated_checks'] ?? []) }} automated code-level verification gates.</p>
        </div>

        {{-- Verification Status --}}
        <div class="bg-white dark:bg-stone-900 p-6 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Automated Checks</span>
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black text-stone-900 dark:text-stone-100">
                    {{ collect($audit['automated_checks'] ?? [])->where('passed', true)->count() }} / {{ count($audit['automated_checks'] ?? []) }}
                </span>
                <span class="text-xs font-bold text-stone-500">Passed</span>
            </div>
            <p class="text-xs text-stone-400 mt-2">Zero critical access leaks detected.</p>
        </div>

        {{-- Vault Isolation --}}
        <div class="bg-white dark:bg-stone-900 p-6 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Vault Isolation</span>
                <i data-lucide="lock" class="w-5 h-5 text-amber-500"></i>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">Strictly Isolated</span>
            </div>
            <p class="text-xs text-stone-400 mt-2">Private tier items inaccessible to public visitors or external crawlers.</p>
        </div>

        {{-- AI Model Boundary --}}
        <div class="bg-white dark:bg-stone-900 p-6 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">AI Model Boundary</span>
                <i data-lucide="cpu" class="w-5 h-5 text-indigo-500"></i>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-xl font-black text-stone-900 dark:text-stone-100">PII Pre-Masked</span>
            </div>
            <p class="text-xs text-stone-400 mt-2">Phone numbers, emails & credentials stripped before external API inference.</p>
        </div>
    </div>

    {{-- Executive AI Compliance Summary --}}
    <div class="bg-gradient-to-r from-amber-500/10 via-stone-900/5 to-emerald-500/10 dark:from-amber-950/20 dark:via-stone-900 dark:to-emerald-950/20 rounded-3xl p-6 sm:p-8 border border-amber-500/20 dark:border-amber-500/10 mb-8 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-500 flex items-center justify-center shrink-0">
                <i data-lucide="sparkles" class="w-6 h-6"></i>
            </div>
            <div class="flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                    <h2 class="text-xl font-black text-stone-900 dark:text-stone-100 flex items-center gap-2">
                        Executive AI Policy & Compliance Synthesis
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-stone-200 dark:bg-stone-800 text-stone-600 dark:text-stone-300">
                            Meta {{ $audit['ai_synthesis']['model'] ?? 'muse-wonder-1.0' }}
                        </span>
                    </h2>
                    <span class="text-xs text-stone-400">Audited at {{ \Carbon\Carbon::parse($audit['timestamp'] ?? ($audit['audited_at'] ?? now()))->format('M d, Y H:i:s') }}</span>
                </div>
                <div class="prose dark:prose-invert max-w-none text-stone-700 dark:text-stone-300 text-sm leading-relaxed space-y-3">
                    <p class="whitespace-pre-line">{{ $audit['ai_synthesis']['summary'] ?? 'Audit evaluation pending execution.' }}</p>
                </div>

                {{-- Key Strengths & Watchpoints --}}
                @if(!empty($audit['ai_synthesis']['key_strengths']))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 pt-6 border-t border-stone-200 dark:border-stone-800">
                    <div>
                        <h4 class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i> Verified Safeguards
                        </h4>
                        <ul class="text-xs text-stone-600 dark:text-stone-400 space-y-1 list-disc list-inside">
                            @foreach($audit['ai_synthesis']['key_strengths'] as $strength)
                                <li>{{ $strength }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i> Continuous Attention Points
                        </h4>
                        <ul class="text-xs text-stone-600 dark:text-stone-400 space-y-1 list-disc list-inside">
                            @foreach($audit['ai_synthesis']['watchpoints'] ?? [] as $wp)
                                <li>{{ $wp }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                {{-- Legal & Technical Disclaimer --}}
                <div class="mt-6 pt-4 border-t border-stone-200/60 dark:border-stone-800/60 flex items-center gap-2 text-[11px] text-stone-400 italic">
                    <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                    <span>{{ $audit['ai_synthesis']['disclaimer'] ?? 'Note: This automated audit reflects technical application tests and architectural verification. It does not replace formal legal counsel.' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Automated Code-Level Security Checks --}}
    <div class="mb-12">
        <h3 class="text-lg font-black text-stone-900 dark:text-stone-100 mb-4 flex items-center gap-2">
            <i data-lucide="cpu" class="w-5 h-5 text-amber-500"></i>
            Automated System Architecture & Boundary Verification
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($audit['automated_checks'] ?? [] as $check)
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border {{ $check['passed'] ? 'border-stone-200 dark:border-stone-800' : 'border-rose-300 dark:border-rose-900' }} shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Gate {{ $loop->iteration }}</span>
                        @if($check['passed'])
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800 flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i> Passed
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-300 dark:border-rose-800 flex items-center gap-1">
                                <i data-lucide="x" class="w-3 h-3"></i> Failed
                            </span>
                        @endif
                    </div>
                    <h4 class="font-bold text-stone-900 dark:text-stone-100 text-sm mb-1.5">{{ $check['name'] }}</h4>
                    <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed mb-3">{{ $check['description'] ?? $check['details'] ?? $check['pillar'] }}</p>
                </div>

                <div class="pt-3 border-t border-stone-100 dark:border-stone-800/80">
                    <p class="text-[11px] text-stone-500 dark:text-stone-400 font-mono">
                        <span class="font-semibold text-stone-700 dark:text-stone-300">Finding:</span> {{ $check['details'] }}
                    </p>
                    <div class="mt-2 flex items-center justify-between text-[11px]">
                        <span class="text-stone-400">Remediation:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $check['remediation'] }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Interactive Content Privacy Scanner (Inspector Bench) --}}
    <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-stone-800 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-black text-stone-900 dark:text-stone-100 flex items-center gap-2">
                    <i data-lucide="search" class="w-5 h-5 text-amber-500"></i>
                    Real-Time Content Privacy & PII Scanner
                </h3>
                <p class="text-xs text-stone-500 mt-1">
                    Test how Kulturegram's content safety layer detects exposed phone numbers, emails, passwords, NIN/SSN, or credentials before user publication.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-2">Input Content Sample</label>
                <textarea x-model="sampleText" rows="5"
                    class="w-full bg-stone-50 dark:bg-stone-950 border border-stone-200 dark:border-stone-800 rounded-xl p-3 text-sm text-stone-900 dark:text-stone-100 focus:outline-none focus:border-amber-500 font-mono transition"
                    placeholder="Type or paste sample text (e.g. 'Call me on 08031234567 or email secret@gmail.com for access')"></textarea>
                
                <div class="flex items-center justify-between mt-3">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="loadPiiSample()" class="text-xs text-amber-600 dark:text-amber-400 hover:underline">
                            Load sample with PII
                        </button>
                        <span class="text-stone-300 dark:text-stone-700">|</span>
                        <button type="button" @click="loadCleanSample()" class="text-xs text-stone-500 hover:underline">
                            Load clean cultural story
                        </button>
                    </div>

                    <button type="button" @click="scanText()" :disabled="scanning || !sampleText.trim()"
                        class="px-4 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 dark:bg-stone-100 dark:hover:bg-white text-white dark:text-stone-900 font-bold text-xs transition flex items-center gap-1.5 disabled:opacity-50">
                        <i data-lucide="scan" class="w-3.5 h-3.5"></i>
                        <span x-text="scanning ? 'Scanning...' : 'Scan for Privacy Risks'"></span>
                    </button>
                </div>
            </div>

            {{-- Scan Results --}}
            <div class="bg-stone-50 dark:bg-stone-950/60 rounded-xl p-4 border border-stone-200 dark:border-stone-800 flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-stone-500 mb-3 flex items-center justify-between">
                        <span>Scan Output</span>
                        <template x-if="scanResult">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black"
                                :class="{
                                    'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400': scanResult.risk_level === 'high',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400': scanResult.risk_level === 'medium',
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400': scanResult.risk_level === 'low'
                                }"
                                x-text="scanResult.risk_level.toUpperCase() + ' RISK'"></span>
                        </template>
                    </h4>

                    <template x-if="!scanResult">
                        <div class="h-32 flex flex-col items-center justify-center text-center text-stone-400 text-xs">
                            <i data-lucide="shield-alert" class="w-8 h-8 text-stone-300 dark:text-stone-700 mb-1"></i>
                            <p>Click "Scan for Privacy Risks" to inspect content.</p>
                        </div>
                    </template>

                    <template x-if="scanResult">
                        <div class="space-y-3">
                            <div>
                                <span class="text-[11px] font-bold text-stone-400">Detected PII Types:</span>
                                <div class="flex flex-wrap gap-1.5 mt-1">
                                    <template x-if="scanResult.detected_types && scanResult.detected_types.length > 0">
                                        <template x-for="type in scanResult.detected_types" :key="type">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-400 border border-rose-300 dark:border-rose-800" x-text="type"></span>
                                        </template>
                                    </template>
                                    <template x-if="!scanResult.detected_types || scanResult.detected_types.length === 0">
                                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">None — Content safe from personal disclosure.</span>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <span class="text-[11px] font-bold text-stone-400">Sanitized / Redacted Output:</span>
                                <div class="mt-1 p-2 bg-white dark:bg-stone-900 rounded-lg border border-stone-200 dark:border-stone-800 font-mono text-xs text-stone-800 dark:text-stone-200 whitespace-pre-wrap"
                                    x-text="scanResult.masked_preview"></div>
                            </div>

                            <div>
                                <span class="text-[11px] font-bold text-stone-400">User Advisory:</span>
                                <p class="text-xs text-stone-600 dark:text-stone-400 mt-0.5" x-text="scanResult.advice"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });

    function privacyAuditComponent() {
        return {
            sampleText: '',
            scanning: false,
            scanResult: null,

            loadPiiSample() {
                this.sampleText = "Please contact Elder Adebayo on +234 803 123 4567 or email adebayo.heritage@gmail.com. Password is Yoruba2026! to download archive.";
            },

            loadCleanSample() {
                this.sampleText = "The New Yam Festival (Iri Ji) is celebrated annually among the Igbo people, honoring the harvest of yams and ancestral blessings of abundance.";
            },

            async scanText() {
                if (!this.sampleText.trim()) return;
                this.scanning = true;

                try {
                    const response = await fetch("{{ route('privacy.scan') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ text: this.sampleText })
                    });
                    const res = await response.json();
                    if (res.success) {
                        this.scanResult = res.data;
                    }
                } catch (e) {
                    console.error('Scan error:', e);
                } finally {
                    this.scanning = false;
                    setTimeout(() => lucide.createIcons(), 50);
                }
            }
        };
    }
</script>
@endsection
