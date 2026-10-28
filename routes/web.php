<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\CulturalHubController;
use App\Http\Controllers\VaultController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LiveStreamController;
use App\Http\Controllers\EducationalHubController;
use App\Http\Controllers\LivingHeritageController;
use App\Http\Controllers\CulturalClassController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



//  Public routes
Route::get('/', [TimelineController::class, 'index'])->name('timeline.index');
Route::get('/timeline/taggable-users', [TimelineController::class, 'taggableUsers'])->middleware('auth')->name('timeline.taggable-users');
Route::get('/timeline/{post}', [TimelineController::class, 'show'])->whereNumber('post')->name('timeline.show');
Route::get('/cultural-hub', [CulturalHubController::class, 'index'])->name('cultural-hub.index');
Route::get('/discover', [DiscoverController::class, 'index'])->name('discover.index');
Route::get('/education', [EducationalHubController::class, 'index'])->name('education.index');

// Educational Hub - Cultural Classes & Language Studio
Route::get('/education/classes', [CulturalClassController::class, 'index'])->name('education.classes.index');
Route::get('/education/classes/create', [CulturalClassController::class, 'create'])->middleware('auth')->name('education.classes.create');
Route::post('/education/classes', [CulturalClassController::class, 'store'])->middleware('auth')->name('education.classes.store');
Route::post('/education/classes/ai-transcribe', [CulturalClassController::class, 'transcribeAi'])->middleware('auth')->name('education.classes.ai-transcribe');
Route::post('/education/classes/ai-studio-background', [CulturalClassController::class, 'generateStudioBackground'])->middleware('auth')->name('education.classes.ai-studio-background');
Route::post('/education/classes/upload-chunk', [CulturalClassController::class, 'uploadChunk'])->middleware('auth')->name('education.classes.upload-chunk');
Route::get('/education/classes/{class}/audio-wav', [CulturalClassController::class, 'audioWav'])->name('education.classes.audio-wav');
Route::post('/education/classes/{class}/sync-subtitles', [CulturalClassController::class, 'syncSubtitles'])->name('education.classes.sync-subtitles');
Route::get('/education/classes/{class}/subtitles.vtt', [CulturalClassController::class, 'subtitlesVtt'])->name('education.classes.subtitles');
Route::get('/education/classes/{slug}', [CulturalClassController::class, 'show'])->name('education.classes.show');
Route::post('/education/classes/{class}/enroll', [CulturalClassController::class, 'enroll'])->middleware('auth')->name('education.classes.enroll');
Route::post('/education/classes/{class}/discussions', [CulturalClassController::class, 'addDiscussion'])->middleware('auth')->name('education.classes.discussion');

Route::get('/privacy', function () { return view('pages.privacy'); })->name('privacy');
Route::get('/terms', function () { return view('pages.terms'); })->name('terms');


//  Auth routes
require __DIR__ . '/auth.php';


Route::get('/login', function () {
    return view('auth.login');   // looks for resources/views/login.blade.php
})->name('login');

// Register page — handled by the controller so the origin dropdown data is provided
Route::get('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('register');

// Suggestions for user-added local areas (countries without official LGA data)
Route::get('/locations/countries/{country}/areas', [\App\Http\Controllers\LocationController::class, 'areas'])
    ->middleware('throttle:60,1')
    ->name('locations.areas');


Route::get('/dashboard', function () {
    return redirect()->route('timeline.index');
})->middleware(['auth', 'verified'])->name('dashboard');

//  Protected routes (require login)
Route::middleware('auth')->group(function () {
    // Timeline
    Route::post('/timeline', [TimelineController::class, 'store'])->name('timeline.store');
    Route::get('/timeline/{post}/edit', [TimelineController::class, 'edit'])->name('timeline.edit');
    Route::put('/timeline/{post}', [TimelineController::class, 'update'])->name('timeline.update');
    Route::delete('/timeline/{post}', [TimelineController::class, 'destroy'])->name('timeline.destroy');
    Route::post('/timeline/{post}/tap', [TimelineController::class, 'tap'])->name('posts.tap');
    Route::post('/timeline/{post}/checkin', [TimelineController::class, 'checkin'])->name('posts.checkin');
    Route::post('/timeline/{post}/resonance', [TimelineController::class, 'resonance'])->name('timeline.resonance');
    Route::post('/resonances/{resonance}/tap', [TimelineController::class, 'tapResonance'])->name('resonances.tap');
    Route::post('/timeline/{post}/translate', [TimelineController::class, 'translatePost'])->name('timeline.translate');
    Route::post('/timeline/{post}/repost', [TimelineController::class, 'repost'])->name('timeline.repost');
    Route::post('/timeline/{post}/tags', [TimelineController::class, 'updateTags'])->name('timeline.tags.update');
    Route::delete('/timeline/{post}/tags/self', [TimelineController::class, 'removeSelfTag'])->name('timeline.tags.remove-self');
    Route::post('/timeline/ai-voice-format', [TimelineController::class, 'aiVoiceFormat'])->name('timeline.ai-voice-format');


    // Cultural Hub
    Route::get('/cultural-hub/create', [CulturalHubController::class, 'create'])->name('cultural-hub.create');
    Route::get('/cultural-hub/{culture}/edit', [CulturalHubController::class, 'edit'])->name('cultural-hub.edit');
    Route::post('/cultural-hub/store', [CulturalHubController::class, 'store'])->name('cultural-hub.store');
    Route::put('/cultural-hub/{culture}', [CulturalHubController::class, 'update'])->name('cultural-hub.update');
    Route::delete('/cultural-hub/{culture}', [CulturalHubController::class, 'destroy'])->name('cultural-hub.destroy');
    Route::post('/cultural-hub/{culture}/lock-in', [CulturalHubController::class, 'lockin'])->name('cultural-hub.lock-in');
    Route::post('/cultural-hub/{culture}/resonance', [CulturalHubController::class, 'resonance'])->name('cultural-hub.resonance');
    Route::post('/cultural-hub/structure-narrative', [CulturalHubController::class, 'structureNarrative'])->name('cultural-hub.structure-narrative');
    Route::post('/cultural-hub/ai-voice-format', [CulturalHubController::class, 'aiVoiceFormat'])->name('cultural-hub.ai-voice-format');
    Route::post('/cultural-hub/{culture}/contribute', [CulturalHubController::class, 'submitContribution'])->name('cultural-hub.contribute');
    Route::post('/cultural-hub/{culture}/review-status', [CulturalHubController::class, 'updateReviewStatus'])->name('cultural-hub.review-status');

    // Living Heritage Capture Pipeline
    Route::get('/cultural-hub/living-heritage', [LivingHeritageController::class, 'index'])->name('cultural-hub.living-heritage.index');
    Route::get('/cultural-hub/living-heritage/capture', [LivingHeritageController::class, 'create'])->name('cultural-hub.living-heritage.create');
    Route::post('/cultural-hub/living-heritage/transcribe', [LivingHeritageController::class, 'transcribeAudio'])->middleware('throttle:20,1')->name('cultural-hub.living-heritage.transcribe');
    Route::post('/cultural-hub/living-heritage/prepare', [LivingHeritageController::class, 'prepareStory'])->middleware('throttle:20,1')->name('cultural-hub.living-heritage.prepare');
    Route::post('/cultural-hub/living-heritage/wonder-plan', [LivingHeritageController::class, 'preservationPlan'])->middleware('throttle:20,1')->name('cultural-hub.living-heritage.wonder-plan');
    Route::post('/cultural-hub/living-heritage/audit-originality', [LivingHeritageController::class, 'auditOriginality'])->middleware('throttle:20,1')->name('cultural-hub.living-heritage.audit');
    Route::post('/cultural-hub/living-heritage/generate-illustration', [LivingHeritageController::class, 'generateIllustration'])->middleware('throttle:15,1')->name('cultural-hub.living-heritage.illustration');
    Route::post('/cultural-hub/living-heritage/segment-artifact', [LivingHeritageController::class, 'segmentArtifact'])->middleware('throttle:15,1')->name('cultural-hub.living-heritage.segment');
    Route::post('/cultural-hub/living-heritage/store', [LivingHeritageController::class, 'store'])->name('cultural-hub.living-heritage.store');
    Route::get('/cultural-hub/living-heritage/compare', [LivingHeritageController::class, 'compare'])->name('cultural-hub.living-heritage.compare');
    Route::post('/cultural-hub/living-heritage/compare', [LivingHeritageController::class, 'runComparison'])->middleware('throttle:15,1')->name('cultural-hub.living-heritage.compare.run');


    // User Interactions
    Route::post('/users/{user}/lockin', [ProfileController::class, 'lockin'])->name('users.lockin');
    Route::post('/users/{user}/tap', [ProfileController::class, 'tap'])->name('users.tap');


    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show'); // Auth user
    Route::get('/profile/activity', [ProfileController::class, 'activity'])->name('profile.activity');
    Route::get('/profile/{user}/activity', [ProfileController::class, 'userActivity'])->name('profile.user.activity');
    Route::get('/profile/{user}', [ProfileController::class, 'showUser'])->name('profile.user'); // Other users
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/chapters/create', [ProfileController::class, 'createChapter'])->name('profile.chapters.create');
    Route::post('/profile/chapters', [ProfileController::class, 'storeChapter'])->name('profile.chapters.store');
    Route::delete('/profile/chapters/{chapter}', [ProfileController::class, 'deleteChapter'])->name('profile.chapters.destroy');
    Route::post('/profile/photo', [ProfileController::class, 'photo'])->name('profile.photo.upload');
    Route::post('/profile/cover', [ProfileController::class, 'cover'])->name('profile.cover.upload');
    Route::get('/settings/{id}', [ProfileController::class, 'settings'])->name('settings.index');
    Route::patch('/settings/{id}', [ProfileController::class, 'updateSettings'])->name('settings.update');


    // Vault
    Route::resource('vault', VaultController::class)->parameters(['vault' => 'vaultItem']);
    Route::get('/vault/{vaultItem}/download', [VaultController::class, 'download'])->name('vault.download');

    // Events
    Route::resource('events', EventController::class);
    Route::post('/events/{event}/join', [EventController::class, 'join'])->name('events.join');
    Route::delete('/events/{event}/leave', [EventController::class, 'leave'])->name('events.leave');
    Route::post('/events/{event}/ticket', [EventController::class, 'purchaseTicket'])->name('events.ticket');
    Route::post('/events/{event}/contribute', [EventController::class, 'sendContribution'])->name('events.contribute');

    // Communities
    Route::resource('communities', CommunityController::class);
    Route::post('/communities/{community}/join', [CommunityController::class, 'join'])->name('communities.join');
    Route::delete('/communities/{community}/leave', [CommunityController::class, 'leave'])->name('communities.leave');
    Route::post('/communities/{community}/posts', [CommunityPostController::class, 'store'])->name('communities.posts.store');
    Route::get('/communities/{community}/posts/{post}/edit', [CommunityPostController::class, 'edit'])->name('communities.posts.edit');
    Route::put('/communities/{community}/posts/{post}', [CommunityPostController::class, 'update'])->name('communities.posts.update');
    Route::delete('/communities/{community}/posts/{post}', [CommunityPostController::class, 'destroy'])->name('communities.posts.destroy');
    Route::post('/community-posts/{post}/tap', [CommunityPostController::class, 'tap'])->name('community-posts.tap');
    Route::post('/community-posts/{post}/comment', [CommunityPostController::class, 'commentAjax'])->name('community-posts.comment.ajax');

    //comment
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{conversation}/updates', [MessageController::class, 'updates'])->name('messages.updates');
    Route::get('/messages/start/{user}', [MessageController::class, 'startConversation'])->name('messages.start');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    Route::get('/live-stream', [LiveStreamController::class, 'index'])->name('live-stream.index');
    Route::post('/live-stream', [LiveStreamController::class, 'store'])->name('live-stream.store');
    Route::get('/live-stream/{stream}', [LiveStreamController::class, 'show'])->name('live-stream.show');
    Route::post('/live-stream/{stream}/end', [LiveStreamController::class, 'end'])->name('live-stream.end');
    Route::post('/live-stream/{stream}/ajax-end', [LiveStreamController::class, 'ajaxEnd'])->name('live-stream.ajax-end');

    // Live Stream AI Moderation & Real-Time Messaging
    Route::get('/live-stream/{stream}/messages', [LiveStreamController::class, 'getMessages'])->name('live-stream.messages.get');
    Route::post('/live-stream/{stream}/messages', [LiveStreamController::class, 'postMessage'])->name('live-stream.messages.post');
    Route::get('/live-stream/{stream}/flagged', [LiveStreamController::class, 'getFlaggedMessages'])->name('live-stream.messages.flagged');
    Route::post('/live-stream/{stream}/messages/{message}/dismiss', [LiveStreamController::class, 'dismissFlag'])->name('live-stream.messages.dismiss');
    Route::post('/live-stream/{stream}/messages/{message}/remove', [LiveStreamController::class, 'removeMessage'])->name('live-stream.messages.remove');

    // AI Community Intelligence (Live summaries, catch-up assistant, and draft record extraction)
    Route::get('/live-stream/{stream}/catch-up', [LiveStreamController::class, 'catchUp'])->name('live-stream.catch-up');
    Route::post('/live-stream/{stream}/generate-summary', [LiveStreamController::class, 'generateSummary'])->name('live-stream.generate-summary');
    Route::post('/live-stream/{stream}/extract-draft-record', [LiveStreamController::class, 'extractDraftRecord'])->name('live-stream.extract-draft-record');

    // AI Cultural Opportunity Engine
    Route::get('/cultural-opportunities', [\App\Http\Controllers\CulturalOpportunityController::class, 'index'])->name('cultural-opportunities.index');
    Route::post('/cultural-opportunities/dismiss', [\App\Http\Controllers\CulturalOpportunityController::class, 'dismiss'])->name('cultural-opportunities.dismiss');
    Route::post('/cultural-opportunities/preferences', [\App\Http\Controllers\CulturalOpportunityController::class, 'updatePreferences'])->name('cultural-opportunities.preferences');

    // AI-Assisted Collective Cultural Preservation (Draft review & submission workflow)
    Route::get('/cultural-hub/drafts', [\App\Http\Controllers\CulturalRecordDraftController::class, 'index'])->name('cultural-hub.drafts.index');
    Route::get('/cultural-hub/drafts/{draft}', [\App\Http\Controllers\CulturalRecordDraftController::class, 'show'])->name('cultural-hub.drafts.show');
    Route::put('/cultural-hub/drafts/{draft}', [\App\Http\Controllers\CulturalRecordDraftController::class, 'update'])->name('cultural-hub.drafts.update');
    Route::post('/cultural-hub/drafts/{draft}/submit', [\App\Http\Controllers\CulturalRecordDraftController::class, 'submitToModeration'])->name('cultural-hub.drafts.submit');
    Route::delete('/cultural-hub/drafts/{draft}', [\App\Http\Controllers\CulturalRecordDraftController::class, 'destroy'])->name('cultural-hub.drafts.destroy');

    // AI Multilingual Cultural Access
    Route::post('/cultural-hub/{culture}/translate', [CulturalHubController::class, 'translate'])->name('cultural-hub.translate');
    Route::post('/cultural-hub/{culture}/adapt-reading', [CulturalHubController::class, 'adaptReading'])->name('cultural-hub.adapt-reading');

    // AI Feedback and Continuous Improvement API
    Route::post('/api/ai/feedback', [\App\Http\Controllers\AiFeedbackController::class, 'store'])->name('ai.feedback.store');

    // Cultural Discovery & Like-Minded Connections
    Route::get('/cultural-discovery/recommendations', [\App\Http\Controllers\CulturalDiscoveryController::class, 'recommendations'])->name('cultural-discovery.recommendations');
    Route::post('/cultural-discovery/dismiss', [\App\Http\Controllers\CulturalDiscoveryController::class, 'dismiss'])->name('cultural-discovery.dismiss');
    Route::post('/cultural-discovery/toggle-opt-in', [\App\Http\Controllers\CulturalDiscoveryController::class, 'toggleOptIn'])->name('cultural-discovery.toggle-opt-in');

    // Content Privacy Pre-Scan API
    Route::post('/api/privacy/scan', [\App\Http\Controllers\Admin\PrivacyAuditController::class, 'scanContent'])->name('privacy.scan');

    // AI-Assisted Event Reach Optimizer, Concept Description & Flyer Generation
    Route::post('/events/ai-optimize', [EventController::class, 'aiOptimize'])->name('events.ai-optimize');
    Route::post('/events/ai-suggest-description', [EventController::class, 'aiSuggestDescription'])->name('events.ai-suggest-description');
    Route::post('/events/ai-generate-flyer', [EventController::class, 'aiGenerateFlyer'])->name('events.ai-generate-flyer');

    // AI-Assisted Live Stream Caption & Thumbnail Generation
    Route::post('/live-stream/ai-suggest-caption', [LiveStreamController::class, 'aiSuggestCaption'])->name('live-stream.ai-suggest-caption');
    Route::post('/live-stream/ai-generate-thumbnail', [LiveStreamController::class, 'aiGenerateThumbnail'])->name('live-stream.ai-generate-thumbnail');

    // Kulturegram Meet - Virtual Classroom
    Route::get('/events/{event}/meet', [EventController::class, 'meet'])->name('events.meet');

    // Admin Moderation & Privacy Audit
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/cultures', [\App\Http\Controllers\Admin\ModerationController::class, 'index'])->name('cultures.index');
        Route::post('/cultures/{culture}/approve', [\App\Http\Controllers\Admin\ModerationController::class, 'approve'])->name('cultures.approve');
        Route::post('/cultures/{culture}/feature', [\App\Http\Controllers\Admin\ModerationController::class, 'feature'])->name('cultures.feature');
        Route::delete('/cultures/{culture}', [\App\Http\Controllers\Admin\ModerationController::class, 'destroy'])->name('cultures.destroy');
        Route::post('/cultural-hub/drafts/{draft}/approve', [\App\Http\Controllers\CulturalRecordDraftController::class, 'approve'])->name('drafts.approve');

        // AI Feedback and Metrics Dashboard
        Route::get('/ai-feedback', [\App\Http\Controllers\AiFeedbackController::class, 'adminIndex'])->name('ai-feedback.index');
        Route::post('/ai-feedback/{log}/status', [\App\Http\Controllers\AiFeedbackController::class, 'updateStatus'])->name('ai-feedback.status');

        // AI System Privacy Policy Audit
        Route::get('/privacy-audit', [\App\Http\Controllers\Admin\PrivacyAuditController::class, 'index'])->name('privacy-audit.index');
        Route::post('/privacy-audit/run', [\App\Http\Controllers\Admin\PrivacyAuditController::class, 'run'])->name('privacy-audit.run');
    });
});

Route::get('/cultural-hub/{culture}', [CulturalHubController::class, 'show'])->name('cultural-hub.show');
Route::post('/cultural-hub/{culture}/companion', [CulturalHubController::class, 'askCompanion'])->name('cultural-hub.companion');
