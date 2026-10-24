<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'website')) {
                $table->string('website')->nullable()->after('location');
            }
            if (!Schema::hasColumn('users', 'cover_photo')) {
                $table->string('cover_photo')->nullable()->after('avatar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'website')) {
                $table->dropColumn('website');
            }
            if (Schema::hasColumn('users', 'cover_photo')) {
                $table->dropColumn('cover_photo');
            }
        });
    }
};
