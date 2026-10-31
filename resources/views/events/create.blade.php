@extends('layouts.app')

@section('content')
  <div class="max-w-3xl mx-auto bg-white dark:bg-stone-900 border border-stone-100 dark:border-stone-800 shadow-lg rounded-2xl p-6 sm:p-8 mt-10">
    <h2 class="text-3xl font-bold mb-8 text-gray-800 dark:text-stone-100">
      {{ isset($community_id) ? 'Schedule Community Meeting' : 'Create Event' }}
    </h2>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-600 dark:text-red-400 text-sm">
            <p class="font-bold mb-1">Please fix the following issues:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="eventOptimizer()">
      @csrf

      @if(isset($community_id))
          <input type="hidden" name="community_id" value="{{ $community_id }}">
      @endif

      <input type="hidden" name="reach_tags" :value="JSON.stringify(reachTags)">
      <input type="hidden" name="ai_reach_metadata" :value="JSON.stringify(aiMetadata)">
      <input type="hidden" name="ai_flyer_path" :value="aiFlyerPath">

      {{-- AI Cultural Reach Assistant Drawer / Card --}}
      <div class="bg-gradient-to-r from-amber-500/10 via-stone-900/5 to-emerald-500/10 dark:from-amber-950/20 dark:via-stone-900 dark:to-emerald-950/20 rounded-2xl p-4 sm:p-5 border border-amber-500/20 dark:border-amber-500/10 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4">
          <div class="flex items-start gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-xs">
              <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
              <h3 class="font-bold text-stone-900 dark:text-stone-100 text-sm sm:text-base flex flex-wrap items-center gap-1.5 sm:gap-2 leading-tight">
                <span>AI Cultural Reach Assistant</span>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30 shrink-0">Meta AI</span>
              </h3>
              <p class="text-xs text-stone-600 dark:text-stone-400 mt-1 leading-relaxed">
                Enhance your gathering's discoverability, target audience tags, and community reach.
              </p>
            </div>
          </div>

          <button type="button" @click="optimize()" :disabled="optimizing"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 sm:py-2 rounded-xl bg-amber-600 hover:bg-amber-500 active:scale-95 text-white font-bold text-xs shadow-md shadow-amber-600/15 transition disabled:opacity-50 shrink-0">
            <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
            <span x-text="optimizing ? 'Analyzing...' : 'Optimize Reach with AI'"></span>
          </button>
        </div>

        {{-- Optimization Suggestions Panel --}}
        <template x-if="optimization">
          <div class="bg-white/90 dark:bg-stone-950/90 backdrop-blur-sm rounded-xl p-3.5 sm:p-4 border border-amber-500/25 space-y-3.5 text-xs animate-in fade-in duration-200 shadow-sm">
            <div class="flex items-center justify-between text-stone-700 dark:text-stone-300 font-bold border-b border-stone-200/80 dark:border-stone-800 pb-2.5">
              <span class="text-amber-600 dark:text-amber-400 font-black flex items-center gap-1.5">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-500"></i>
                AI Recommendations
              </span>
              <button type="button" @click="applyAll()" class="text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 font-semibold hover:underline transition">Apply All Suggestions</button>
            </div>

            {{-- Suggested Title --}}
            <div class="space-y-1">
              <span class="text-stone-400 dark:text-stone-500 font-semibold uppercase tracking-wider text-[10px] block">Suggested Catchy Cultural Title:</span>
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2.5 rounded-lg bg-stone-50 dark:bg-stone-900/60 border border-stone-200/60 dark:border-stone-800/60">
                <span class="font-bold text-stone-800 dark:text-stone-200 break-words leading-snug" x-text="optimization.optimized_title"></span>
                <button type="button" @click="applyTitle()" class="self-end sm:self-auto px-2.5 py-1 rounded-md bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold text-[10px] transition shrink-0 shadow-xs">Use</button>
              </div>
            </div>

            {{-- Suggested Description --}}
            <div class="space-y-1">
              <span class="text-stone-400 dark:text-stone-500 font-semibold uppercase tracking-wider text-[10px] block">Culturally Enriched Description:</span>
              <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2.5 p-2.5 rounded-lg bg-stone-50 dark:bg-stone-900/60 border border-stone-200/60 dark:border-stone-800/60">
                <p class="text-stone-600 dark:text-stone-300 leading-relaxed break-words text-xs flex-1" x-text="optimization.optimized_description"></p>
                <button type="button" @click="applyDescription()" class="self-end sm:self-start px-2.5 py-1 rounded-md bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold text-[10px] shrink-0 transition shadow-xs">Use</button>
              </div>
            </div>

            {{-- Reach Tags --}}
            <template x-if="optimization.reach_tags && optimization.reach_tags.length > 0">
              <div class="space-y-1">
                <span class="text-stone-400 dark:text-stone-500 font-semibold uppercase tracking-wider text-[10px] block">Recommended Reach Tags:</span>
                <div class="flex flex-wrap gap-1.5 mt-1">
                  <template x-for="tag in optimization.reach_tags" :key="tag">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/25" x-text="'#' + tag"></span>
                  </template>
                </div>
              </div>
            </template>

            {{-- Target Communities & Timing Tips --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2.5 border-t border-stone-200/80 dark:border-stone-800 text-[11px] text-stone-500 dark:text-stone-400">
              <div class="p-2 rounded-lg bg-stone-50/60 dark:bg-stone-900/40">
                <strong class="text-stone-700 dark:text-stone-200 block mb-0.5">Target Cultural Circles:</strong>
                <span class="break-words" x-text="optimization.target_communities ? optimization.target_communities.join(', ') : 'Heritage Hubs'"></span>
              </div>
              <div class="p-2 rounded-lg bg-stone-50/60 dark:bg-stone-900/40">
                <strong class="text-stone-700 dark:text-stone-200 block mb-0.5">Cultural Timing Advice:</strong>
                <span class="break-words" x-text="optimization.timing_suggestions || 'Evenings & Weekends'"></span>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Title -->
      <div>
        <label for="title" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Event/Meeting Title</label>
        <input type="text" name="title" id="title" x-model="formTitle" value="{{ old('title') }}"
          class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
          placeholder="What is this event about?" required>
        @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <!-- Description & Concept Generator -->
      <div class="space-y-2">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <label for="description" class="block text-gray-700 dark:text-stone-300 font-semibold">Description</label>
          <button type="button" @click="showConceptHelper = !showConceptHelper"
            class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:text-amber-500 flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 transition">
            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
            <span x-text="showConceptHelper ? 'Hide Concept Helper' : 'AI Cultural Concept & Description'"></span>
          </button>
        </div>

        {{-- Interactive AI Concept Generator Drawer --}}
        <div x-show="showConceptHelper" x-transition class="p-3.5 sm:p-4 rounded-xl bg-stone-50 dark:bg-stone-800/40 border border-amber-500/20 space-y-3">
          <div class="flex items-start justify-between gap-2">
            <div>
              <p class="text-xs font-bold text-stone-800 dark:text-stone-200 flex items-center gap-1.5">
                <i data-lucide="file-text" class="w-3.5 h-3.5 text-amber-500"></i>
                Draft from Rough Key Points
              </p>
              <p class="text-[11px] text-stone-500 dark:text-stone-400 mt-0.5 leading-relaxed">
                Enter quick notes or an outline of your gathering. The AI will compose an authentic cultural concept and presentation for your community.
              </p>
            </div>
          </div>

          <textarea x-model="conceptPoints" rows="2"
            class="w-full border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-900 dark:text-stone-100 rounded-xl p-3 text-xs focus:ring-2 focus:ring-amber-500 outline-none"
            placeholder="e.g. Elders teaching traditional pottery songs to youth, hands-on clay shaping, blessing of ancestral earth, tea sharing..."></textarea>

          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <span class="text-[10px] text-stone-400 italic">No formal writing needed—just list your points.</span>
            <button type="button" @click="generateConceptDescription()" :disabled="generatingConcept || (!conceptPoints && !formTitle)"
              class="w-full sm:w-auto px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow flex items-center justify-center gap-1.5 disabled:opacity-50 transition">
              <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
              <span x-text="generatingConcept ? 'Composing Concept...' : 'Generate Description'"></span>
            </button>
          </div>

          {{-- Suggested Output Preview --}}
          <template x-if="suggestedConcept">
            <div class="mt-3 p-3.5 rounded-xl bg-white dark:bg-stone-900 border border-amber-500/20 space-y-2 text-xs">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-stone-100 dark:border-stone-800 pb-2">
                <div class="flex items-center gap-2">
                  <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold text-[10px]">Concept Preview</span>
                  <span class="text-[11px] font-semibold text-stone-500" x-text="suggestedConcept.tagline"></span>
                </div>
                <div class="flex items-center gap-2">
                  <button type="button" @click="applyConceptDesc()" class="w-full sm:w-auto px-2.5 py-1 rounded-lg bg-amber-500 text-white font-bold text-[11px] hover:bg-amber-600 transition">
                    Use This Description
                  </button>
                </div>
              </div>
              <p class="text-stone-700 dark:text-stone-300 whitespace-pre-line leading-relaxed text-xs" x-text="suggestedConcept.suggested_description"></p>
            </div>
          </template>
        </div>

        <textarea name="description" id="description" rows="4" x-model="formDesc"
          class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
          placeholder="Provide more details..." required>{{ old('description') }}</textarea>
        @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Date -->
          <div>
            <label for="event_date" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Date</label>
            <input type="date" name="event_date" id="event_date" x-model="formDate" value="{{ old('event_date') }}" min="{{ date('Y-m-d') }}"
              class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none" required>
            @error('event_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
          </div>

          <!-- Time -->
          <div>
            <label for="event_time" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Time</label>
            <input type="time" name="event_time" id="event_time" x-model="formTime" value="{{ old('event_time') }}"
              class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none" required>
            @error('event_time')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
          </div>
      </div>

      <!-- Type -->
      <div>
        <label for="type" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Event/Category Type</label>
        <select name="type" id="type" x-model="formType" @change="syncOccasionFromType()" class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:border-amber-500 focus:outline-none" required>
            <optgroup label="Educational Hub">
                <option value="class">Class / Lesson</option>
                <option value="training">Training Program</option>
                <option value="workshop" selected>Workshop</option>
                <option value="ritual">Traditional Ritual</option>
                <option value="ceremony">Ceremony</option>
            </optgroup>
            <optgroup label="Social, Family & Community">
                <option value="wedding">Wedding / Traditional Marriage</option>
                <option value="naming_ceremony">Naming Ceremony / Blessing</option>
                <option value="cultural">Cultural Gathering</option>
                <option value="exhibition">Exhibition</option>
                <option value="conference">Conference</option>
            </optgroup>
        </select>
        @error('type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <!-- Online / Location format -->
      <div class="space-y-4 border border-stone-200 dark:border-stone-800 rounded-xl p-5 bg-stone-50 dark:bg-stone-800/30">
        <div>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="is_online" value="0">
                <input type="checkbox" name="is_online" value="1" id="is_online" class="w-5 h-5 rounded border-stone-300 text-amber-500 focus:ring-amber-500" {{ old('is_online') ? 'checked' : '' }} onchange="toggleOnlineFields()">
                <span class="text-stone-800 dark:text-stone-200 font-medium">This is an online event/meeting</span>
            </label>
        </div>

        <div id="location-field">
            <label for="location" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Physical Venue / Location</label>
            <input type="text" name="location" id="location" x-model="formLocation" value="{{ old('location', '') }}"
            class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
            placeholder="e.g. Royal Palace Pavilion, Kano or Community Hall" required>
        </div>

        <div id="meeting-link-field" class="hidden">
            <label for="meeting_link" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">External Platform Link</label>
            <input type="url" name="meeting_link" id="meeting_link" value="{{ old('meeting_link') }}"
            class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
            placeholder="https://zoom.us/j/123456789">
            <p class="text-[10px] text-stone-500 mt-1 uppercase font-bold tracking-tight">Optional: Provide a link to an external platform like Zoom or Meet</p>
        </div>

        <!-- Internal Hosting Toggle -->
        <div class="mt-4 pt-4 border-t border-stone-200 dark:border-stone-800">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="is_hosted" value="0">
                <input type="checkbox" name="is_hosted" value="1" id="is_hosted" class="w-5 h-5 rounded border-stone-300 text-amber-500 focus:ring-amber-500" {{ old('is_hosted') ? 'checked' : '' }} onchange="toggleHostedFields()">
                <div>
                    <span class="block text-stone-800 dark:text-stone-200 font-medium">Host class content directly on Kulturegram</span>
                    <span class="block text-xs text-stone-500 dark:text-stone-400">Perfect for sharing videos, text lessons, and resources without needing external apps.</span>
                </div>
            </label>
        </div>

        <div id="hosted-content-field" class="hidden mt-4">
            <label for="hosted_content" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Class/Hosted Content</label>
            <textarea name="hosted_content" id="hosted_content" rows="10"
              class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none font-mono text-sm"
              placeholder="Paste your video embed codes, write your lesson content, or list resource links here...">{{ old('hosted_content') }}</textarea>
            <p class="text-xs text-stone-500 mt-2">You can use HTML embed codes for videos from YouTube, Vimeo, etc.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Max Attendees -->
          <div>
            <label for="max_attendees" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Capacity (Optional)</label>
            <input type="number" name="max_attendees" id="max_attendees" value="{{ old('max_attendees') }}" min="1"
              class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none" placeholder="Leave empty for unlimited">
            @error('max_attendees')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
          </div>

          <!-- Price / Ticketing -->
          <div>
            <label for="price" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Ticket Price ($)</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 font-bold">$</span>
                <input type="number" name="price" id="price" value="{{ old('price', 0) }}" step="0.01" min="0"
                  class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl pl-8 pr-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none text-lg font-bold" placeholder="0.00 (Free)">
            </div>
            <p class="text-[10px] text-stone-500 mt-1 uppercase font-bold tracking-tight">Set to 0.00 for free registration</p>
            @error('price')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
          </div>
      </div>

      <!-- Contributions Toggle -->
      <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30 rounded-2xl p-6">
        <label class="flex items-center gap-4 cursor-pointer">
            <input type="hidden" name="accepts_contributions" value="0">
            <input type="checkbox" name="accepts_contributions" value="1" {{ old('accepts_contributions') ? 'checked' : '' }}
                class="w-6 h-6 rounded-lg border-stone-300 text-amber-500 focus:ring-amber-500">
            <div>
                <span class="block text-stone-900 dark:text-stone-100 font-black text-lg">Accept Financial Contributions</span>
                <span class="block text-stone-600 dark:text-stone-400 text-sm">Allow attendees and viewers to send voluntary donations to support this {{ isset($community_id) ? 'meeting' : 'event' }}.</span>
            </div>
        </label>
      </div>

      <!-- Virtual Classroom Toggle -->
      <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30 rounded-2xl p-6">
          <label class="flex items-center gap-4 cursor-pointer">
              <input type="checkbox" id="enable_virtual" class="w-6 h-6 rounded-lg border-stone-300 text-blue-500 focus:ring-blue-500" onchange="toggleVirtualRoom()">
              <input type="hidden" name="virtual_room_id" id="virtual_room_id">
              <div>
                  <span class="block text-stone-900 dark:text-stone-100 font-black text-lg">Kulturegram Meet (Virtual Classroom)</span>
                  <span class="block text-stone-600 dark:text-stone-400 text-sm">Host a live, interactive video meeting with screen sharing on the platform. Participants can join directly from the event page.</span>
              </div>
          </label>
      </div>

      <!-- Upload Image / AI Flyer -->
      <div class="space-y-4">
        <label for="image" class="block text-gray-700 dark:text-stone-300 font-semibold">Event Cover Flyer / Image</label>
        
        {{-- AI Flyer Active Preview Card --}}
        <div x-show="aiFlyerUrl" x-transition class="space-y-3">
          <div class="relative rounded-2xl overflow-hidden border-2 border-amber-500/40 shadow-xl bg-stone-950">
            <div class="p-4 flex items-center justify-center bg-stone-950/90">
              <img :src="aiFlyerDataUrl || aiFlyerUrl" alt="AI Generated Cultural Flyer" class="max-h-[540px] w-auto rounded-xl shadow-2xl object-contain border border-amber-500/20">
            </div>
            <div class="bg-stone-900/95 border-t border-stone-800 p-4 sm:p-5">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-500 text-stone-950 shadow">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> AI Cultural Flyer Ready
                  </span>
                  <p class="text-xs text-stone-300 mt-1">Designed with your real event date, time, venue, and custom images.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                  <button type="button" @click="downloadFlyer()"
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition flex items-center gap-1.5 shadow cursor-pointer">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    <span>Download Flyer</span>
                  </button>
                  <button type="button" @click="showFlyerDesigner = !showFlyerDesigner"
                    class="px-3.5 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/40 text-amber-300 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                    <span x-text="showFlyerDesigner ? 'Hide Designer' : 'Edit Flyer Details & Photo'"></span>
                  </button>
                  <button type="button" @click="generateFlyer()" :disabled="generatingFlyer"
                    class="px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-bold transition flex items-center gap-1.5 disabled:opacity-50 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="generatingFlyer ? 'animate-spin' : ''"></i>
                    <span x-text="generatingFlyer ? 'Updating...' : 'Regenerate'"></span>
                  </button>
                  <button type="button" @click="clearFlyer()"
                    class="px-3 py-2 rounded-xl bg-red-600/80 hover:bg-red-600 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>Remove</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Manual File Upload Box (shown when no AI flyer) --}}
        <div x-show="!aiFlyerUrl" class="space-y-3">
          <div id="image-upload-box"
            class="w-full py-10 border-2 border-dashed border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-800/50 rounded-xl text-center cursor-pointer hover:border-amber-500 transition relative"
            onclick="document.getElementById('image').click()">
            <input type="file" name="image" id="image" accept="image/*" class="hidden" onchange="previewImage(event)">

            <div id="preview" class="hidden">
              <img id="preview-img" class="mx-auto max-h-48 rounded-lg shadow object-cover" />
              <p class="text-gray-500 text-sm mt-2">Click to change cover</p>
            </div>

            <div id="upload-text" class="text-gray-500">
              <i data-lucide="image" class="w-12 h-12 mx-auto mb-3 text-stone-400"></i>
              <p class="text-base text-stone-600 dark:text-stone-300 font-medium">Click to upload custom flyer / photo</p>
              <p class="text-xs text-stone-400 mt-1">PNG, JPG up to 10MB</p>
            </div>
          </div>

          {{-- AI Flyer Action Prompt --}}
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-4 rounded-xl bg-gradient-to-r from-amber-500/10 via-stone-500/5 to-amber-500/5 border border-amber-500/20">
            <div class="space-y-0.5">
              <span class="text-xs font-bold text-stone-900 dark:text-stone-100 flex items-center gap-1.5">
                <i data-lucide="palette" class="w-4 h-4 text-amber-500"></i>
                Don't have a flyer? Let AI design one for you
              </span>
              <p class="text-[11px] text-stone-500 dark:text-stone-400">
                Add your real event date, venue, time, couple/baby/speaker photo, and logo—ideal for weddings, naming ceremonies, and cultural events.
              </p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto shrink-0">
              <button type="button" @click="showFlyerDesigner = !showFlyerDesigner"
                class="w-full sm:w-auto px-3.5 py-2 rounded-xl border border-amber-500/40 bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 font-bold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                <span x-text="showFlyerDesigner ? 'Hide Flyer Studio' : 'Customize Flyer Details & Photo'"></span>
              </button>
              <button type="button" @click="generateFlyer()" :disabled="generatingFlyer"
                class="w-full sm:w-auto px-4 py-2 rounded-xl bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-900 hover:bg-amber-600 dark:hover:bg-amber-400 dark:hover:text-stone-950 font-bold text-xs shadow flex items-center justify-center gap-2 shrink-0 disabled:opacity-50 transition cursor-pointer">
                <i data-lucide="wand-2" class="w-3.5 h-3.5" :class="generatingFlyer ? 'animate-spin' : ''"></i>
                <span x-text="generatingFlyer ? 'Designing Flyer...' : 'Generate AI Cultural Flyer'"></span>
              </button>
            </div>
          </div>
        </div>

        {{-- Interactive AI Cultural Flyer Studio Panel (Real Details, Photo, Logo, Date, Time, Venue) --}}
        <div x-show="showFlyerDesigner" x-transition class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-950/90 border-2 border-amber-500/30 space-y-4">
          <div class="flex items-center justify-between border-b border-stone-200 dark:border-stone-800 pb-3">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center">
                <i data-lucide="Sparkles" class="w-4 h-4"></i>
              </div>
              <div>
                <h4 class="text-sm font-black text-stone-900 dark:text-white">AI Cultural Flyer Studio — Real Event Details</h4>
                <p class="text-[11px] text-stone-500 dark:text-stone-400">Every detail below is printed directly onto your downloadable flyer—no random guesses.</p>
              </div>
            </div>
            <button type="button" @click="showFlyerDesigner = false" class="text-xs text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 font-bold">Close</button>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Ceremony / Occasion Header --}}
            <div>
              <label class="block text-[11px] font-black uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                Ceremony / Occasion Banner *
              </label>
              <input type="text" x-model="flyerOccasionLabel"
                placeholder="e.g. TRADITIONAL WEDDING CEREMONY or NAMING CEREMONY"
                class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3.5 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            {{-- Color Theme --}}
            <div>
              <label class="block text-[11px] font-black uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                Cultural Color Palette
              </label>
              <select x-model="flyerPalette"
                class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3.5 py-2 text-xs text-stone-900 dark:text-stone-100 focus:border-amber-500 outline-none">
                <option value="royal_gold">Royal Gold &amp; Obsidian (Classic Regal)</option>
                <option value="emerald_ivory">Emerald &amp; Gold (Wedding / Naming Ceremony)</option>
                <option value="terracotta_bronze">Terracotta &amp; Saharan Bronze (Ancestral Earth)</option>
                <option value="indigo_silver">Adire Indigo &amp; Silver (Royal Court)</option>
                <option value="crimson_amber">Ceremonial Crimson &amp; Amber (Festival)</option>
              </select>
            </div>

            {{-- Main Title / Honoree Names (Synced with Event Title) --}}
            <div>
              <label class="block text-[11px] font-black uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                Main Title / Couple or Honoree Names *
              </label>
              <input type="text" x-model="formTitle"
                placeholder="e.g. Amina & Ibrahim / Naming of Baby Zaynab"
                class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3.5 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            {{-- Host Family / Subtitle --}}
            <div>
              <label class="block text-[11px] font-black uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                Host Family / Invitation Line / Subtitle
              </label>
              <input type="text" x-model="flyerSubtitle"
                placeholder="e.g. The Families of Alhaji Musa & Chief Adeyemi Cordially Invite You"
                class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3.5 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            {{-- Event Date (Synced with formDate) --}}
            <div>
              <label class="block text-[11px] font-black uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                Event Date (Real Date on Flyer) *
              </label>
              <input type="date" x-model="formDate"
                class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3.5 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            {{-- Event Time (Synced with formTime) --}}
            <div>
              <label class="block text-[11px] font-black uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                Event Time (Real Time on Flyer) *
              </label>
              <input type="time" x-model="formTime"
                class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3.5 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            {{-- Venue / Location (Synced with formLocation) --}}
            <div class="sm:col-span-2">
              <label class="block text-[11px] font-black uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                Venue / Address on Flyer *
              </label>
              <input type="text" x-model="formLocation"
                placeholder="e.g. Royal Palace Grand Pavilion, Nassarawa GRA, Kano"
                class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3.5 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            {{-- Dress Code / Colors of the Day --}}
            <div>
              <label class="block text-[11px] font-black uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                Dress Code / Colors of the Day (Optional)
              </label>
              <input type="text" x-model="flyerDressCode"
                placeholder="e.g. Traditional Regal Attire (Emerald & Gold)"
                class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3.5 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            {{-- RSVP / Contact --}}
            <div>
              <label class="block text-[11px] font-black uppercase tracking-wider text-stone-600 dark:text-stone-400 mb-1">
                RSVP / Contact Info (Optional)
              </label>
              <input type="text" x-model="flyerRsvp"
                placeholder="e.g. 0803 000 0000 • 0802 111 2222"
                class="w-full rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-3.5 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            {{-- Upload Subject / Couple / Celebrant Photo --}}
            <div class="p-3.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2">
              <div class="flex items-center justify-between">
                <label class="text-[11px] font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 flex items-center gap-1.5">
                  <i data-lucide="user-Check" class="w-3.5 h-3.5 text-amber-500"></i>
                  <span>Featured Photo (Couple / Baby / Speaker)</span>
                </label>
                <button type="button" x-show="flyerSubjectPhotoDataUrl" @click="flyerSubjectPhotoDataUrl = ''"
                  class="text-[11px] text-red-500 hover:underline font-bold">Remove</button>
              </div>
              <input type="file" accept="image/*" @change="onFlyerPhotoSelected($event)"
                class="w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-500/15 file:text-amber-600 dark:file:text-amber-400 hover:file:bg-amber-500/25 cursor-pointer">
              <div x-show="flyerSubjectPhotoDataUrl" class="flex items-center gap-2 pt-1">
                <img :src="flyerSubjectPhotoDataUrl" class="w-12 h-12 rounded-full object-cover border-2 border-amber-500">
                <span class="text-[11px] text-emerald-500 font-semibold">Photo ready for flyer centerpiece</span>
              </div>
            </div>

            {{-- Upload Logo / Family Crest --}}
            <div class="p-3.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2">
              <div class="flex items-center justify-between">
                <label class="text-[11px] font-black uppercase tracking-wider text-stone-700 dark:text-stone-300 flex items-center gap-1.5">
                  <i data-lucide="shield" class="w-3.5 h-3.5 text-amber-500"></i>
                  <span>Event Logo / Family Crest (Optional)</span>
                </label>
                <button type="button" x-show="flyerLogoDataUrl" @click="flyerLogoDataUrl = ''"
                  class="text-[11px] text-red-500 hover:underline font-bold">Remove</button>
              </div>
              <input type="file" accept="image/*" @change="onFlyerLogoSelected($event)"
                class="w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-500/15 file:text-amber-600 dark:file:text-amber-400 hover:file:bg-amber-500/25 cursor-pointer">
              <div x-show="flyerLogoDataUrl" class="flex items-center gap-2 pt-1">
                <img :src="flyerLogoDataUrl" class="w-12 h-12 rounded-lg object-contain bg-stone-950 p-1 border border-amber-500">
                <span class="text-[11px] text-emerald-500 font-semibold">Logo ready for top crest</span>
              </div>
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-stone-200 dark:border-stone-800">
            <span class="text-[11px] text-stone-500">
              Generates a high-resolution downloadable PNG flyer with your exact details.
            </span>
            <div class="flex items-center gap-2">
              <button type="button" x-show="aiFlyerUrl" @click="downloadFlyer()"
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow flex items-center gap-1.5 transition cursor-pointer">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                <span>Download Flyer (PNG)</span>
              </button>
              <button type="button" @click="generateFlyer()" :disabled="generatingFlyer"
                class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-stone-950 font-black text-xs shadow-lg flex items-center gap-2 disabled:opacity-50 transition cursor-pointer">
                <i data-lucide="wand-2" class="w-4 h-4" :class="generatingFlyer ? 'animate-spin' : ''"></i>
                <span x-text="generatingFlyer ? 'Designing Flyer...' : 'Generate AI Cultural Flyer'"></span>
              </button>
            </div>
          </div>
        </div>

        @error('image')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <script>
        function toggleOnlineFields() {
            const isOnline = document.getElementById('is_online').checked;
            const locField = document.getElementById('location-field');
            const linkField = document.getElementById('meeting-link-field');
            const locInput = document.getElementById('location');

            if (isOnline) {
                locField.classList.add('hidden');
                linkField.classList.remove('hidden');
                if (!locInput.value || locInput.value === '') {
                    locInput.value = 'Online';
                }
            } else {
                locField.classList.remove('hidden');
                linkField.classList.add('hidden');
                if (locInput.value === 'Online') {
                    locInput.value = '';
                }
            }
        }
        
        // Initialize state on load
        document.addEventListener('DOMContentLoaded', () => {
            toggleOnlineFields();
            toggleHostedFields();
        });

        function toggleHostedFields() {
            const isHosted = document.getElementById('is_hosted').checked;
            const hostedField = document.getElementById('hosted-content-field');
            
            if (isHosted) {
                hostedField.classList.remove('hidden');
            } else {
                hostedField.classList.add('hidden');
            }
        }

        function toggleVirtualRoom() {
            const isEnable = document.getElementById('enable_virtual').checked;
            const roomIdField = document.getElementById('virtual_room_id');
            
            if (isEnable) {
                // Generate a unique room ID
                const randomStr = Math.random().toString(36).substring(2, 8).toUpperCase();
                roomIdField.value = `KULTUREGRAM-MEET-${randomStr}`;
            } else {
                roomIdField.value = '';
            }
        }

        function previewImage(event) {
          const file = event.target.files[0];
          const previewContainer = document.getElementById('preview');
          const previewImage = document.getElementById('preview-img');
          const uploadText = document.getElementById('upload-text');

          if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
              previewImage.src = e.target.result;
              previewContainer.classList.remove('hidden');
              uploadText.classList.add('hidden');
            }
            reader.readAsDataURL(file);
          }
        }
      </script>

      <!-- Submit -->
      <div class="pt-4 border-t border-stone-100 dark:border-stone-800">
        <button type="submit"
          class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-lg px-6 py-4 rounded-xl shadow-md transition-all duration-200 cursor-pointer active:scale-[0.99]">
          {{ isset($community_id) ? 'Schedule Meeting' : 'Create Event' }}
        </button>
      </div>
    </form>
  </div>
  
  @php
      $initEventTitle = old('title', '');
      $initEventDesc = old('description', '');
      $initEventDate = old('event_date', '');
      $initEventTime = old('event_time', '');
      $initEventType = old('type', 'workshop');
      $initEventLocation = old('location', '');
  @endphp
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
      lucide.createIcons();

      document.addEventListener('alpine:init', () => {
          Alpine.data('eventOptimizer', () => ({
              formTitle: @json($initEventTitle),
              formDesc: @json($initEventDesc),
              formDate: @json($initEventDate),
              formTime: @json($initEventTime),
              formType: @json($initEventType),
              formLocation: @json($initEventLocation),
              optimizing: false,
              optimization: null,
              reachTags: [],
              aiMetadata: null,

              // Cultural Concept & Description Generator
              showConceptHelper: false,
              conceptPoints: '',
              generatingConcept: false,
              suggestedConcept: null,

              // AI Flyer Studio & Customizer
              showFlyerDesigner: false,
              generatingFlyer: false,
              aiFlyerPath: '',
              aiFlyerUrl: '',
              aiFlyerDataUrl: '',
              flyerOccasionLabel: 'CULTURAL HERITAGE WORKSHOP',
              flyerSubtitle: '',
              flyerDressCode: '',
              flyerRsvp: '',
              flyerPalette: 'royal_gold',
              flyerSubjectPhotoDataUrl: '',
              flyerLogoDataUrl: '',

              init() {
                  this.syncOccasionFromType();
              },

              syncOccasionFromType() {
                  const labels = {
                      wedding: 'TRADITIONAL WEDDING CEREMONY',
                      naming_ceremony: 'NAMING CEREMONY & BLESSING',
                      ritual: 'SACRED TRADITIONAL RITUAL',
                      ceremony: 'ROYAL CULTURAL CEREMONY',
                      cultural: 'CULTURAL HERITAGE GATHERING',
                      workshop: 'CULTURAL HERITAGE WORKSHOP',
                      class: 'CULTURAL HERITAGE CLASS',
                      training: 'HERITAGE TRAINING PROGRAM',
                      exhibition: 'CULTURAL ART & HERITAGE EXHIBITION',
                      conference: 'CULTURAL LEADERSHIP CONFERENCE'
                  };
                  this.flyerOccasionLabel = labels[this.formType] || 'CULTURAL HERITAGE GATHERING';
                  if (this.formType === 'wedding' || this.formType === 'naming_ceremony') {
                      this.flyerPalette = 'emerald_ivory';
                  }
              },

              onFlyerPhotoSelected(event) {
                  const file = event.target.files && event.target.files[0];
                  if (!file) return;
                  const reader = new FileReader();
                  reader.onload = (e) => {
                      this.flyerSubjectPhotoDataUrl = e.target.result;
                  };
                  reader.readAsDataURL(file);
              },

              onFlyerLogoSelected(event) {
                  const file = event.target.files && event.target.files[0];
                  if (!file) return;
                  const reader = new FileReader();
                  reader.onload = (e) => {
                      this.flyerLogoDataUrl = e.target.result;
                  };
                  reader.readAsDataURL(file);
              },

              formatPrettyDate(dateStr) {
                  if (!dateStr) return 'Date TBA';
                  try {
                      const parts = dateStr.split('-');
                      if (parts.length === 3) {
                          const d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
                          return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                      }
                  } catch (e) {}
                  return dateStr;
              },

              formatPrettyTime(timeStr) {
                  if (!timeStr) return 'Time TBA';
                  try {
                      const [hStr, mStr] = timeStr.split(':');
                      let h = parseInt(hStr, 10);
                      if (!isNaN(h)) {
                          const ampm = h >= 12 ? 'PM' : 'AM';
                          h = h % 12 || 12;
                          return `${h}:${mStr || '00'} ${ampm}`;
                      }
                  } catch (e) {}
                  return timeStr;
              },

              loadImageElement(src) {
                  return new Promise((resolve) => {
                      if (!src) return resolve(null);
                      const img = new Image();
                      img.crossOrigin = 'anonymous';
                      img.onload = () => resolve(img);
                      img.onerror = () => resolve(null);
                      img.src = src;
                  });
              },

              wrapCanvasText(ctx, text, x, y, maxWidth, lineHeight, maxLines = 2) {
                  const words = String(text || '').trim().split(/\s+/);
                  let line = '';
                  let lines = [];
                  for (let n = 0; n < words.length; n++) {
                      const testLine = line + (line ? ' ' : '') + words[n];
                      const metrics = ctx.measureText(testLine);
                      if (metrics.width > maxWidth && n > 0) {
                          lines.push(line);
                          line = words[n];
                          if (lines.length >= maxLines - 1) {
                              for (let r = n + 1; r < words.length; r++) {
                                  line += ' ' + words[r];
                              }
                              break;
                          }
                      } else {
                          line = testLine;
                      }
                  }
                  if (line) lines.push(line);
                  const totalH = (lines.length - 1) * lineHeight;
                  let startY = y - totalH / 2;
                  lines.forEach((l) => {
                      ctx.fillText(l, x, startY);
                      startY += lineHeight;
                  });
                  return startY;
              },

              async renderFlyerCanvasDataUrl() {
                  const canvas = document.createElement('canvas');
                  canvas.width = 1200;
                  canvas.height = 1350;
                  const ctx = canvas.getContext('2d');

                  const palettes = {
                      royal_gold: { bg1: '#78350f', bg2: '#3b1504', bg3: '#0c0a09', gold: '#fbbf24', accent: '#f59e0b', card: 'rgba(12, 10, 9, 0.82)' },
                      emerald_ivory: { bg1: '#065f46', bg2: '#022c22', bg3: '#091512', gold: '#fbbf24', accent: '#34d399', card: 'rgba(2, 44, 34, 0.82)' },
                      terracotta_bronze: { bg1: '#9a3412', bg2: '#431407', bg3: '#1c1917', gold: '#fbbf24', accent: '#fb923c', card: 'rgba(28, 25, 23, 0.82)' },
                      indigo_silver: { bg1: '#1e1b4b', bg2: '#312e81', bg3: '#090d16', gold: '#e2e8f0', accent: '#818cf8', card: 'rgba(15, 23, 42, 0.82)' },
                      crimson_amber: { bg1: '#7f1d1d', bg2: '#450a0a', bg3: '#18181b', gold: '#fbbf24', accent: '#f87171', card: 'rgba(24, 24, 27, 0.82)' }
                  };
                  const pal = palettes[this.flyerPalette] || palettes.royal_gold;

                  // 1. Background gradient
                  const grad = ctx.createLinearGradient(0, 0, 1200, 1350);
                  grad.addColorStop(0, pal.bg1);
                  grad.addColorStop(0.5, pal.bg2);
                  grad.addColorStop(1, pal.bg3);
                  ctx.fillStyle = grad;
                  ctx.fillRect(0, 0, 1200, 1350);

                  // 2. Subtle cultural geometric diamond pattern
                  ctx.strokeStyle = 'rgba(251, 191, 36, 0.10)';
                  ctx.lineWidth = 2;
                  for (let gx = 0; gx < 1200; gx += 75) {
                      for (let gy = 0; gy < 1350; gy += 75) {
                          ctx.beginPath();
                          ctx.moveTo(gx, gy + 37.5);
                          ctx.lineTo(gx + 37.5, gy);
                          ctx.lineTo(gx + 75, gy + 37.5);
                          ctx.lineTo(gx + 37.5, gy + 75);
                          ctx.closePath();
                          ctx.stroke();
                      }
                  }

                  // 3. Outer & inner ceremonial frames
                  ctx.strokeStyle = pal.gold;
                  ctx.globalAlpha = 0.55;
                  ctx.lineWidth = 3;
                  ctx.setLineDash([14, 8]);
                  ctx.strokeRect(42, 42, 1116, 1266);
                  ctx.setLineDash([]);
                  ctx.globalAlpha = 1.0;

                  ctx.fillStyle = pal.card;
                  ctx.fillRect(66, 66, 1068, 1218);
                  ctx.strokeStyle = pal.gold;
                  ctx.lineWidth = 2;
                  ctx.strokeRect(66, 66, 1068, 1218);

                  // 4. Top Logo or Cultural Emblem
                  const logoImg = await this.loadImageElement(this.flyerLogoDataUrl);
                  if (logoImg) {
                      ctx.save();
                      ctx.beginPath();
                      ctx.arc(600, 145, 52, 0, Math.PI * 2);
                      ctx.closePath();
                      ctx.clip();
                      ctx.drawImage(logoImg, 548, 93, 104, 104);
                      ctx.restore();
                      ctx.beginPath();
                      ctx.arc(600, 145, 54, 0, Math.PI * 2);
                      ctx.strokeStyle = pal.gold;
                      ctx.lineWidth = 3;
                      ctx.stroke();
                  } else {
                      ctx.beginPath();
                      ctx.arc(600, 145, 42, 0, Math.PI * 2);
                      ctx.fillStyle = 'rgba(251, 191, 36, 0.15)';
                      ctx.fill();
                      ctx.strokeStyle = pal.gold;
                      ctx.lineWidth = 2.5;
                      ctx.stroke();

                      ctx.beginPath();
                      ctx.moveTo(600, 115);
                      ctx.lineTo(624, 145);
                      ctx.lineTo(600, 175);
                      ctx.lineTo(576, 145);
                      ctx.closePath();
                      ctx.fillStyle = pal.gold;
                      ctx.fill();
                  }

                  // 5. Occasion / Ceremony Banner Pill
                  const occasionText = (this.flyerOccasionLabel || 'CULTURAL GATHERING').toUpperCase();
                  ctx.font = '900 18px system-ui, -apple-system, sans-serif';
                  const occWidth = Math.max(380, Math.min(820, ctx.measureText(occasionText).width + 90));
                  ctx.fillStyle = 'rgba(251, 191, 36, 0.16)';
                  ctx.strokeStyle = pal.gold;
                  ctx.lineWidth = 1.8;
                  ctx.beginPath();
                  ctx.roundRect(600 - occWidth / 2, 218, occWidth, 46, 23);
                  ctx.fill();
                  ctx.stroke();

                  ctx.fillStyle = pal.gold;
                  ctx.textAlign = 'center';
                  ctx.textBaseline = 'middle';
                  ctx.fillText(occasionText, 600, 241);

                  // 6. Main Event Title / Honoree Names
                  ctx.fillStyle = '#ffffff';
                  ctx.font = '900 50px Georgia, "Times New Roman", serif';
                  this.wrapCanvasText(ctx, this.formTitle || 'Cultural Celebration', 600, 326, 940, 56, 2);

                  // 7. Host Family / Subtitle
                  if (this.flyerSubtitle && this.flyerSubtitle.trim()) {
                      ctx.fillStyle = '#e2e8f0';
                      ctx.font = '600 23px system-ui, -apple-system, sans-serif';
                      this.wrapCanvasText(ctx, this.flyerSubtitle.trim(), 600, 405, 920, 30, 2);
                  }

                  // 8. Centerpiece Photo (Couple / Baby / Honoree / Speaker) or Ceremonial Medallion
                  const subjectImg = await this.loadImageElement(this.flyerSubjectPhotoDataUrl);
                  const centerY = 615;
                  if (subjectImg) {
                      const radius = 172;
                      ctx.save();
                      ctx.beginPath();
                      ctx.arc(600, centerY, radius, 0, Math.PI * 2);
                      ctx.closePath();
                      ctx.clip();
                      const scale = Math.max((radius * 2) / subjectImg.width, (radius * 2) / subjectImg.height);
                      const dw = subjectImg.width * scale;
                      const dh = subjectImg.height * scale;
                      ctx.drawImage(subjectImg, 600 - dw / 2, centerY - dh / 2, dw, dh);
                      ctx.restore();

                      ctx.beginPath();
                      ctx.arc(600, centerY, radius + 4, 0, Math.PI * 2);
                      ctx.strokeStyle = pal.gold;
                      ctx.lineWidth = 6;
                      ctx.stroke();

                      ctx.beginPath();
                      ctx.arc(600, centerY, radius + 16, 0, Math.PI * 2);
                      ctx.strokeStyle = pal.accent;
                      ctx.setLineDash([12, 8]);
                      ctx.lineWidth = 2.5;
                      ctx.stroke();
                      ctx.setLineDash([]);
                  } else {
                      ctx.beginPath();
                      ctx.arc(600, centerY, 130, 0, Math.PI * 2);
                      ctx.fillStyle = 'rgba(255, 255, 255, 0.04)';
                      ctx.fill();
                      ctx.strokeStyle = pal.gold;
                      ctx.lineWidth = 3;
                      ctx.stroke();

                      ctx.beginPath();
                      ctx.arc(600, centerY, 96, 0, Math.PI * 2);
                      ctx.setLineDash([8, 6]);
                      ctx.stroke();
                      ctx.setLineDash([]);

                      ctx.beginPath();
                      ctx.moveTo(600, centerY - 56);
                      ctx.lineTo(656, centerY);
                      ctx.lineTo(600, centerY + 56);
                      ctx.lineTo(544, centerY);
                      ctx.closePath();
                      ctx.fillStyle = pal.gold;
                      ctx.fill();
                  }

                  // 9. Real Date & Time Cards
                  const dateInputEl = document.getElementById('event_date');
                  const timeInputEl = document.getElementById('event_time');
                  const locInputEl = document.getElementById('location');
                  const isOnlineEl = document.getElementById('is_online');

                  const rawDate = this.formDate || (dateInputEl ? dateInputEl.value : '');
                  const rawTime = this.formTime || (timeInputEl ? timeInputEl.value : '');
                  const rawVenue = this.formLocation || (locInputEl ? locInputEl.value : '') || (isOnlineEl && isOnlineEl.checked ? 'Online Event' : 'Venue TBA');

                  const displayDate = this.formatPrettyDate(rawDate);
                  const displayTime = this.formatPrettyTime(rawTime);

                  // Date Box
                  ctx.fillStyle = 'rgba(255, 255, 255, 0.06)';
                  ctx.strokeStyle = 'rgba(251, 191, 36, 0.4)';
                  ctx.lineWidth = 1.8;
                  ctx.beginPath();
                  ctx.roundRect(120, 840, 460, 118, 20);
                  ctx.fill();
                  ctx.stroke();

                  ctx.fillStyle = pal.gold;
                  ctx.font = '800 15px system-ui, -apple-system, sans-serif';
                  ctx.fillText('EVENT DATE', 350, 875);
                  ctx.fillStyle = '#ffffff';
                  ctx.font = '900 26px system-ui, -apple-system, sans-serif';
                  ctx.fillText(displayDate, 350, 918);

                  // Time Box
                  ctx.fillStyle = 'rgba(255, 255, 255, 0.06)';
                  ctx.beginPath();
                  ctx.roundRect(620, 840, 460, 118, 20);
                  ctx.fill();
                  ctx.stroke();

                  ctx.fillStyle = pal.gold;
                  ctx.font = '800 15px system-ui, -apple-system, sans-serif';
                  ctx.fillText('TIME', 850, 875);
                  ctx.fillStyle = '#ffffff';
                  ctx.font = '900 26px system-ui, -apple-system, sans-serif';
                  ctx.fillText(displayTime, 850, 918);

                  // 10. Real Venue / Location Box
                  ctx.fillStyle = 'rgba(255, 255, 255, 0.06)';
                  ctx.beginPath();
                  ctx.roundRect(120, 982, 960, 130, 20);
                  ctx.fill();
                  ctx.stroke();

                  ctx.fillStyle = pal.gold;
                  ctx.font = '800 15px system-ui, -apple-system, sans-serif';
                  ctx.fillText('VENUE / LOCATION', 600, 1016);
                  ctx.fillStyle = '#ffffff';
                  ctx.font = '900 27px system-ui, -apple-system, sans-serif';
                  this.wrapCanvasText(ctx, rawVenue, 600, 1064, 900, 32, 2);

                  // 11. Dress Code & RSVP Footer
                  ctx.strokeStyle = 'rgba(251, 191, 36, 0.35)';
                  ctx.beginPath();
                  ctx.moveTo(180, 1152);
                  ctx.lineTo(1020, 1152);
                  ctx.stroke();

                  const footerParts = [];
                  if (this.flyerDressCode && this.flyerDressCode.trim()) {
                      footerParts.push('DRESS CODE: ' + this.flyerDressCode.trim());
                  }
                  if (this.flyerRsvp && this.flyerRsvp.trim()) {
                      footerParts.push('RSVP: ' + this.flyerRsvp.trim());
                  }
                  const footerText = footerParts.length > 0
                      ? footerParts.join('   •   ')
                      : 'KULTUREGRAM CULTURAL PRESERVATION & COMMUNITY HERITAGE';

                  ctx.fillStyle = pal.gold;
                  ctx.font = '700 19px system-ui, -apple-system, sans-serif';
                  this.wrapCanvasText(ctx, footerText, 600, 1200, 940, 26, 2);

                  return canvas.toDataURL('image/png');
              },

              async generateFlyer() {
                  if (!this.formTitle || !this.formTitle.trim()) {
                      this.showFlyerDesigner = true;
                      alert('Please enter an Event Title (or Couple / Honoree Names) first before generating your flyer.');
                      return;
                  }

                  this.generatingFlyer = true;
                  try {
                      const renderedPngDataUrl = await this.renderFlyerCanvasDataUrl();
                      this.aiFlyerDataUrl = renderedPngDataUrl;

                      const dateInputEl = document.getElementById('event_date');
                      const timeInputEl = document.getElementById('event_time');
                      const locInputEl = document.getElementById('location');

                      const res = await fetch("{{ route('events.ai-generate-flyer') }}", {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/json',
                              'X-CSRF-TOKEN': "{{ csrf_token() }}"
                          },
                          body: JSON.stringify({
                              title: this.formTitle,
                              description: this.formDesc || this.formTitle,
                              type: this.formType || 'Cultural Gathering',
                              cultural_context: (this.reachTags && this.reachTags.length > 0) ? this.reachTags.join(', ') : '',
                              occasion_label: this.flyerOccasionLabel,
                              subtitle: this.flyerSubtitle,
                              event_date: this.formatPrettyDate(this.formDate || (dateInputEl ? dateInputEl.value : '')),
                              event_time: this.formatPrettyTime(this.formTime || (timeInputEl ? timeInputEl.value : '')),
                              location: this.formLocation || (locInputEl ? locInputEl.value : 'Online'),
                              dress_code: this.flyerDressCode,
                              rsvp: this.flyerRsvp,
                              theme_palette: this.flyerPalette,
                              rendered_flyer_data_url: renderedPngDataUrl
                          })
                      });
                      const data = await res.json();
                      if (data.success && (data.image_path || data.flyer_path)) {
                          this.aiFlyerPath = data.image_path || data.flyer_path;
                          this.aiFlyerUrl = data.image_url || data.flyer_url;
                          const manualPreview = document.getElementById('preview');
                          if (manualPreview) manualPreview.classList.add('hidden');
                          const uploadText = document.getElementById('upload-text');
                          if (uploadText) uploadText.classList.remove('hidden');
                          const manualInput = document.getElementById('image');
                          if (manualInput) manualInput.value = '';
                      } else {
                          alert(data.error || 'Flyer generation failed. Please try again.');
                      }
                  } catch (e) {
                      console.error('Flyer generation error:', e);
                  } finally {
                      this.generatingFlyer = false;
                      setTimeout(() => lucide.createIcons(), 50);
                  }
              },

              async downloadFlyer() {
                  let dataUrl = this.aiFlyerDataUrl;
                  if (!dataUrl) {
                      dataUrl = await this.renderFlyerCanvasDataUrl();
                      this.aiFlyerDataUrl = dataUrl;
                  }
                  const slug = (this.formTitle || 'cultural-event')
                      .toLowerCase()
                      .replace(/[^a-z0-9]+/g, '-')
                      .replace(/(^-|-$)/g, '') || 'cultural-event';
                  const link = document.createElement('a');
                  link.href = dataUrl || this.aiFlyerUrl;
                  link.download = `${slug}-flyer.png`;
                  document.body.appendChild(link);
                  link.click();
                  document.body.removeChild(link);
              },

              clearFlyer() {
                  this.aiFlyerPath = '';
                  this.aiFlyerUrl = '';
                  this.aiFlyerDataUrl = '';
              },

              async optimize() {
                  if (!this.formTitle && !this.formDesc) {
                      alert('Please enter at least a draft Title or Description first so the AI can understand your cultural topic.');
                      return;
                  }

                  this.optimizing = true;
                  try {
                      const res = await fetch("{{ route('events.ai-optimize') }}", {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/json',
                              'X-CSRF-TOKEN': "{{ csrf_token() }}"
                          },
                          body: JSON.stringify({
                              title: this.formTitle || 'Cultural Gathering',
                              description: this.formDesc || this.formTitle,
                              type: document.getElementById('type') ? document.getElementById('type').value : 'cultural',
                              location: document.getElementById('location') ? document.getElementById('location').value : 'Online'
                          })
                      });
                      const data = await res.json();
                      if (data.success && data.data) {
                          this.optimization = data.data;
                          this.reachTags = data.data.reach_tags || [];
                          this.aiMetadata = data.data;
                      }
                  } catch (e) {
                      console.error('Optimization error:', e);
                  } finally {
                      this.optimizing = false;
                      setTimeout(() => lucide.createIcons(), 50);
                  }
              },

              async generateConceptDescription() {
                  if (!this.conceptPoints && !this.formTitle) {
                      alert('Please enter some key points or a gathering title first.');
                      return;
                  }

                  this.generatingConcept = true;
                  try {
                      const res = await fetch("{{ route('events.ai-suggest-description') }}", {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/json',
                              'X-CSRF-TOKEN': "{{ csrf_token() }}"
                          },
                          body: JSON.stringify({
                              title: this.formTitle || '',
                              key_points: this.conceptPoints,
                              type: document.getElementById('type') ? document.getElementById('type').value : 'workshop',
                              cultural_focus: (this.reachTags && this.reachTags.length > 0) ? this.reachTags.join(', ') : ''
                          })
                      });
                      const data = await res.json();
                      if (data.success && data.suggested_description) {
                          this.suggestedConcept = data;
                      } else {
                          alert(data.error || 'Could not generate concept. Please check your notes.');
                      }
                  } catch (e) {
                      console.error('Concept generation error:', e);
                  } finally {
                      this.generatingConcept = false;
                      setTimeout(() => lucide.createIcons(), 50);
                  }
              },

              applyConceptDesc() {
                  if (this.suggestedConcept?.suggested_description) {
                      this.formDesc = this.suggestedConcept.suggested_description;
                      if (!this.formTitle && this.suggestedConcept.concept_summary) {
                          this.formTitle = this.suggestedConcept.concept_summary;
                      }
                  }
              },

              applyTitle() {
                  if (this.optimization?.optimized_title) {
                      this.formTitle = this.optimization.optimized_title;
                  }
              },

              applyDescription() {
                  if (this.optimization?.optimized_description) {
                      this.formDesc = this.optimization.optimized_description;
                  }
              },

              applyAll() {
                  this.applyTitle();
                  this.applyDescription();
              }
          }));
      });
  </script>
@endsection
