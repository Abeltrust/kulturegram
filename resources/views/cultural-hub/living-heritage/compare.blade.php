@extends('layouts.app')

@section('title', 'Meta Model Comparison Lab - Kulturegram')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
     x-data="modelComparisonLab({
        csrfToken: '{{ csrf_token() }}',
        initialStory: `{{ addslashes($sampleStory) }}`
     })">

    <!-- Header Section -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-6 sm:p-10 shadow-sm space-y-3">
        <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-amber-600">
            <i data-lucide="git-compare" class="w-4 h-4"></i>
            <span>Living Heritage • Architecture & Inference Lab</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 dark:text-stone-100 tracking-tight">
            Cultural AI Model Comparison Lab
        </h1>
        <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed max-w-3xl">
            Side-by-side comparative inference benchmarking hosted Meta Model API capabilities against independent open-weight model endpoints. Never fabricates outputs; faithfully reports latency and provider status.
        </p>
    </div>

    <!-- Source Story Input Card -->
    <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 dark:text-stone-400">
                Source Cultural Story Narrative
            </label>
            <span class="text-[11px] text-stone-400">Min 20 words</span>
        </div>
        <textarea x-model="storyText"
                  rows="4"
                  class="w-full stnd-input p-3.5 text-xs font-sans leading-relaxed"></textarea>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
            <div class="text-xs text-stone-500">
                <span>Compares:</span>
                <strong class="text-stone-700 dark:text-stone-300">Muse Spark (Hosted)</strong> vs
                <strong class="text-stone-700 dark:text-stone-300">Muse Glimmer (Open)</strong> vs
                <strong class="text-stone-700 dark:text-stone-300">Muse Spark Open</strong>
            </div>

            <button type="button"
                    @click="executeComparison()"
                    :disabled="isLoading || !storyText.trim()"
                    class="px-6 py-3 bg-amber-500 hover:bg-amber-600 disabled:opacity-40 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-colors inline-flex items-center space-x-2">
                <template x-if="isLoading">
                    <span class="inline-flex items-center space-x-2">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        <span>Benchmarking Models...</span>
                    </span>
                </template>
                <template x-if="!isLoading">
                    <span class="inline-flex items-center space-x-2">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        <span>Run Comparative Benchmark</span>
                    </span>
                </template>
            </button>
        </div>
    </div>

    <!-- Error Banner -->
    <div x-show="errorMessage" x-cloak class="p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-xs text-red-700 dark:text-red-300">
        <span x-text="errorMessage"></span>
    </div>

    <!-- Comparison Results Grid (3 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- CARD 1: MUSE SPARK (HOSTED) -->
        <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-5 flex flex-col justify-between space-y-4 shadow-sm">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400">
                        Hosted Meta API
                    </span>
                    <span class="text-[11px] font-mono text-stone-400"
                          x-text="results?.muse_spark?.latency_ms ? results.muse_spark.latency_ms + 'ms' : '--'"></span>
                </div>

                <div>
                    <h3 class="text-base font-bold text-stone-900 dark:text-stone-100">Muse Spark</h3>
                    <p class="text-[11px] text-stone-500">Shared Meta Model API Client</p>
                </div>

                <!-- Status Badge -->
                <div class="text-xs">
                    <template x-if="results?.muse_spark?.success">
                        <span class="inline-flex items-center text-green-600 font-bold">
                            <i data-lucide="check" class="w-3.5 h-3.5 mr-1"></i> Inference Succeeded
                        </span>
                    </template>
                    <template x-if="results?.muse_spark && !results.muse_spark.success">
                        <span class="inline-flex items-center text-amber-600 font-bold">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 mr-1"></i>
                            <span x-text="results.muse_spark.configured ? 'Access Pending' : 'Unconfigured Key'"></span>
                        </span>
                    </template>
                    <template x-if="!results">
                        <span class="text-stone-400 italic">Ready to run</span>
                    </template>
                </div>

                <!-- Output Area -->
                <div class="p-3 bg-stone-50 dark:bg-stone-800/40 rounded-xl border border-stone-100 dark:border-stone-800 min-h-[160px] text-xs font-mono overflow-auto max-h-72">
                    <template x-if="results?.muse_spark?.output">
                        <pre class="whitespace-pre-wrap text-[11px] text-stone-700 dark:text-stone-300" x-text="JSON.stringify(results.muse_spark.output, null, 2)"></pre>
                    </template>
                    <template x-if="results?.muse_spark?.error">
                        <p class="text-stone-500 dark:text-stone-400 italic text-[11px]" x-text="results.muse_spark.error"></p>
                    </template>
                    <template x-if="!results">
                        <p class="text-stone-400 italic text-[11px]">Click 'Run Comparative Benchmark' above to inspect structured output.</p>
                    </template>
                </div>
            </div>

            <div class="pt-2 border-t border-stone-100 dark:border-stone-800 text-[10px] text-stone-400">
                Single Credential: <code>META_MODEL_API_KEY</code>
            </div>
        </div>

        <!-- CARD 2: MUSE GLIMMER (OPEN-WEIGHT) -->
        <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-5 flex flex-col justify-between space-y-4 shadow-sm">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300">
                        Open-Weight Provider
                    </span>
                    <span class="text-[11px] font-mono text-stone-400"
                          x-text="results?.muse_glimmer?.latency_ms ? results.muse_glimmer.latency_ms + 'ms' : '--'"></span>
                </div>

                <div>
                    <h3 class="text-base font-bold text-stone-900 dark:text-stone-100">Muse Glimmer</h3>
                    <p class="text-[11px] text-stone-500">Taxonomy & Claim Verification</p>
                </div>

                <!-- Status Badge -->
                <div class="text-xs">
                    <template x-if="results?.muse_glimmer?.success">
                        <span class="inline-flex items-center text-green-600 font-bold">
                            <i data-lucide="check" class="w-3.5 h-3.5 mr-1"></i> Inference Succeeded
                        </span>
                    </template>
                    <template x-if="results?.muse_glimmer && !results.muse_glimmer.success">
                        <span class="inline-flex items-center text-stone-500 font-semibold">
                            <i data-lucide="cloud-off" class="w-3.5 h-3.5 mr-1"></i>
                            <span x-text="results.muse_glimmer.configured ? 'Provider Error' : 'Endpoint Not Configured'"></span>
                        </span>
                    </template>
                    <template x-if="!results">
                        <span class="text-stone-400 italic">Ready to run</span>
                    </template>
                </div>

                <!-- Output Area -->
                <div class="p-3 bg-stone-50 dark:bg-stone-800/40 rounded-xl border border-stone-100 dark:border-stone-800 min-h-[160px] text-xs font-mono overflow-auto max-h-72">
                    <template x-if="results?.muse_glimmer?.output && (results.muse_glimmer.output.tags?.length || results.muse_glimmer.output.claim_flags?.length)">
                        <pre class="whitespace-pre-wrap text-[11px] text-stone-700 dark:text-stone-300" x-text="JSON.stringify(results.muse_glimmer.output, null, 2)"></pre>
                    </template>
                    <template x-if="results?.muse_glimmer?.error">
                        <p class="text-stone-500 dark:text-stone-400 italic text-[11px]" x-text="results.muse_glimmer.error"></p>
                    </template>
                    <template x-if="!results">
                        <p class="text-stone-400 italic text-[11px]">Reports archive taxonomy and factual verification checks.</p>
                    </template>
                </div>
            </div>

            <div class="pt-2 border-t border-stone-100 dark:border-stone-800 text-[10px] text-stone-400">
                Endpoint: <code>META_GLIMMER_ENDPOINT</code>
            </div>
        </div>

        <!-- CARD 3: MUSE SPARK OPEN (OPEN-WEIGHT) -->
        <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-2xl p-5 flex flex-col justify-between space-y-4 shadow-sm">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300">
                        Open-Weight Provider
                    </span>
                    <span class="text-[11px] font-mono text-stone-400"
                          x-text="results?.muse_spark_open?.latency_ms ? results.muse_spark_open.latency_ms + 'ms' : '--'"></span>
                </div>

                <div>
                    <h3 class="text-base font-bold text-stone-900 dark:text-stone-100">Muse Spark Open</h3>
                    <p class="text-[11px] text-stone-500">Open-Weight Structured Synthesis</p>
                </div>

                <!-- Status Badge -->
                <div class="text-xs">
                    <template x-if="results?.muse_spark_open?.success">
                        <span class="inline-flex items-center text-green-600 font-bold">
                            <i data-lucide="check" class="w-3.5 h-3.5 mr-1"></i> Inference Succeeded
                        </span>
                    </template>
                    <template x-if="results?.muse_spark_open && !results.muse_spark_open.success">
                        <span class="inline-flex items-center text-stone-500 font-semibold">
                            <i data-lucide="cloud-off" class="w-3.5 h-3.5 mr-1"></i>
                            <span x-text="results.muse_spark_open.configured ? 'Provider Error' : 'Endpoint Not Configured'"></span>
                        </span>
                    </template>
                    <template x-if="!results">
                        <span class="text-stone-400 italic">Ready to run</span>
                    </template>
                </div>

                <!-- Output Area -->
                <div class="p-3 bg-stone-50 dark:bg-stone-800/40 rounded-xl border border-stone-100 dark:border-stone-800 min-h-[160px] text-xs font-mono overflow-auto max-h-72">
                    <template x-if="results?.muse_spark_open?.output">
                        <pre class="whitespace-pre-wrap text-[11px] text-stone-700 dark:text-stone-300" x-text="JSON.stringify(results.muse_spark_open.output, null, 2)"></pre>
                    </template>
                    <template x-if="results?.muse_spark_open?.error">
                        <p class="text-stone-500 dark:text-stone-400 italic text-[11px]" x-text="results.muse_spark_open.error"></p>
                    </template>
                    <template x-if="!results">
                        <p class="text-stone-400 italic text-[11px]">Benchmarked against local or hosted vLLM/Ollama endpoints.</p>
                    </template>
                </div>
            </div>

            <div class="pt-2 border-t border-stone-100 dark:border-stone-800 text-[10px] text-stone-400">
                Endpoint: <code>META_SPARK_OPEN_ENDPOINT</code>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function modelComparisonLab(config) {
    return {
        csrfToken: config.csrfToken,
        storyText: config.initialStory,
        isLoading: false,
        results: null,
        errorMessage: '',

        async executeComparison() {
            if (!this.storyText.trim()) return;

            this.isLoading = true;
            this.errorMessage = '';

            try {
                const response = await fetch("{{ route('cultural-hub.living-heritage.compare.run') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ story: this.storyText })
                });

                const data = await response.json();
                if (data.models) {
                    this.results = data.models;
                } else if (data.message) {
                    this.errorMessage = data.message;
                }
            } catch (err) {
                this.errorMessage = 'Comparison failed: ' + err.message;
            } finally {
                this.isLoading = false;
                this.$nextTick(() => {
                    lucide.createIcons();
                });
            }
        }
    }
}
</script>
@endpush
