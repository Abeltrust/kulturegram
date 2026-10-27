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
        // 1. Extend cultures table with core archive classification & provenance fields
        Schema::table('cultures', function (Blueprint $table) {
            if (!Schema::hasColumn('cultures', 'cultural_group')) {
                $table->string('cultural_group')->nullable()->after('category');
            }
            if (!Schema::hasColumn('cultures', 'translated_title')) {
                $table->string('translated_title')->nullable()->after('name');
            }
            if (!Schema::hasColumn('cultures', 'subtype')) {
                $table->string('subtype')->nullable()->after('category');
            }
            if (!Schema::hasColumn('cultures', 'review_status')) {
                // draft, submitted, under_review, published, awaiting_contributions, disputed, restricted, archived
                $table->string('review_status')->default('published')->after('status');
            }
            if (!Schema::hasColumn('cultures', 'sensitivity_level')) {
                // public, community_only, restricted
                $table->string('sensitivity_level')->default('public')->after('review_status');
            }
            if (!Schema::hasColumn('cultures', 'locality')) {
                $table->string('locality')->nullable()->after('region');
            }
            if (!Schema::hasColumn('cultures', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('locality');
            }
            if (!Schema::hasColumn('cultures', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });

        // 2. Cultural Sources (Testimony, recordings, manuscripts, scans)
        Schema::create('cultural_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('culture_id')->constrained('cultures')->onDelete('cascade');
            $table->string('source_type'); // oral_testimony, written_account, audio_recording, video_recording, archival_document, photograph, artefact, other
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->longText('original_text')->nullable();
            $table->string('original_language')->nullable();
            $table->string('speaker_name')->nullable();
            $table->boolean('speaker_is_anonymous')->default(false);
            $table->string('attribution_preference')->nullable();
            $table->string('recording_date')->nullable();
            $table->string('recording_location')->nullable();
            $table->string('recording_creator')->nullable();
            $table->string('transcript_origin')->default('human_authored'); // human_authored, ai_generated, collaborative
            $table->string('transcript_review_status')->default('unreviewed'); // unreviewed, community_reviewed, source_verified, approved
            $table->json('timestamps_data')->nullable();
            $table->text('source_notes')->nullable();
            $table->string('access_restriction')->default('public'); // public, community_only, restricted
            $table->timestamps();

            $table->index(['culture_id', 'source_type']);
        });

        // 3. Cultural Claims and Supporting Evidence
        Schema::create('cultural_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('culture_id')->constrained('cultures')->onDelete('cascade');
            $table->foreignId('cultural_source_id')->nullable()->constrained('cultural_sources')->onDelete('set null');
            $table->text('claim_text');
            $table->string('claim_type')->default('quoted_testimony'); // quoted_testimony, contributor_interpretation, historical_fact, ai_suggestion, oral_tradition, disputed_claim
            $table->text('evidence_reference')->nullable(); // excerpt, timestamp, citation URL, or page
            $table->string('review_status')->default('unverified'); // unverified, source_verified, community_reviewed, disputed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['culture_id', 'review_status']);
        });

        // 4. Cultural Contributors, Custodians & Reviewers
        Schema::create('cultural_contributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('culture_id')->constrained('cultures')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('public_name')->nullable();
            $table->string('role')->default('contributor'); // contributor, storyteller, elder, practitioner, speaker, translator, recorder, reviewer, custodian
            $table->string('attribution_preference')->nullable();
            $table->text('relationship_to_material')->nullable();
            $table->string('review_responsibility')->nullable();
            $table->string('review_decision')->default('pending'); // pending, approved, flagged, changes_requested
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('permission_to_display')->default(true);
            $table->timestamps();

            $table->index(['culture_id', 'role']);
        });

        // 5. Cultural Media Assets and Rights Tracking
        Schema::create('cultural_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('culture_id')->constrained('cultures')->onDelete('cascade');
            $table->string('media_type')->default('photograph'); // photograph, audio, video, scanned_document, artefact_image, 3d_model, supporting_file
            $table->string('file_path');
            $table->text('caption')->nullable();
            $table->string('creator_attribution')->nullable();
            $table->string('creation_date')->nullable();
            $table->string('copyright_info')->nullable();
            $table->string('license_type')->nullable();
            $table->boolean('has_consent')->default(true);
            $table->string('access_restriction')->default('public'); // public, community_only, restricted
            $table->string('processing_status')->default('ready');
            $table->timestamps();

            $table->index(['culture_id', 'media_type']);
        });

        // 6. Cultural Translations
        Schema::create('cultural_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('culture_id')->constrained('cultures')->onDelete('cascade');
            $table->string('language');
            $table->string('dialect')->nullable();
            $table->string('translated_title')->nullable();
            $table->longText('translated_content');
            $table->string('translator_name')->nullable();
            $table->string('provenance')->default('human'); // human, ai_assisted, community_edited
            $table->string('review_status')->default('draft'); // draft, community_reviewed, approved
            $table->text('alternative_spellings')->nullable();
            $table->text('dialect_notes')->nullable();
            $table->timestamps();

            $table->index(['culture_id', 'language']);
        });

        // 7. Cultural Contributions and Revision History
        Schema::create('cultural_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('culture_id')->constrained('cultures')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('contributor_name')->nullable();
            $table->string('contributor_email')->nullable();
            $table->string('revision_type')->default('additional_context'); // correction, additional_context, alternative_account, additional_source, new_media, suggested_relationship
            $table->string('title')->nullable();
            $table->longText('proposed_content')->nullable();
            $table->text('evidence_notes')->nullable();
            $table->string('status')->default('submitted'); // submitted, under_review, approved, rejected
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['culture_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cultural_revisions');
        Schema::dropIfExists('cultural_translations');
        Schema::dropIfExists('cultural_media');
        Schema::dropIfExists('cultural_contributors');
        Schema::dropIfExists('cultural_claims');
        Schema::dropIfExists('cultural_sources');

        Schema::table('cultures', function (Blueprint $table) {
            $columnsToDrop = [
                'cultural_group',
                'translated_title',
                'subtype',
                'review_status',
                'sensitivity_level',
                'locality',
                'latitude',
                'longitude',
            ];
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('cultures', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
