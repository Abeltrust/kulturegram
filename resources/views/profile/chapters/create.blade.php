@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 sm:p-8 border border-stone-100 dark:border-stone-800 shadow-sm">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-stone-100 dark:border-stone-800">
            <a href="{{ route('profile.show') }}" class="w-10 h-10 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center text-stone-600 dark:text-stone-300 hover:bg-stone-200 transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-stone-900 dark:text-white">Record Life Chapter</h1>
                <p class="text-xs sm:text-sm text-stone-500">Document a defining era, cultural milestone, or geographical chapter of your life.</p>
            </div>
        </div>

        <form action="{{ route('profile.chapters.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-2">
                    Chapter Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" required placeholder="e.g., Childhood in Kano & Apprenticeship"
                    class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-2xl text-sm font-semibold text-stone-800 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-2">
                        Time Period / Era
                    </label>
                    <input type="text" name="period" placeholder="e.g., 2005 - 2012 or Youth"
                        class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-2xl text-sm font-semibold text-stone-800 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-2">
                        Location / Cultural Territory
                    </label>
                    <input type="text" name="location" placeholder="e.g., Jos, Plateau State, Nigeria"
                        class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-2xl text-sm font-semibold text-stone-800 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-2">
                    Narrative & Cultural Significance
                </label>
                <textarea name="description" rows="4" placeholder="Describe the traditions learned, community mentors, lessons, or pivotal moments during this chapter..."
                    class="w-full px-4 py-3 bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-2xl text-sm text-stone-800 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none leading-relaxed"></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('profile.show') }}" class="px-5 py-2.5 rounded-xl text-stone-600 dark:text-stone-400 font-bold text-sm hover:bg-stone-100 dark:hover:bg-stone-800 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition active:scale-95">
                    Save Life Chapter
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
