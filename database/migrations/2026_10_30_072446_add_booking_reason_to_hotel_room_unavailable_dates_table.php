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
        Schema::table('hotel_room_unavailable_dates', function (Blueprint $table): void {
            $table->enum('reason', ['admin_blocked', 'package', 'booking'])->default('admin_blocked')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_room_unavailable_dates', function (Blueprint $table): void {
            $table->enum('reason', ['admin_blocked', 'package'])->default('admin_blocked')->change();
        });
    }
};
