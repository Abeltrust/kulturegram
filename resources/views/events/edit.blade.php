@extends('layouts.app')

@section('title', 'Edit Event - ' . $event->title)

@section('content')
  <div class="max-w-3xl mx-auto bg-white dark:bg-stone-900 border border-stone-100 dark:border-stone-800 shadow-lg rounded-2xl p-6 sm:p-8 mt-10">
    <div class="flex items-center justify-between mb-8">
      <div>
        <a href="{{ route('events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 hover:underline uppercase tracking-widest mb-2">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Event
        </a>
        <h2 class="text-3xl font-bold text-gray-800 dark:text-stone-100">
          Edit Event
        </h2>
      </div>
      <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently cancel and delete this event?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-4 py-2 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 dark:hover:bg-red-900/50 text-red-600 font-semibold text-sm rounded-xl transition border border-red-200 dark:border-red-900 flex items-center gap-2">
          <i data-lucide="trash-2" class="w-4 h-4"></i>
          Delete
        </button>
      </form>
    </div>

    <form action="{{ route('events.update', $event) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
      @csrf
      @method('PUT')

      <!-- Title -->
      <div>
        <label for="title" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Event Title</label>
        <input type="text" name="title" id="title" value="{{ old('title', $event->title) }}"
          class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
          placeholder="What is this event about?" required>
        @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <!-- Description -->
      <div>
        <label for="description" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Description</label>
        <textarea name="description" id="description" rows="4"
          class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
          placeholder="Provide more details..." required>{{ old('description', $event->description) }}</textarea>
        @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Date -->
          <div>
            <label for="event_date" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Date</label>
            <input type="date" name="event_date" id="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}"
              class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none" required>
            @error('event_date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
          </div>

          <!-- Time -->
          <div>
            <label for="event_time" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Time</label>
            <input type="time" name="event_time" id="event_time" value="{{ old('event_time', $event->event_time) }}"
              class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none" required>
            @error('event_time')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
          </div>
      </div>

      <!-- Type -->
      <div>
        <label for="type" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Category Type</label>
        <select name="type" id="type" class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none" required>
            <optgroup label="Educational Hub">
                <option value="class" {{ old('type', $event->type) == 'class' ? 'selected' : '' }}>Class / Lesson</option>
                <option value="training" {{ old('type', $event->type) == 'training' ? 'selected' : '' }}>Training Program</option>
                <option value="workshop" {{ old('type', $event->type) == 'workshop' ? 'selected' : '' }}>Workshop</option>
                <option value="ritual" {{ old('type', $event->type) == 'ritual' ? 'selected' : '' }}>Traditional Ritual</option>
                <option value="wedding" {{ old('type', $event->type) == 'wedding' ? 'selected' : '' }}>Cultural Wedding</option>
                <option value="ceremony" {{ old('type', $event->type) == 'ceremony' ? 'selected' : '' }}>Ceremony</option>
            </optgroup>
            <optgroup label="General Events">
                <option value="cultural" {{ old('type', $event->type) == 'cultural' ? 'selected' : '' }}>Cultural Gathering</option>
                <option value="meetup" {{ old('type', $event->type) == 'meetup' ? 'selected' : '' }}>Community Meetup</option>
                <option value="celebration" {{ old('type', $event->type) == 'celebration' ? 'selected' : '' }}>Celebration</option>
            </optgroup>
        </select>
        @error('type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <!-- Location -->
      <div>
        <label for="location" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Location / Venue</label>
        <input type="text" name="location" id="location" value="{{ old('location', $event->location) }}"
          class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
          placeholder="e.g. Lagos Cultural Center or Online" required>
        @error('location')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Price -->
          <div>
            <label for="price" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Ticket Price ($)</label>
            <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price', $event->price) }}"
              class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
              placeholder="0 for free">
            @error('price')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
          </div>

          <!-- Max Attendees -->
          <div>
            <label for="max_attendees" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Max Attendees</label>
            <input type="number" min="1" name="max_attendees" id="max_attendees" value="{{ old('max_attendees', $event->max_attendees) }}"
              class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:outline-none"
              placeholder="Leave blank for unlimited">
            @error('max_attendees')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
          </div>
      </div>

      <!-- Accept Contributions -->
      <div class="flex items-center gap-3 p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/50">
          <input type="checkbox" name="accepts_contributions" value="1" id="accepts_contributions" class="w-5 h-5 rounded text-amber-500 focus:ring-amber-500" {{ old('accepts_contributions', $event->accepts_contributions) ? 'checked' : '' }}>
          <label for="accepts_contributions" class="text-sm font-semibold text-stone-800 dark:text-stone-200">
              Allow attendees to send optional cultural contributions/gifts
          </label>
      </div>

      <!-- Current Image / Replace Image -->
      <div>
        <label for="image" class="block text-gray-700 dark:text-stone-300 font-semibold mb-2">Event Cover Image</label>
        @if($event->image)
          <div class="mb-3 relative w-48 h-32 rounded-xl overflow-hidden border border-stone-200 dark:border-stone-700">
            <img src="{{ asset('storage/' . $event->image) }}" class="w-full h-full object-cover">
          </div>
        @endif
        <input type="file" name="image" id="image" accept="image/*"
          class="w-full border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-xl px-4 py-3">
        @error('image')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="flex items-center justify-end gap-4 pt-4">
        <a href="{{ route('events.show', $event) }}" class="px-6 py-3 rounded-xl border border-stone-300 dark:border-stone-700 font-bold text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 transition">
          Cancel
        </a>
        <button type="submit"
          class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold rounded-xl shadow-lg hover:from-amber-600 hover:to-orange-700 transition">
          Update Event
        </button>
      </div>
    </form>
  </div>
@endsection
