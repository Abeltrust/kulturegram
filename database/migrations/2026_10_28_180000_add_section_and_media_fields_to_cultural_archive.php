<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cultural_media', function (Blueprint $table) {
            if (!Schema::hasColumn('cultural_media', 'section')) {
                $table->string('section')->nullable()->default('overview')->after('media_type');
            }
            if (!Schema::hasColumn('cultural_media', 'external_url')) {
                $table->string('external_url')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('cultural_media', 'book_title')) {
                $table->string('book_title')->nullable()->after('external_url');
            }
            if (!Schema::hasColumn('cultural_media', 'book_author')) {
                $table->string('book_author')->nullable()->after('book_title');
            }
            if (!Schema::hasColumn('cultural_media', 'book_year')) {
                $table->string('book_year')->nullable()->after('book_author');
            }
            if (!Schema::hasColumn('cultural_media', 'book_publisher')) {
                $table->string('book_publisher')->nullable()->after('book_year');
            }
            if (!Schema::hasColumn('cultural_media', 'book_isbn')) {
                $table->string('book_isbn')->nullable()->after('book_publisher');
            }
            if (!Schema::hasColumn('cultural_media', 'book_excerpt')) {
                $table->text('book_excerpt')->nullable()->after('book_isbn');
            }
        });

        Schema::table('cultural_revisions', function (Blueprint $table) {
            if (!Schema::hasColumn('cultural_revisions', 'target_section')) {
                $table->string('target_section')->nullable()->default('overview')->after('revision_type');
            }
            if (!Schema::hasColumn('cultural_revisions', 'image_path')) {
                $table->string('image_path')->nullable()->after('proposed_content');
            }
            if (!Schema::hasColumn('cultural_revisions', 'video_url')) {
                $table->string('video_url')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('cultural_revisions', 'video_path')) {
                $table->string('video_path')->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('cultural_revisions', 'book_title')) {
                $table->string('book_title')->nullable()->after('video_path');
            }
            if (!Schema::hasColumn('cultural_revisions', 'book_author')) {
                $table->string('book_author')->nullable()->after('book_title');
            }
            if (!Schema::hasColumn('cultural_revisions', 'book_year')) {
                $table->string('book_year')->nullable()->after('book_author');
            }
            if (!Schema::hasColumn('cultural_revisions', 'book_publisher')) {
                $table->string('book_publisher')->nullable()->after('book_year');
            }
            if (!Schema::hasColumn('cultural_revisions', 'book_isbn')) {
                $table->string('book_isbn')->nullable()->after('book_publisher');
            }
            if (!Schema::hasColumn('cultural_revisions', 'book_excerpt')) {
                $table->text('book_excerpt')->nullable()->after('book_isbn');
            }
            if (!Schema::hasColumn('cultural_revisions', 'cultural_media_id')) {
                $table->foreignId('cultural_media_id')->nullable()->constrained('cultural_media')->nullOnDelete()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cultural_revisions', function (Blueprint $table) {
            if (Schema::hasColumn('cultural_revisions', 'cultural_media_id')) {
                $table->dropConstrainedForeignId('cultural_media_id');
            }
            $columns = [
                'target_section', 'image_path', 'video_url', 'video_path',
                'book_title', 'book_author', 'book_year', 'book_publisher', 'book_isbn', 'book_excerpt'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('cultural_revisions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('cultural_media', function (Blueprint $table) {
            $columns = [
                'section', 'external_url', 'book_title', 'book_author',
                'book_year', 'book_publisher', 'book_isbn', 'book_excerpt'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('cultural_media', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

