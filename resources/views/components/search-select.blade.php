{{--
    model:    parent Alpine property to bind, e.g. "countryId"
    source:   JS expression returning [{id, name, flag?}]
    disabled: JS expression
--}}
@props([
    'model',
    'source',
    'inputId',
    'placeholder' => 'Search...',
    'icon' => 'search',
    'disabled' => 'false',
])

{{-- Searchable, keyboard-friendly dropdown. Requires the searchSelect() Alpine helper. --}}
<div x-data="searchSelect({ source: () => {{ $source }} })" x-modelable="value" x-model="{{ $model }}"
    @click.outside="close()" {{ $attributes->merge(['class' => 'relative']) }}>
    <div class="relative group">
        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-400 group-focus-within:text-amber-500 transition-colors pointer-events-none">
            <i data-lucide="{{ $icon }}" class="w-4 h-4 sm:w-5 sm:h-5"></i>
        </span>
        <input type="text" id="{{ $inputId }}" x-ref="input" x-model="query" autocomplete="off"
            role="combobox" aria-autocomplete="list" :aria-expanded="open.toString()"
            placeholder="{{ $placeholder }}" :disabled="{{ $disabled }}"
            @focus="openList()" @click="openList()" @input="open = true; active = 0"
            @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)"
            @keydown.enter.prevent="choose(filtered[active])" @keydown.escape.prevent="close()"
            @keydown.tab="close()"
            class="pl-10 pr-14 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500 disabled:opacity-50 disabled:cursor-not-allowed transition">

        {{-- Trailing Actions: Clear and Amber Chevron --}}
        <div class="absolute inset-y-0 right-3 flex items-center gap-1.5 z-10">
            {{-- Clear Selection Button --}}
            <button type="button" x-show="value" x-cloak @click.stop="clear()" tabindex="-1" aria-label="Clear selection"
                class="flex items-center justify-center w-5 h-5 rounded-full hover:bg-stone-100 dark:hover:bg-stone-700 text-stone-400 hover:text-amber-600 transition text-sm leading-none">&times;</button>

            {{-- Chevron Indicator (Rotates when open) --}}
            <button type="button" 
                @click.stop="toggleList()"
                tabindex="-1"
                aria-label="Toggle dropdown"
                class="text-stone-400 hover:text-amber-500 transition-transform duration-200 flex items-center justify-center cursor-pointer"
                :class="open ? 'rotate-180 text-amber-500' : ''">
                <i data-lucide="chevron-down" class="w-4 h-4 text-amber-500"></i>
            </button>
        </div>
    </div>

    <ul x-show="open" x-cloak x-transition.opacity.duration.150ms role="listbox"
        class="absolute z-40 mt-1.5 w-full max-h-60 overflow-auto modern-scrollbar rounded-2xl border border-stone-200/90 dark:border-stone-700 bg-white/95 dark:bg-stone-900/95 backdrop-blur-md shadow-xl p-1.5 space-y-0.5">
        <template x-for="(item, index) in filtered" :key="item.id">
            <li role="option" :aria-selected="(String(item.id) === String(value)).toString()"
                @mousedown.prevent="choose(item)" @mouseenter="active = index"
                :class="{
                    'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-semibold': String(item.id) === String(value),
                    'bg-amber-50/70 dark:bg-stone-800/80 text-amber-600 dark:text-amber-400': index === active && String(item.id) !== String(value),
                    'text-stone-700 dark:text-stone-200': index !== active && String(item.id) !== String(value)
                }"
                class="px-3.5 py-2.5 rounded-xl cursor-pointer text-xs sm:text-sm flex items-center gap-2.5 transition">
                <span x-show="item.flag" x-text="item.flag" class="text-base shrink-0"></span>
                <span x-text="item.name" class="truncate flex-1"></span>
                <span x-show="String(item.id) === String(value)" class="ml-auto text-amber-500 shrink-0 font-bold">&#10003;</span>
            </li>
        </template>
        <li x-show="filtered.length === 0" class="px-4 py-3 text-xs sm:text-sm text-stone-400 text-center">No matches found</li>
    </ul>
</div>
