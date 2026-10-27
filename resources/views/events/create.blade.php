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
      <div class="bg-gradient-to-r from-amber-500/10 via-stone-900/5 to-emerald-500/10 dark:from-amber-950/20 dark:via-stone-900 dark:to-emerald-950/20 rounded-2xl p-5 border border-amber-500/20 dark:border-amber-500/10 space-y-4">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center shrink-0">
              <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div>
              <h3 class="font-bold text-stone-900 dark:text-stone-100 text-sm flex items-center gap-2">
                AI Cultural Reach Assistant
                <span class="text-[10px] font-semibold px-2 py-0.2 rounded-full bg-amber-500/20 text-amber-500">Meta AI</span>
              </h3>
              <p class="text-xs text-stone-500">Enhance your gathering's discoverability, target audience tags, and community reach.</p>
            </div>
          </div>

          <button type="button" @click="optimize()" :disabled="optimizing"
            class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow transition flex items-center gap-1.5 shrink-0 disabled:opacity-50">
            <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
            <span x-text="optimizing ? 'Analyzing...' : 'Optimize Reach with AI'"></span>
          </button>
        </div>

        {{-- Optimization Suggestions Panel --}}
        <template x-if="optimization">
          <div class="bg-white/80 dark:bg-stone-950/80 rounded-xl p-4 border border-amber-500/20 space-y-3 text-xs animate-in fade-in duration-200">
            <div class="flex items-center justify-between text-stone-700 dark:text-stone-300 font-bold border-b border-stone-200 dark:border-stone-800 pb-2">
              <span class="text-amber-500 font-black">AI Recommendations</span>
              <button type="button" @click="applyAll()" class="text-amber-600 dark:text-amber-400 hover:underline">Apply All Suggestions</button>
            </div>

            {{-- Suggested Title --}}
            <div>
              <span class="text-stone-400 font-semibold uppercase tracking-wider text-[10px] block">Suggested Catchy Cultural Title:</span>
              <div class="flex items-center justify-between gap-2 mt-0.5">
                <span class="font-bold text-stone-800 dark:text-stone-200" x-text="optimization.optimized_title"></span>
                <button type="button" @click="applyTitle()" class="px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold text-[10px]">Use</button>
              </div>
            </div>

            {{-- Suggested Description --}}
            <div>
              <span class="text-stone-400 font-semibold uppercase tracking-wider text-[10px] block">Culturally Enriched Description:</span>
              <div class="flex items-start justify-between gap-2 mt-0.5">
                <p class="text-stone-600 dark:text-stone-400 line-clamp-3 leading-relaxed" x-text="optimization.optimized_description"></p>
                <button type="button" @click="applyDescription()" class="px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold text-[10px] shrink-0">Use</button>
              </div>
            </div>

            {{-- Reach Tags --}}
            <template x-if="optimization.reach_tags && optimization.reach_tags.length > 0">
              <div>
                <span class="text-stone-400 font-semibold uppercase tracking-wider text-[10px] block">Recommended Reach Tags:</span>
                <div class="flex flex-wrap gap-1.5 mt-1">
                  <template x-for="tag in optimization.reach_tags" :key="tag">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20" x-text="'#' + tag"></span>
                  </template>
                </div>
              </div>
            </template>

            {{-- Target Communities & Timing Tips --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pt-2 border-t border-stone-200 dark:border-stone-800 text-[11px] text-stone-500">
              <div>
                <strong class="text-stone-700 dark:text-stone-300">Target Cultural Circles:</strong>
                <span x-text="optimization.target_communities ? optimization.target_communities.join(', ') : 'Heritage Hubs'"></span>
              </div>
              <div>
                <strong class="text-stone-700 dark:text-stone-300">Cultural Timing Advice:</strong>
                <span x-text="optimization.timing_suggestions || 'Evenings & Weekends'"></span>
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
        <div class="flex items-center justify-between">
          <label for="description" class="block text-gray-700 dark:text-stone-300 font-semibold">Description</label>
          <button type="button" @click="showConceptHelper = !showConceptHelper"
            class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:text-amber-500 flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 transition">
            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
            <span x-text="showConceptHelper ? 'Hide Concept Helper' : 'AI Cultural Concept & Description'"></span>
          </button>
        </div>

        {{-- Interactive AI Concept Generator Drawer --}}
        <div x-show="showConceptHelper" x-transition class="p-4 rounded-xl bg-stone-50 dark:bg-stone-800/40 border border-amber-500/20 space-y-3">
          <div class="flex items-start justify-between gap-2">
            <div>
              <p class="text-xs font-bold text-stone-800 dark:text-stone-200 flex items-center gap-1.5">
                <i data-lucide="file-text" class="w-3.5 h-3.5 text-amber-500"></i>
                Draft from Rough Key Points
              </p>
              <p class="text-[11px] text-stone-500 dark:text-stone-400">
                Enter quick notes or an outline of your gathering. The AI will compose an authentic cultural concept and presentation for your community.
              </p>
            </div>
          </div>

          <textarea x-model="conceptPoints" rows="2"
            class="w-full border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-900 dark:text-stone-100 rounded-xl p-3 text-xs focus:ring-2 focus:ring-amber-500 outline-none"
            placeholder="e.g. Elders teaching traditional pottery songs to youth, hands-on clay shaping, blessing of ancestral earth, tea sharing..."></textarea>

          <div class="flex items-center justify-between">
            <span class="text-[10px] text-stone-400 italic">No formal writing needed—just list your points.</span>
            <button type="button" @click="generateConceptDescription()" :disabled="generatingConcept || (!conceptPoints && !formTitle)"
              class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow flex items-center gap-1.5 disabled:opacity-50 transition">
              <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
              <span x-text="generatingConcept ? 'Composing Concept...' : 'Generate Description'"></span>
            </button>
          </div>

          {{-- Suggested Output Preview --}}
          <template x-if="suggestedConcept">
            <div class="mt-3 p-3.5 rounded-xl bg-white dark:bg-stone-900 border border-amber-500/20 space-y-2 text-xs">
              <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 pb-2">
                <div class="flex items-center gap-2">
                  <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold text-[10px]">Concept Preview</span>
                  <span class="text-[11px] font-semibold text-stone-500" x-text="suggestedConcept.tagline"></span>
                </div>
                <div class="flex items-center gap-2">
                  <button type="button" @click="applyConceptDesc()" class="px-2.5 py-1 rounded-lg bg-amber-500 text-white font-bold text-[11px] hover:bg-amber-600 transition">
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
            <input type="date" name="event_date" id="event_date" value="{{ old('event_date') }}" min="{{ date('Y-m-d') }}"
              class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none" required>
            @error('event_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
          </div>

          <!-- Time -->
          <div>
            <label for="event_time" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Time</label>
            <input type="time" name="event_time" id="event_time" value="{{ old('event_time') }}"
              class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none" required>
            @error('event_time')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
          </div>
      </div>

      <!-- Type -->
      <div>
        <label for="type" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Event/Category Type</label>
        <select name="type" id="type" class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none" required>
            <optgroup label="Educational Hub">
                <option value="class">Class / Lesson</option>
                <option value="training">Training Program</option>
                <option value="workshop" selected>Workshop</option>
                <option value="ritual">Traditional Ritual</option>
                <option value="ceremony">Ceremony</option>
            </optgroup>
            <optgroup label="Social & Community">
                <option value="wedding">Wedding / Rights</option>
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
            <label for="location" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Physical Location</label>
            <input type="text" name="location" id="location" value="{{ old('location', 'Online') }}"
            class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
            placeholder="e.g. City Hall, Local Community Center" required>
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
          <div class="relative rounded-2xl overflow-hidden border-2 border-amber-500/40 shadow-lg bg-stone-900 group">
            <img :src="aiFlyerUrl" alt="AI Generated Cultural Flyer" class="w-full h-72 object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col justify-end p-5">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-500 text-stone-950 shadow">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> AI Cultural Flyer
                  </span>
                  <p class="text-xs text-stone-300 mt-1">Authentic cultural artwork generated for this event.</p>
                </div>
                <div class="flex items-center gap-2">
                  <button type="button" @click="generateFlyer()" :disabled="generatingFlyer"
                    class="px-3.5 py-1.5 rounded-xl bg-white/20 hover:bg-white/30 backdrop-blur text-white text-xs font-bold transition flex items-center gap-1.5 disabled:opacity-50">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="generatingFlyer ? 'animate-spin' : ''"></i>
                    <span x-text="generatingFlyer ? 'Regenerating...' : 'Regenerate'"></span>
                  </button>
                  <button type="button" @click="clearFlyer()"
                    class="px-3.5 py-1.5 rounded-xl bg-red-600/80 hover:bg-red-600 text-white text-xs font-bold transition flex items-center gap-1.5">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>Remove</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Manual File Upload Box (shown when no AI flyer or user wants to upload) --}}
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
                Generates an authentic cultural poster tailored to your event title, category, and heritage context.
              </p>
            </div>
            <button type="button" @click="generateFlyer()" :disabled="generatingFlyer || !formTitle"
              class="px-4 py-2 rounded-xl bg-stone-900 dark:bg-stone-100 text-white dark:text-stone-900 hover:bg-amber-600 dark:hover:bg-amber-400 dark:hover:text-stone-950 font-bold text-xs shadow flex items-center gap-2 shrink-0 disabled:opacity-50 transition">
              <i data-lucide="wand-2" class="w-3.5 h-3.5" :class="generatingFlyer ? 'animate-spin' : ''"></i>
              <span x-text="generatingFlyer ? 'Designing Flyer...' : 'Generate AI Cultural Flyer'"></span>
            </button>
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
  
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
      lucide.createIcons();

      document.addEventListener('alpine:init', () => {
          Alpine.data('eventOptimizer', () => ({
              formTitle: "{{ old('title', '') }}",
              formDesc: "{{ old('description', '') }}",
              optimizing: false,
              optimization: null,
              reachTags: [],
              aiMetadata: null,

              // Cultural Concept & Description Generator
              showConceptHelper: false,
              conceptPoints: '',
              generatingConcept: false,
              suggestedConcept: null,

              // AI Flyer Generator
              generatingFlyer: false,
              aiFlyerPath: '',
              aiFlyerUrl: '',

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

              async generateFlyer() {
                  if (!this.formTitle) {
                      alert('Please enter an Event Title first before generating an AI flyer.');
                      return;
                  }

                  this.generatingFlyer = true;
                  try {
                      const res = await fetch("{{ route('events.ai-generate-flyer') }}", {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/json',
                              'X-CSRF-TOKEN': "{{ csrf_token() }}"
                          },
                          body: JSON.stringify({
                              title: this.formTitle,
                              description: this.formDesc || this.formTitle,
                              type: document.getElementById('type') ? document.getElementById('type').value : 'Cultural Gathering',
                              cultural_context: (this.reachTags && this.reachTags.length > 0) ? this.reachTags.join(', ') : ''
                          })
                      });
                      const data = await res.json();
                      if (data.success && (data.image_path || data.flyer_path)) {
                          this.aiFlyerPath = data.image_path || data.flyer_path;
                          this.aiFlyerUrl = data.image_url || data.flyer_url;
                          // Clear manual file input preview if any
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

              clearFlyer() {
                  this.aiFlyerPath = '';
                  this.aiFlyerUrl = '';
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
