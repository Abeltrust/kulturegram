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
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



//  Public routes
Route::get('/', [TimelineController::class, 'index'])->name('timeline.index');
Route::get('/cultural-hub', [CulturalHubController::class, 'index'])->name('cultural-hub.index');
Route::get('/discover', [DiscoverController::class, 'index'])->name('discover.index');
Route::get('/education', [EducationalHubController::class, 'index'])->name('education.index');
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
    Route::post('/timeline/{post}/tap', [TimelineController::class, 'tap'])->name('posts.tap');
    Route::post('/timeline/{post}/checkin', [TimelineController::class, 'checkin'])->name('posts.checkin');
    Route::post('/timeline/{post}/resonance', [TimelineController::class, 'resonance'])->name('timeline.resonance');


    // Cultural Hub
    Route::get('/cultural-hub/create', [CulturalHubController::class, 'create'])->name('cultural-hub.create');
    Route::get('/cultural-hub/{culture}/edit', [CulturalHubController::class, 'edit'])->name('cultural-hub.edit');
    Route::post('/cultural-hub/store', [CulturalHubController::class, 'store'])->name('cultural-hub.store');
    Route::put('/cultural-hub/{culture}', [CulturalHubController::class, 'update'])->name('cultural-hub.update');
    Route::delete('/cultural-hub/{culture}', [CulturalHubController::class, 'destroy'])->name('cultural-hub.destroy');
    Route::post('/cultural-hub/{culture}/lock-in', [CulturalHubController::class, 'lockin'])->name('cultural-hub.lock-in');
    Route::post('/cultural-hub/{culture}/resonance', [CulturalHubController::class, 'resonance'])->name('cultural-hub.resonance');


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

    // Kulturegram Meet - Virtual Classroom
    Route::get('/events/{event}/meet', [EventController::class, 'meet'])->name('events.meet');

    // Admin Moderation
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/cultures', [\App\Http\Controllers\Admin\ModerationController::class, 'index'])->name('cultures.index');
        Route::post('/cultures/{culture}/approve', [\App\Http\Controllers\Admin\ModerationController::class, 'approve'])->name('cultures.approve');
        Route::post('/cultures/{culture}/feature', [\App\Http\Controllers\Admin\ModerationController::class, 'feature'])->name('cultures.feature');
        Route::delete('/cultures/{culture}', [\App\Http\Controllers\Admin\ModerationController::class, 'destroy'])->name('cultures.destroy');
    });
});

Route::get('/cultural-hub/{culture}', [CulturalHubController::class, 'show'])->name('cultural-hub.show');
