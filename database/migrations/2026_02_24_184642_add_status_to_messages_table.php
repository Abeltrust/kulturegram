<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'status')) {
                $table->string('status')->default('sent')->after('content');
            }
            if (Schema::hasColumn('messages', 'is_read') && \Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                $table->dropColumn('is_read');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('attachment');
            }
            if (Schema::hasColumn('messages', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
