<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'shared_post_id')) {
                $table->foreignId('shared_post_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('posts')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('posts', 'tagged_users')) {
                $table->json('tagged_users')->nullable()->after('tags');
            }

            if (!Schema::hasColumn('posts', 'shares_count')) {
                $table->integer('shares_count')->default(0)->after('check_ins_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'shared_post_id')) {
                $table->dropConstrainedForeignId('shared_post_id');
            }
            if (Schema::hasColumn('posts', 'tagged_users')) {
                $table->dropColumn('tagged_users');
            }
            if (Schema::hasColumn('posts', 'shares_count')) {
                $table->dropColumn('shares_count');
            }
        });
    }
};

