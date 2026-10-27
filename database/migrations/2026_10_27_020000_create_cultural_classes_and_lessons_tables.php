<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cultural_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cultural_focus')->nullable(); // e.g. "Hausa Language & Everyday Etiquette"
            $table->string('target_language')->nullable(); // e.g. "Hausa", "Yoruba", "Igbo", "Swahili"
            $table->string('source_language')->default('English');
            $table->string('level')->default('all_levels'); // beginner, intermediate, advanced, all_levels
            $table->enum('price_type', ['free', 'paid'])->default('free');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('video_url')->nullable();
            $table->string('preview_video_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('virtual_background_url')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->longText('transcript')->nullable();
            $table->longText('subtitles_vtt')->nullable();
            $table->json('timed_subtitles')->nullable(); // [{start, end, text, source_phrase, target_phrase, cultural_note}]
            $table->json('bilingual_vocabulary')->nullable(); // [{source, target, language, context, pronunciation}]
            $table->text('ai_summary')->nullable();
            $table->json('ai_enhancements')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('enrollments_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->boolean('shared_to_timeline')->default(false);
            $table->foreignId('timeline_post_id')->nullable()->constrained('posts')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('cultural_class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cultural_class_id')->constrained('cultural_classes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('price_paid', 10, 2)->default(0.00);
            $table->string('access_status')->default('active'); // active, completed
            $table->unsignedInteger('progress_seconds')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('last_watched_at')->nullable();
            $table->timestamps();

            $table->unique(['cultural_class_id', 'user_id']);
        });

        Schema::create('cultural_class_discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cultural_class_id')->constrained('cultural_classes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('timestamp_seconds')->nullable(); // Question linked to exact second in video!
            $table->text('message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cultural_class_discussions');
        Schema::dropIfExists('cultural_class_enrollments');
        Schema::dropIfExists('cultural_classes');
    }
};
