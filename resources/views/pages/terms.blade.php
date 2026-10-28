@extends('layouts.app')

@section('title', 'Terms of Service - Kulturegram')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 sm:py-16" x-data="{ activeSection: 'sec-1', showSummary: false }">
    {{-- Header Banner --}}
    <div class="mb-10 text-center sm:text-left border-b border-stone-200 dark:border-stone-800 pb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/40 text-amber-700 dark:text-amber-400 text-xs font-bold mb-4">
            <i data-lucide="scale" class="w-4 h-4"></i>
            Community Standards & Platform Terms
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-amber-500 tracking-tight">
            Terms of Service & Community Agreement
        </h1>
        <p class="text-stone-600 dark:text-stone-400 text-sm sm:text-base mt-3 max-w-3xl leading-relaxed">
            Welcome to Kulturegram, the social media network celebrating cultural heritage, storytelling, and authentic human connection. By creating an account, browsing, or using our services, you enter into a legally binding agreement to uphold these Terms and our Community Standards.
        </p>
        <div class="flex flex-wrap items-center justify-between gap-4 mt-6 pt-4 border-t border-stone-100 dark:border-stone-800/60 text-xs text-stone-500 dark:text-stone-400">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-amber-500"></i>
                    Effective: October 2026
                </span>
                <span>&bull;</span>
                <span class="inline-flex items-center gap-1">
                    <i data-lucide="globe" class="w-3.5 h-3.5 text-amber-500"></i>
                    Applies Globally
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 hover:bg-stone-50 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-300 font-medium transition active:scale-95">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    Print Terms
                </button>
                <a href="{{ route('privacy') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 font-medium hover:bg-amber-100 dark:hover:bg-amber-900/40 transition">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    Privacy Policy
                </a>
            </div>
        </div>
    </div>

    {{-- The Gist: Plain-English Executive Summary Cards --}}
    <div class="mb-12">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl sm:text-2xl font-bold text-amber-500 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-5 h-5 text-amber-500"></i>
                The Gist: Plain-English Highlights (TL;DR)
            </h2>
            <span class="text-xs text-stone-400 font-medium">Quick reference guide</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3">
                    <i data-lucide="award" class="w-5 h-5"></i>
                </div>
                <h3 class="text-base font-bold text-amber-500 mb-1.5">You Own Your Content</h3>
                <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">
                    Your photos, folklore, stories, recipes, and videos remain 100% yours. You grant Kulturegram only the basic license required to host and display your posts.
                </p>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3">
                    <i data-lucide="heart-handshake" class="w-5 h-5"></i>
                </div>
                <h3 class="text-base font-bold text-amber-500 mb-1.5">Cultural Dignity First</h3>
                <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">
                    Zero tolerance for hate speech, xenophobia, ethnic mockery, harassment, or commercial exploitation of sacred traditions and ancestral customs.
                </p>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3">
                    <i data-lucide="lock" class="w-5 h-5"></i>
                </div>
                <h3 class="text-base font-bold text-amber-500 mb-1.5">Locked-In Circles</h3>
                <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">
                    Direct messaging and reposting are reserved for your locked-in connections. You always have full sovereignty to untag yourself from any post.
                </p>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200/80 dark:border-stone-800 shadow-sm">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3">
                    <i data-lucide="shield" class="w-5 h-5"></i>
                </div>
                <h3 class="text-base font-bold text-amber-500 mb-1.5">Authentic Community</h3>
                <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">
                    Real people, genuine roots. No bots, scrapers, impersonation, scams, or predatory algorithms. Your personal roots data is never sold to ad brokers.
                </p>
            </div>
        </div>
    </div>

    {{-- Main Layout: Navigation Sidebar + Detailed Clauses --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Sticky Navigation Table of Contents --}}
        <aside class="hidden lg:block lg:col-span-4 sticky top-24 space-y-4">
            <div class="bg-white dark:bg-stone-900 p-5 rounded-2xl border border-stone-200/80 dark:border-stone-800 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-500 mb-3 flex items-center gap-1.5">
                    <i data-lucide="list" class="w-4 h-4"></i>
                    Table of Contents
                </h3>
                <nav class="space-y-1 text-xs">
                    <a href="#sec-1" class="block py-1.5 px-2.5 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-amber-50 dark:hover:bg-stone-800 hover:text-amber-600 transition">1. Acceptance & Eligibility</a>
                    <a href="#sec-2" class="block py-1.5 px-2.5 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-amber-50 dark:hover:bg-stone-800 hover:text-amber-600 transition">2. Account & Identity Integrity</a>
                    <a href="#sec-3" class="block py-1.5 px-2.5 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-amber-50 dark:hover:bg-stone-800 hover:text-amber-600 transition">3. Locked-In Circles, Vibing & Tagging</a>
                    <a href="#sec-4" class="block py-1.5 px-2.5 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-amber-50 dark:hover:bg-stone-800 hover:text-amber-600 transition">4. Cultural Dignity & Sacred Heritage</a>
                    <a href="#sec-5" class="block py-1.5 px-2.5 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-amber-50 dark:hover:bg-stone-800 hover:text-amber-600 transition">5. Content Ownership & License</a>
                    <a href="#sec-6" class="block py-1.5 px-2.5 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-amber-50 dark:hover:bg-stone-800 hover:text-amber-600 transition">6. Classes, Workshops & Live Hubs</a>
                    <a href="#sec-7" class="block py-1.5 px-2.5 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-amber-50 dark:hover:bg-stone-800 hover:text-amber-600 transition">7. AI Features & Discovery Transparency</a>
                    <a href="#sec-8" class="block py-1.5 px-2.5 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-amber-50 dark:hover:bg-stone-800 hover:text-amber-600 transition">8. Prohibited Conduct & Safety Standards</a>
                    <a href="#sec-9" class="block py-1.5 px-2.5 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-amber-50 dark:hover:bg-stone-800 hover:text-amber-600 transition">9. Moderation, Reports & Enforcement</a>
                    <a href="#sec-10" class="block py-1.5 px-2.5 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-amber-50 dark:hover:bg-stone-800 hover:text-amber-600 transition">10. Disclaimers & Liability Limitations</a>
                    <a href="#sec-11" class="block py-1.5 px-2.5 rounded-lg text-stone-600 dark:text-stone-400 hover:bg-amber-50 dark:hover:bg-stone-800 hover:text-amber-600 transition">11. Disputes, Termination & Updates</a>
                </nav>
            </div>

            <div class="bg-amber-50/60 dark:bg-amber-950/20 p-5 rounded-2xl border border-amber-200/60 dark:border-amber-800/40">
                <h4 class="text-xs font-bold text-amber-700 dark:text-amber-400 mb-1 flex items-center gap-1.5">
                    <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                    Questions or Reports?
                </h4>
                <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed mb-3">
                    Our community council is committed to fair resolutions and sacred cultural protections.
                </p>
                <a href="mailto:support@kulturegram.com" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                    Contact Legal & Safety &rarr;
                </a>
            </div>
        </aside>

        {{-- Detailed Legal Articles --}}
        <main class="lg:col-span-8 space-y-8 text-stone-700 dark:text-stone-300 leading-relaxed text-sm sm:text-base">

            {{-- 1. Acceptance & User Eligibility --}}
            <section id="sec-1" class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4 scroll-mt-24">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                        1
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-amber-500">1. Acceptance & User Eligibility</h2>
                </div>
                <p>
                    These Terms of Service ("Terms") constitute a legally binding contract between you and Kulturegram ("we", "us", or "our"). By registering for an account, tapping "Create account", downloading our app, or using any part of the Kulturegram platform, you affirm that:
                </p>
                <ul class="list-disc list-inside space-y-2 text-stone-600 dark:text-stone-400 pl-2 text-sm">
                    <li><strong class="text-stone-800 dark:text-stone-200">Minimum Age:</strong> You are at least 13 years of age (or the legal age required in your country to consent to online services without parental permission).</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">Legal Capacity:</strong> You possess the legal right and capacity to enter into these Terms under the laws of your jurisdiction.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">Prior Standing:</strong> You have not been previously banned, permanently suspended, or restricted from Kulturegram for violating safety or community conduct rules.</li>
                </ul>
            </section>

            {{-- 2. Account & Identity Integrity --}}
            <section id="sec-2" class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4 scroll-mt-24">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                        2
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-amber-500">2. Account Creation & Identity Integrity</h2>
                </div>
                <p>
                    Kulturegram thrives on genuine human connections and authentic cultural lineage. When registering:
                </p>
                <ul class="list-disc list-inside space-y-2 text-stone-600 dark:text-stone-400 pl-2 text-sm">
                    <li><strong class="text-stone-800 dark:text-stone-200">Accurate Profile Info:</strong> You agree to provide accurate, current registration details, including your authentic name or verified cultural moniker.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">Cultural Roots & Dialects:</strong> Specifying your ancestral country, state, local government area, and spoken languages is voluntary. When provided, you agree to represent your connections truthfully.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">Account Security:</strong> You are solely responsible for maintaining the confidentiality of your login credentials and for all activities conducted under your account. Notify us immediately if you suspect unauthorized access.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">No Account Transfer or Sale:</strong> You may not sell, lease, transfer, or license your account, username, or verified badges to any third party.</li>
                </ul>
            </section>

            {{-- 3. Locked-In Circles, Vibing & Tagging --}}
            <section id="sec-3" class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4 scroll-mt-24">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                        3
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-amber-500">3. Social Interactions (Locking-In, Vibing, Messaging & Tagging)</h2>
                </div>
                <p>
                    Unlike traditional social networks driven by unsolicited reach, Kulturegram champions consent-based social interactions:
                </p>
                <div class="space-y-3 pt-1 text-sm text-stone-600 dark:text-stone-400">
                    <div class="p-3.5 rounded-xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60">
                        <strong class="text-stone-800 dark:text-stone-200 block text-sm mb-1">A. The "Locked-In" Mutual Agreement</strong>
                        Direct messaging and deep sharing are exclusively reserved for members who are locked-in with each other. This creates a safe, troll-free sanctuary. You can unlock or unfriend anyone at any time with immediate effect.
                    </div>
                    <div class="p-3.5 rounded-xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60">
                        <strong class="text-stone-800 dark:text-stone-200 block text-sm mb-1">B. "Vibing" With Profiles</strong>
                        Vibing allows you to express mutual admiration and receive occasional, curated timeline updates from a member without granting direct messaging or private circle access.
                    </div>
                    <div class="p-3.5 rounded-xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200/60 dark:border-stone-700/60">
                        <strong class="text-stone-800 dark:text-stone-200 block text-sm mb-1">C. Tagging & Sharing Sovereignty</strong>
                        You may only tag members who have locked-in with you or are participating in the same cultural group. Every tagged member possesses the absolute right to remove their tag from any post at any time without requiring the author's approval.
                    </div>
                </div>
            </section>

            {{-- 4. Cultural Stewardship & Heritage Respect --}}
            <section id="sec-4" class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4 scroll-mt-24">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                        4
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-amber-500">4. Cultural Stewardship & Heritage Dignity</h2>
                </div>
                <p>
                    Kulturegram is built upon profound respect for indigenous traditions, folklore, spiritual ceremonies, languages, and historical memory:
                </p>
                <ul class="list-disc list-inside space-y-2 text-stone-600 dark:text-stone-400 pl-2 text-sm">
                    <li><strong class="text-stone-800 dark:text-stone-200">Zero Tolerance for Cultural Mockery:</strong> Ridiculing, parodying, or disparaging ancestral traditions, sacred rites, traditional garments, or linguistic dialects is grounds for immediate suspension.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">Protection of Restricted Ceremonies:</strong> Certain ancestral customs, spiritual initiations, and sacred relics are guarded by indigenous elders and customary laws. You agree never to publish secret, closed, or restricted ceremonial rites without explicit authorization from the legitimate traditional custodians.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">Historical Honesty:</strong> Intentionally fabricating historical events, falsely asserting tribal lineage for clout, or engaging in revisionist propaganda that harms communal peace is strictly prohibited.</li>
                </ul>
            </section>

            {{-- 5. Content Ownership & License --}}
            <section id="sec-5" class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4 scroll-mt-24">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                        5
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-amber-500">5. Content Ownership, Intellectual Property & Platform License</h2>
                </div>
                <p>
                    We hold a creator-first philosophy. Your memories, photographs, folklore, culinary recipes, and spoken audio remain yours:
                </p>
                <div class="space-y-3 text-sm text-stone-600 dark:text-stone-400">
                    <p>
                        <strong class="text-stone-800 dark:text-stone-200">You Retain Full Ownership:</strong> Kulturegram does NOT claim copyright or title to your photos, audio recordings, written folklore, or classroom materials.
                    </p>
                    <p>
                        <strong class="text-stone-800 dark:text-stone-200">Limited Operational License:</strong> In order to provide the platform, you grant Kulturegram a non-exclusive, royalty-free, worldwide, transferable license to store, format, transcode, display, and distribute your content across devices solely for the purpose of operating, improving, and showcasing the service.
                    </p>
                    <p>
                        <strong class="text-stone-800 dark:text-stone-200">Copyright Infringement (DMCA):</strong> We respect copyright laws globally. If you believe your copyrighted work or traditional knowledge has been improperly posted, submit a notice to <code class="text-amber-600 dark:text-amber-400 font-mono text-xs">dmca@kulturegram.com</code> for immediate review and removal.
                    </p>
                </div>
            </section>

            {{-- 6. Classes, Workshops & Live Hubs --}}
            <section id="sec-6" class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4 scroll-mt-24">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                        6
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-amber-500">6. Cultural Classes, Workshops & Live Broadcasts</h2>
                </div>
                <p>
                    Kulturegram enables cultural masters, elders, and educators to conduct interactive classes and live community gatherings:
                </p>
                <ul class="list-disc list-inside space-y-2 text-stone-600 dark:text-stone-400 pl-2 text-sm">
                    <li><strong class="text-stone-800 dark:text-stone-200">Instructor Integrity:</strong> Educators and workshop hosts agree to present authentic, constructive cultural instruction and avoid promoting dangerous, unverified, or harmful practices.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">No Unauthorized Recording or Piracy:</strong> Attendees may not record, scrape, rip, or redistribute live masterclasses, language audio files, or curriculum documents without the instructor's explicit written permission.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">Classroom Etiquette:</strong> Disruptive behavior, hate speech, or offensive interruptions during live events will result in immediate ejection and account penalty.</li>
                </ul>
            </section>

            {{-- 7. AI Features & Discovery Transparency --}}
            <section id="sec-7" class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4 scroll-mt-24">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                        7
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-amber-500">7. AI-Assisted Discovery, Translation & Algorithms</h2>
                </div>
                <p>
                    We deploy ethical artificial intelligence to empower cultural preservation and connection:
                </p>
                <ul class="list-disc list-inside space-y-2 text-stone-600 dark:text-stone-400 pl-2 text-sm">
                    <li><strong class="text-stone-800 dark:text-stone-200">Suggested Connections:</strong> Our system analyzes your chosen cultural roots, spoken dialects, and public interests to introduce you to relevant cultural peers seamlessly in your timeline.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">Bilingual Subtitles & Translation:</strong> Automated translation tools provide real-time language bridge assistance. While we aim for high dialectal precision, translations are provided for cultural accessibility and may not be 100% grammatically perfect.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">Privacy Pre-Scanning:</strong> Automated safeguards protect your private information before public posting to prevent accidental leakage of sensitive identifiers.</li>
                </ul>
            </section>

            {{-- 8. Prohibited Conduct & Safety Standards --}}
            <section id="sec-8" class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4 scroll-mt-24">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                        8
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-amber-500">8. Prohibited Conduct & Safety Standards</h2>
                </div>
                <p>
                    To ensure the safety, warmth, and integrity of Kulturegram, the following behaviors are strictly forbidden:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                    <div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-950/20 border border-red-200/50 dark:border-red-900/40">
                        <span class="font-bold text-red-700 dark:text-red-400 block mb-0.5">Hate Speech & Bullying</span>
                        Attacking or denigrating persons based on race, ethnicity, caste, religion, sexual orientation, disability, or nationality.
                    </div>
                    <div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-950/20 border border-red-200/50 dark:border-red-900/40">
                        <span class="font-bold text-red-700 dark:text-red-400 block mb-0.5">Violence & Illegal Material</span>
                        Promoting violent extremism, terrorism, non-consensual sexual imagery, child exploitation (CSAM), or physical harm.
                    </div>
                    <div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-950/20 border border-red-200/50 dark:border-red-900/40">
                        <span class="font-bold text-red-700 dark:text-red-400 block mb-0.5">Bots, Scraping & Automation</span>
                        Deploying automated crawlers, scrapers, bot farms, fake accounts, or spam messaging services.
                    </div>
                    <div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-950/20 border border-red-200/50 dark:border-red-900/40">
                        <span class="font-bold text-red-700 dark:text-red-400 block mb-0.5">Doxxing & Privacy Invasions</span>
                        Publishing private telephone numbers, home addresses, unredacted government IDs, or banking credentials without permission.
                    </div>
                </div>
            </section>

            {{-- 9. Moderation, Reports & Enforcement --}}
            <section id="sec-9" class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4 scroll-mt-24">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                        9
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-amber-500">9. Moderation, Community Reports & Enforcement</h2>
                </div>
                <p>
                    We balance freedom of cultural expression with rigorous safety:
                </p>
                <ul class="list-disc list-inside space-y-2 text-stone-600 dark:text-stone-400 pl-2 text-sm">
                    <li><strong class="text-stone-800 dark:text-stone-200">Community Reporting:</strong> Every user can report offensive content, harassing messages, or stolen cultural artifacts using the report trigger on posts and profiles.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">Progressive Discipline:</strong> Violations may result in content removal, tagging restrictions, temporary feature cooldowns, or permanent account bans depending on severity.</li>
                    <li><strong class="text-stone-800 dark:text-stone-200">Appeals:</strong> If you believe content was flagged or removed in error, you may file an appeal through our support portal within 14 days of notification.</li>
                </ul>
            </section>

            {{-- 10. Disclaimers & Liability Limitations --}}
            <section id="sec-10" class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4 scroll-mt-24">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                        10
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-amber-500">10. Disclaimers & Limitations of Liability</h2>
                </div>
                <p class="text-xs uppercase tracking-wider text-stone-400 font-bold">Please Read Carefully</p>
                <p class="text-sm text-stone-600 dark:text-stone-400">
                    Kulturegram is provided on an "AS IS" and "AS AVAILABLE" basis. To the maximum extent permitted by applicable law, Kulturegram, its directors, employees, and community elders disclaim all warranties, express or implied, including merchantability, fitness for a particular purpose, and non-infringement.
                </p>
                <p class="text-sm text-stone-600 dark:text-stone-400">
                    We do not endorse or vouch for the factual veracity of user-generated folklore, family ancestry claims, or community comments. Under no circumstances shall Kulturegram be liable for indirect, incidental, punitive, or consequential damages resulting from your use of the platform.
                </p>
            </section>

            {{-- 11. Disputes, Termination & Updates --}}
            <section id="sec-11" class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-200/80 dark:border-stone-800 shadow-sm space-y-4 scroll-mt-24">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-black text-sm">
                        11
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-amber-500">11. Disputes, Termination & Updates</h2>
                </div>
                <div class="space-y-3 text-sm text-stone-600 dark:text-stone-400">
                    <p>
                        <strong class="text-stone-800 dark:text-stone-200">Account Deletion:</strong> You may close your account and delete your profile data at any time via Profile Settings. Upon deletion, your private data is purged according to our Privacy Policy.
                    </p>
                    <p>
                        <strong class="text-stone-800 dark:text-stone-200">Modifications to Terms:</strong> We may revise these Terms as our platform evolves. If we make material changes, we will notify you through an in-app alert or via your registered email address before the revisions take effect.
                    </p>
                    <p>
                        <strong class="text-stone-800 dark:text-stone-200">Good-Faith Resolution:</strong> You and Kulturegram agree that any claim or dispute arising out of these Terms shall first be addressed through informal, good-faith dialogue with our community council before initiating formal proceedings.
                    </p>
                </div>
            </section>

            {{-- Confirmation CTA --}}
            <div class="p-6 sm:p-8 rounded-3xl bg-amber-500/10 border border-amber-500/20 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white mx-auto flex items-center justify-center shadow-md">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-amber-500">Honoring Heritage Together</h3>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 max-w-lg mx-auto">
                    By standing with Kulturegram, you join a vibrant global movement preserving ancestral wisdom, celebrating cultural identity, and fostering kind human connections.
                </p>
                <div class="pt-2">
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm shadow-md transition active:scale-95">
                        Join Kulturegram
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

        </main>
    </div>
</div>
@endsection
