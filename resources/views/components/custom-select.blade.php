{{--
    name:        Input name for standard form submission (e.g. "chapter")
    id:          Optional element ID
    options:     Array of items: ['A', 'B'] or ['val1' => 'Label 1'] or [['id' => 1, 'name' => 'Name', 'flag' => '...']]
    value:       Initial selected value
    selected:    Alias for value
    placeholder: Dropdown placeholder
    icon:        Lucide icon name (e.g. 'bookmark', 'languages', 'globe')
    searchable:  Boolean to force search filter on or off (defaults to true if > 5 options)
    disabled:    Boolean or JS expression
    required:    Boolean
    inputClass:  Extra classes for input
--}}
@props([
    'name' => null,
    'id' => null,
    'options' => [],
    'value' => null,
    'selected' => null,
    'placeholder' => 'Select an option...',
    'icon' => null,
    'searchable' => null,
    'disabled' => false,
    'required' => false,
    'inputClass' => '',
])

@php
    $inputId = $id ?: ($name ? 'kg-select-' . Str::slug($name) . '-' . Str::random(4) : 'kg-select-' . Str::random(6));
    $initialVal = old($name, $selected ?? $value ?? '');

    // Normalize options for passing to Alpine JS
    $normalizedOptions = [];
    if (is_array($options) || $options instanceof \Illuminate\Support\Collection) {
        foreach ($options as $key => $option) {
            if (is_array($option)) {
                $normalizedOptions[] = [
                    'id' => (string)($option['id'] ?? $option['value'] ?? $key),
                    'name' => (string)($option['name'] ?? $option['label'] ?? $option['title'] ?? $key),
                    'flag' => $option['flag'] ?? null,
                ];
            } elseif (is_object($option)) {
                $normalizedOptions[] = [
                    'id' => (string)($option->id ?? $option->value ?? $key),
                    'name' => (string)($option->name ?? $option->label ?? $option->title ?? $key),
                    'flag' => $option->flag ?? null,
                ];
            } else {
                $optId = is_numeric($key) ? (string)$option : (string)$key;
                $normalizedOptions[] = [
                    'id' => $optId,
                    'name' => (string)$option,
                    'flag' => null,
                ];
            }
        }
    }
    $isSearchable = $searchable !== null ? ($searchable ? 'true' : 'false') : (count($normalizedOptions) > 5 ? 'true' : 'false');
@endphp

<div x-data="customSelect({
        options: @js($normalizedOptions),
        initialValue: @js((string)$initialVal),
        searchable: {{ $isSearchable }}
    })"
    x-modelable="value"
    @click.outside="close()"
    {{ $attributes->merge(['class' => 'relative w-full']) }}>

    {{-- Hidden form input for standard POST/GET submissions --}}
    @if($name)
        <input type="hidden" id="{{ $inputId }}" name="{{ $name }}" x-ref="hiddenInput" :value="value" {{ $required ? 'required' : '' }}>
    @endif

    <div class="relative group">
        {{-- Leading Icon --}}
        @if($icon)
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-400 group-focus-within:text-amber-500 transition-colors pointer-events-none z-10">
                <i data-lucide="{{ $icon }}" class="w-4 h-4 sm:w-4.5 sm:h-4.5"></i>
            </span>
        @endif

        {{-- Visible Combobox Input --}}
        <input type="text"
            id="{{ $inputId }}-display"
            x-ref="searchInput"
            x-model="query"
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            :aria-expanded="open.toString()"
            placeholder="{{ $placeholder }}"
            :disabled="{{ $disabled ? 'true' : 'false' }}"
            :readonly="!searchable"
            @focus="openList()"
            @click="toggleList()"
            @input="if(searchable){ open = true; active = 0; }"
            @keydown.arrow-down.prevent="move(1)"
            @keydown.arrow-up.prevent="move(-1)"
            @keydown.enter.prevent="if(open && filtered[active]){ choose(filtered[active]); } else { openList(); }"
            @keydown.escape.prevent="close()"
            @keydown.tab="close()"
            class="{{ $icon ? 'pl-10' : 'pl-3.5' }} pr-14 py-2.5 sm:py-3 block w-full stnd-input text-xs sm:text-sm text-stone-900 dark:text-stone-100 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-xl hover:border-amber-400 dark:hover:border-stone-600 focus:border-amber-500 focus:outline-none transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed {{ $inputClass }}">

        {{-- Trailing Actions: Clear and Amber Chevron --}}
        <div class="absolute inset-y-0 right-3 flex items-center gap-1.5 z-10">
            {{-- Clear Selection Button --}}
            <button type="button" 
                x-show="value" 
                x-cloak 
                @click.stop="clear()" 
                tabindex="-1" 
                aria-label="Clear selection"
                class="flex items-center justify-center w-5 h-5 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-400 hover:text-amber-600 dark:hover:text-amber-400 transition text-sm leading-none">&times;</button>

            {{-- Chevron Indicator (Rotates 180deg when open) --}}
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

    {{-- Floating Menu Card (Registration Dropdown Look & Feel) --}}
    <ul x-show="open" 
        x-cloak 
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        role="listbox"
        class="absolute z-50 mt-1.5 w-full max-h-60 overflow-auto modern-scrollbar rounded-2xl border border-stone-200/90 dark:border-stone-700 bg-white/98 dark:bg-stone-900/98 backdrop-blur-md shadow-xl p-1.5 space-y-0.5">
        
        <template x-for="(item, index) in filtered" :key="item.id">
            <li role="option" 
                :aria-selected="(String(item.id) === String(value)).toString()"
                @mousedown.prevent="choose(item)" 
                @mouseenter="active = index"
                :class="{
                    'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-semibold': String(item.id) === String(value),
                    'bg-amber-50/70 dark:bg-stone-800/80 text-amber-600 dark:text-amber-400': index === active && String(item.id) !== String(value),
                    'text-stone-700 dark:text-stone-200': index !== active && String(item.id) !== String(value)
                }"
                class="px-3.5 py-2.5 rounded-xl cursor-pointer text-xs sm:text-sm flex items-center gap-2.5 transition select-none">
                <span x-show="item.flag" x-text="item.flag" class="text-base shrink-0"></span>
                <span x-text="item.name" class="truncate flex-1"></span>
                <span x-show="String(item.id) === String(value)" class="ml-auto text-amber-500 shrink-0 font-bold">&#10003;</span>
            </li>
        </template>
        
        <li x-show="filtered.length === 0" class="px-4 py-3 text-xs sm:text-sm text-stone-400 text-center select-none">
            No matches found
        </li>
    </ul>
</div>
