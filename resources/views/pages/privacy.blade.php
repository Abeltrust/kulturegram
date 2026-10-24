@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-16">
    {{-- Header Banner --}}
    <div class="mb-10 text-center sm:text-left border-b border-stone-200 dark:border-stone-800 pb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/40 text-amber-700 dark:text-amber-400 text-xs font-bold mb-4">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            Trust & Heritage Sovereignty
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-stone-900 dark:text-white tracking-tight">
            Privacy Policy & Data Sovereignty
        </h1>
        <p class="text-stone-600 dark:text-stone-400 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
            At Kulturegram, we believe cultural heritage, personal memories, and sacred generational artifacts belong to their rightful guardians. This policy details how your personal data and digital heirlooms are protected.
        </p>
        <p class="text-xs text-stone-400 dark:text-stone-500 mt-3">
            Last Updated: October 2026 • Effective Worldwide
        </p>
    </div>

    {{-- Content Sections --}}
    <div class="space-y-10 text-stone-700 dark:text-stone-300 leading-relaxed text-sm sm:text-base">

        {{-- 1. Cultural Data Sovereignty --}}
        <section class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-100 dark:border-stone-800 shadow-sm space-y-4">
            <div class="flex items-center gap-3 text-amber-600 dark:text-amber-400">
                <i data-lucide="award" class="w-6 h-6"></i>
                <h2 class="text-xl font-bold text-stone-900 dark:text-white">1. Indigenous & Cultural Data Sovereignty</h2>
            </div>
            <p>
                Kulturegram operates under the guiding principles of Indigenous Data Sovereignty and Cultural Intellectual Property. You retain complete ownership over the oral histories, songs, traditional knowledge, crafts, folklore, and cultural expressions you submit to Kulturegram.
            </p>
            <ul class="list-disc list-inside space-y-2 text-stone-600 dark:text-stone-400 pl-2">
                <li>We do not claim intellectual property or copyright over your cultural heritage.</li>
                <li>Public cultural stories are shared under open or community licenses selected by the submitter (e.g., Creative Commons, Traditional Knowledge Labels).</li>
                <li>You may modify, redact, or request the archiving of cultural stories you have contributed at any time.</li>
            </ul>
        </section>

        {{-- 2. Personal Heritage Vault Privacy --}}
        <section class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-100 dark:border-stone-800 shadow-sm space-y-4">
            <div class="flex items-center gap-3 text-amber-600 dark:text-amber-400">
                <i data-lucide="lock" class="w-6 h-6"></i>
                <h2 class="text-xl font-bold text-stone-900 dark:text-white">2. Personal Vault Security & Access Levels</h2>
            </div>
            <p>
                Your Personal Heritage Vault is an encrypted digital repository engineered for private heirlooms, ancestral records, audio recordings, and family documents. Items stored within your Vault are strictly governed by your selected access tier:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700">
                    <p class="font-bold text-stone-900 dark:text-white text-sm mb-1 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> Private
                    </p>
                    <p class="text-xs text-stone-500 dark:text-stone-400">Only accessible by you when logged in. Stored in isolated, authenticated private storage.</p>
                </div>
                <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700">
                    <p class="font-bold text-stone-900 dark:text-white text-sm mb-1 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Family & Kin
                    </p>
                    <p class="text-xs text-stone-500 dark:text-stone-400">Accessible solely to designated locked-in connections verified as kin or family members.</p>
                </div>
                <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700">
                    <p class="font-bold text-stone-900 dark:text-white text-sm mb-1 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Research Archive
                    </p>
                    <p class="text-xs text-stone-500 dark:text-stone-400">Accessible to accredited historians and academic researchers with provenance attribution.</p>
                </div>
                <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-800/60 border border-stone-200 dark:border-stone-700">
                    <p class="font-bold text-stone-900 dark:text-white text-sm mb-1 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Public Cultural Commons
                    </p>
                    <p class="text-xs text-stone-500 dark:text-stone-400">Publicly visible across the Kulturegram community to celebrate shared heritage.</p>
                </div>
            </div>
        </section>

        {{-- 3. Information We Collect --}}
        <section class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-100 dark:border-stone-800 shadow-sm space-y-4">
            <div class="flex items-center gap-3 text-amber-600 dark:text-amber-400">
                <i data-lucide="database" class="w-6 h-6"></i>
                <h2 class="text-xl font-bold text-stone-900 dark:text-white">3. Information We Collect & Why</h2>
            </div>
            <p>
                To provide our interactive timeline, community circles, virtual gatherings, and live broadcasts, we collect:
            </p>
            <ul class="list-disc list-inside space-y-2 text-stone-600 dark:text-stone-400 pl-2">
                <li><strong>Account Details:</strong> Name, username, email address, password hash, and optional biographical notes.</li>
                <li><strong>Profile Customization:</strong> Profile photo (avatar), custom cover banner, cultural origin, ancestral languages, and location.</li>
                <li><strong>User-Generated Content:</strong> Timeline posts, photos, audio clips, community discussions, and event registrations.</li>
                <li><strong>Transaction Records:</strong> History of tickets reserved or community contributions sent (all transactions are securely logged with unique verification codes).</li>
            </ul>
        </section>

        {{-- 4. No Commercial Sale of Personal Data --}}
        <section class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-100 dark:border-stone-800 shadow-sm space-y-4">
            <div class="flex items-center gap-3 text-amber-600 dark:text-amber-400">
                <i data-lucide="shield-alert" class="w-6 h-6"></i>
                <h2 class="text-xl font-bold text-stone-900 dark:text-white">4. Absolute Commitment: No Selling of Data</h2>
            </div>
            <p>
                <strong>We do not sell, rent, monetize, or broker your personal information or heritage files to third-party advertisers or data brokers.</strong>
            </p>
            <p>
                Your data is used solely to power your platform experience: connecting you with cultural communities, providing event notifications, and preserving your memories securely.
            </p>
        </section>

        {{-- 5. Your Rights: Export & Deletion --}}
        <section class="bg-white dark:bg-stone-900 p-6 sm:p-8 rounded-3xl border border-stone-100 dark:border-stone-800 shadow-sm space-y-4">
            <div class="flex items-center gap-3 text-amber-600 dark:text-amber-400">
                <i data-lucide="user-check" class="w-6 h-6"></i>
                <h2 class="text-xl font-bold text-stone-900 dark:text-white">5. Your Rights & Account Controls</h2>
            </div>
            <p>
                You retain ultimate authority over your account:
            </p>
            <ul class="list-disc list-inside space-y-2 text-stone-600 dark:text-stone-400 pl-2">
                <li><strong>Access & Modification:</strong> Update your profile photo, biographical data, and privacy preferences directly in your <a href="{{ auth()->check() ? route('settings.index', auth()->id()) : route('login') }}" class="text-amber-600 dark:text-amber-400 underline font-semibold">User Settings</a>.</li>
                <li><strong>Right to Erasure (Account Deletion):</strong> You may permanently delete your account and all associated vault records at any time from your settings dashboard.</li>
                <li><strong>Communication Preferences:</strong> Control email alerts and in-app notifications according to your preferences.</li>
            </ul>
        </section>

        {{-- 6. Contact Us --}}
        <section class="bg-gradient-to-br from-amber-500/10 to-orange-500/10 dark:from-amber-950/30 dark:to-stone-900 p-6 sm:p-8 rounded-3xl border border-amber-200 dark:border-amber-900/50 space-y-3">
            <h2 class="text-lg font-bold text-stone-900 dark:text-white flex items-center gap-2">
                <i data-lucide="mail" class="w-5 h-5 text-amber-600"></i>
                Questions or Heritage Inquiries?
            </h2>
            <p class="text-sm text-stone-600 dark:text-stone-300">
                If you have questions regarding this Privacy Policy, ancestral provenance rights, or data protection, please reach out to our team at:
            </p>
            <p class="text-sm font-bold text-amber-700 dark:text-amber-400">
                privacy@kulturegram.com • Kulturegram Heritage Foundation
            </p>
        </section>

    </div>
</div>
@endsection
