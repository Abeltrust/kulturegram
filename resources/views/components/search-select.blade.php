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
    <div class="relative">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400 pointer-events-none">
            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
        </span>
        <input type="text" id="{{ $inputId }}" x-ref="input" x-model="query" autocomplete="off"
            role="combobox" aria-autocomplete="list" :aria-expanded="open.toString()"
            placeholder="{{ $placeholder }}" :disabled="{{ $disabled }}"
            @focus="openList()" @click="openList()" @input="open = true; active = 0"
            @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)"
            @keydown.enter.prevent="choose(filtered[active])" @keydown.escape.prevent="close()"
            @keydown.tab="close()"
            class="pl-10 pr-10 py-3 block w-full stnd-input px-4 dark:bg-stone-800 dark:border-stone-700 dark:text-stone-200 dark:placeholder-stone-500 disabled:opacity-50 disabled:cursor-not-allowed transition">
        <button type="button" x-show="value" x-cloak @click="clear()" tabindex="-1" aria-label="Clear selection"
            class="absolute inset-y-0 right-3 flex items-center text-stone-400 hover:text-amber-600 text-lg leading-none">&times;</button>
    </div>

    <ul x-show="open" x-cloak x-transition.opacity.duration.150ms role="listbox"
        class="absolute z-30 mt-1 w-full max-h-60 overflow-auto modern-scrollbar rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 shadow-xl py-1">
        <template x-for="(item, index) in filtered" :key="item.id">
            <li role="option" :aria-selected="(String(item.id) === String(value)).toString()"
                @mousedown.prevent="choose(item)" @mouseenter="active = index"
                :class="index === active ? 'bg-amber-50 dark:bg-stone-700 text-amber-700 dark:text-amber-400' : 'text-stone-700 dark:text-stone-200'"
                class="px-4 py-2 cursor-pointer text-sm flex items-center gap-2">
                <span x-show="item.flag" x-text="item.flag" class="text-base"></span>
                <span x-text="item.name" class="truncate"></span>
                <span x-show="String(item.id) === String(value)" class="ml-auto text-amber-500">&#10003;</span>
            </li>
        </template>
        <li x-show="filtered.length === 0" class="px-4 py-2 text-sm text-stone-400">No matches found</li>
    </ul>
</div>
