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
        Schema::table('absens', function (Blueprint $table) {
            $table->string('distance_check_in')->nullable()->after('longitude_check_out')->comment('Distance from check in location to office location');
            $table->string('distance_check_out')->nullable()->after('longitude_check_out')->comment('Distance from check out location to office location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absens', function (Blueprint $table) {
            $table->dropColumn('distance_check_in');
            $table->dropColumn('distance_check_out');
        });
    }
};
