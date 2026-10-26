<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure status column can accommodate 'draft' alongside 'pending_review', 'approved', 'featured'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE cultures MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending_review'");
        } else {
            Schema::table('cultures', function (Blueprint $table) {
                $table->string('status', 50)->default('pending_review')->change();
            });
        }

        // 2. Add Living Heritage AI and oral preservation columns
        Schema::table('cultures', function (Blueprint $table) {
            $table->text('raw_story')->nullable()->after('description');
            $table->text('audio_transcript')->nullable()->after('raw_story');
            $table->boolean('is_ai_assisted')->default(false)->after('audio_transcript');
            $table->string('ai_illustration')->nullable()->after('image');
            $table->text('ai_illustration_prompt')->nullable()->after('ai_illustration');
            $table->json('artifact_segmentation')->nullable()->after('media_files');
            $table->json('wonder_preservation_plan')->nullable()->after('artifact_segmentation');
            $table->json('ai_metadata')->nullable()->after('wonder_preservation_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cultures', function (Blueprint $table) {
            $table->dropColumn([
                'raw_story',
                'audio_transcript',
                'is_ai_assisted',
                'ai_illustration',
                'ai_illustration_prompt',
                'artifact_segmentation',
                'wonder_preservation_plan',
                'ai_metadata',
            ]);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE cultures MODIFY COLUMN status ENUM('pending_review', 'approved', 'featured') NOT NULL DEFAULT 'pending_review'");
        }
    }
};
