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
        // 1. Extend live_streams with AI intelligence fields
        if (Schema::hasTable('live_streams')) {
            Schema::table('live_streams', function (Blueprint $table) {
                if (!Schema::hasColumn('live_streams', 'ai_summary')) {
                    $table->text('ai_summary')->nullable()->after('is_live');
                }
                if (!Schema::hasColumn('live_streams', 'unanswered_questions')) {
                    $table->json('unanswered_questions')->nullable()->after('ai_summary');
                }
                if (!Schema::hasColumn('live_streams', 'cultural_references')) {
                    $table->json('cultural_references')->nullable()->after('unanswered_questions');
                }
                if (!Schema::hasColumn('live_streams', 'summary_generated_at')) {
                    $table->timestamp('summary_generated_at')->nullable()->after('cultural_references');
                }
                if (!Schema::hasColumn('live_streams', 'summary_status')) {
                    $table->string('summary_status', 30)->default('none')->after('summary_generated_at');
                }
            });
        }

        // 2. Extend users with opt-in collaboration setting
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'opt_in_collaboration')) {
                    $table->boolean('opt_in_collaboration')->default(false)->after('preferences');
                }
            });
        }

        // 3. Cultural Relationships (Knowledge Graph via relational mapping)
        if (!Schema::hasTable('cultural_relationships')) {
            Schema::create('cultural_relationships', function (Blueprint $table) {
                $table->id();
                $table->string('source_type', 60); // Culture, Language, Community, Event
                $table->unsignedBigInteger('source_id');
                $table->string('target_type', 60);
                $table->unsignedBigInteger('target_id');
                $table->string('relationship_type', 60); // shares_language, shares_region, ritual_counterpart, historical_lineage, related_practice, custodian_link
                $table->decimal('confidence', 4, 3)->default(0.850);
                $table->string('explanation', 255);
                $table->boolean('verified_by_human')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['source_type', 'source_id'], 'source_entity_index');
                $table->index(['target_type', 'target_id'], 'target_entity_index');
                $table->index('relationship_type');
            });
        }

        // 4. Cultural Record Drafts (AI-Assisted Collective Cultural Preservation)
        if (!Schema::hasTable('cultural_record_drafts')) {
            Schema::create('cultural_record_drafts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_stream_id')->nullable()->constrained('live_streams')->nullOnDelete();
                $table->foreignId('contributor_id')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->string('category', 60)->nullable();
                $table->string('region', 100)->nullable();
                $table->string('language', 100)->nullable();
                $table->longText('extracted_story'); // Original testimony / narrative
                $table->text('ai_summary')->nullable(); // Explicitly labeled AI interpretation
                $table->json('unanswered_questions')->nullable(); // Inquiries for community custodians
                $table->json('suggested_links')->nullable(); // Suggested links to existing heritage records
                $table->text('custodian_notes')->nullable(); // Custodian enrichments & corrections
                $table->string('status', 40)->default('draft'); // draft, reviewed, submitted_to_moderation, approved, rejected
                $table->foreignId('approved_culture_id')->nullable()->constrained('cultures')->nullOnDelete();
                $table->timestamps();

                $table->index('status');
                $table->index('contributor_id');
            });
        }

        // 5. AI Feedback Logs (Controlled feedback loop for continuous improvement)
        if (!Schema::hasTable('ai_feedback_logs')) {
            Schema::create('ai_feedback_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('feedback_type', 50); // moderation_dispute, recommendation_irrelevant, recommendation_inappropriate, event_audience_mismatch, summary_correction, preference_update
                $table->string('target_type', 60)->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->smallInteger('rating')->nullable();
                $table->string('reason', 100)->nullable();
                $table->text('user_correction')->nullable();
                $table->json('metadata')->nullable();
                $table->string('status', 30)->default('logged'); // logged, reviewed, applied
                $table->timestamps();

                $table->index(['feedback_type', 'status']);
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_feedback_logs');
        Schema::dropIfExists('cultural_record_drafts');
        Schema::dropIfExists('cultural_relationships');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'opt_in_collaboration')) {
                    $table->dropColumn('opt_in_collaboration');
                }
            });
        }

        if (Schema::hasTable('live_streams')) {
            Schema::table('live_streams', function (Blueprint $table) {
                $cols = ['ai_summary', 'unanswered_questions', 'cultural_references', 'summary_generated_at', 'summary_status'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('live_streams', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
