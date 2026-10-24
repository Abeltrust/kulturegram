@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-16">
    {{-- Header Banner --}}
    <div class="mb-10 text-center sm:text-left border-b border-stone-200 dark:border-stone-800 pb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/40 text-amber-700 dark:text-amber-400 text-xs font-bold mb-4">
            <i data-lucide="scale" class="w-4 h-4"></i>
            Community Standards & Ethics
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-stone-900 dark:text-white tracking-tight">
            Terms of Service
        </h1>
        <p class="text-stone-600 dark:text-stone-400 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
            Kulturegram is a dedicated space for cultural reverence, historical accuracy, authentic connection, and respectful community dialogue.
        </p>
        <p class="text-xs text-stone-400 dark:text-stone-500 mt-3">
            Last Updated: October 2026
        </p>
    </div>

    {{-- Content Sections --}}
    <div class="space-y-8 text-stone-700 dark:text-stone-300 leading-relaxed text-sm sm:text-base">

        <section class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-100 dark:border-stone-800 shadow-sm space-y-4">
            <h2 class="text-xl font-bold text-stone-900 dark:text-white flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5 text-amber-500"></i>
                1. Respectful Conduct & Heritage Safeguarding
            </h2>
            <p>
                By participating in Kulturegram, you agree to treat diverse traditions, indigenous practices, historical accounts, and cultural expressions with dignity and respect. Defamation, hate speech, cultural appropriation with commercial malice, harassment, and intentional misinformation are strictly forbidden.
            </p>
        </section>

        <section class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-100 dark:border-stone-800 shadow-sm space-y-4">
            <h2 class="text-xl font-bold text-stone-900 dark:text-white flex items-center gap-2">
                <i data-lucide="feather" class="w-5 h-5 text-amber-500"></i>
                2. Authorship & Intellectual Integrity
            </h2>
            <p>
                When submitting stories to the Cultural Hub or posting media in communities, you confirm that you are the rightful creator or have acquired proper consent from custodians, lineage elders, or institutions. Plagiarism or deceptive claims of cultural authority will result in content removal and account suspension.
            </p>
        </section>

        <section class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-100 dark:border-stone-800 shadow-sm space-y-4">
            <h2 class="text-xl font-bold text-stone-900 dark:text-white flex items-center gap-2">
                <i data-lucide="video" class="w-5 h-5 text-amber-500"></i>
                3. Gatherings, Workshops & Live Broadcasts
            </h2>
            <p>
                Organizers of cultural workshops and virtual masterclasses are responsible for delivering accurate instructional content. Recordings of virtual classrooms or live streams may not be republished without the explicit consent of the organizers and attendees.
            </p>
        </section>

        <section class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-100 dark:border-stone-800 shadow-sm space-y-4">
            <h2 class="text-xl font-bold text-stone-900 dark:text-white flex items-center gap-2">
                <i data-lucide="shield" class="w-5 h-5 text-amber-500"></i>
                4. Moderation & Administrative Oversight
            </h2>
            <p>
                The Kulturegram curation and moderation team reserves the right to review submissions to the public Cultural Hub to verify historical context, categorization, and authenticity before featuring or approving stories for worldwide distribution.
            </p>
        </section>

    </div>
</div>
@endsection
