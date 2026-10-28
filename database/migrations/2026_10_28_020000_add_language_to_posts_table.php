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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('language', 60)->nullable()->after('privacy');
            $table->text('oral_transcript')->nullable()->after('content');
            $table->boolean('is_ai_formatted')->default(false)->after('oral_transcript');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['language', 'oral_transcript', 'is_ai_formatted']);
        });
    }
};
