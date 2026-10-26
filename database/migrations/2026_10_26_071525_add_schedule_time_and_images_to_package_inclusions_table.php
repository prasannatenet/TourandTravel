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
        Schema::table('package_inclusions', function (Blueprint $table) {
            $table->time('scheduled_time')->nullable();
            $table->string('image_one')->nullable();
            $table->string('image_two')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_inclusions', function (Blueprint $table) {
            $table->dropColumn(['scheduled_time', 'image_one', 'image_two']);
        });
    }
};
