<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE events MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'workshop'");
        } else {
            Schema::table('events', function (Blueprint $table) {
                $table->string('type', 50)->default('workshop')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE events MODIFY COLUMN type ENUM('workshop', 'conference', 'cultural', 'exhibition') NOT NULL DEFAULT 'workshop'");
        }
    }
};
