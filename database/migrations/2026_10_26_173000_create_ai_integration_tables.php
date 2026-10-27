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
        // 1. Live stream chat messages with intelligent AI moderation support
        if (!Schema::hasTable('live_stream_messages')) {
            Schema::create('live_stream_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('message');
                $table->boolean('is_flagged')->default(false);
                $table->string('flag_status', 30)->default('clean'); // clean, flagged, dismissed, removed
                $table->string('flag_category', 50)->nullable(); // harassment, hate_speech, spam, privacy_violation, harmful_content, derailment
                $table->text('flag_reason')->nullable();
                $table->string('violated_rule', 100)->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['live_stream_id', 'created_at']);
                $table->index('flag_status');
            });
        }

        // 2. User recommendation dismissals for user control & opt-outs
        if (!Schema::hasTable('cultural_recommendation_dismissals')) {
            Schema::create('cultural_recommendation_dismissals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('recommendation_type', 30); // user, community, event
                $table->unsignedBigInteger('target_id');
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['user_id', 'recommendation_type', 'target_id'], 'user_rec_dismissal_unique');
            });
        }

        // 3. AI reach metadata and tags on events for discovery
        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                if (!Schema::hasColumn('events', 'reach_tags')) {
                    $table->json('reach_tags')->nullable()->after('requirements');
                }
                if (!Schema::hasColumn('events', 'ai_reach_metadata')) {
                    $table->json('ai_reach_metadata')->nullable()->after('reach_tags');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cultural_recommendation_dismissals');
        Schema::dropIfExists('live_stream_messages');

        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                if (Schema::hasColumn('events', 'ai_reach_metadata')) {
                    $table->dropColumn('ai_reach_metadata');
                }
                if (Schema::hasColumn('events', 'reach_tags')) {
                    $table->dropColumn('reach_tags');
                }
            });
        }
    }
};
