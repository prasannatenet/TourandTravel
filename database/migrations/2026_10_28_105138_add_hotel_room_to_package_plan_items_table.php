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
        Schema::table('package_plan_items', function (Blueprint $table): void {
            $table->foreignId('hotel_room_id')
                ->nullable()
                ->after('city_place_id')
                ->constrained('hotel_rooms')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_plan_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('hotel_room_id');
        });
    }
};
