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
        Schema::table('resonances', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('post_id')
                ->constrained('resonances')
                ->cascadeOnDelete();
            $table->unsignedInteger('taps_count')->default(0)->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resonances', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'taps_count']);
        });
    }
};
