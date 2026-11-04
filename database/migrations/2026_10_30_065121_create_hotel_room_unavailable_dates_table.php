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
        Schema::create('hotel_room_unavailable_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_room_id')->constrained('hotel_rooms')->cascadeOnDelete();
            $table->date('date');
            $table->enum('reason', ['admin_blocked', 'package'])->default('admin_blocked');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['hotel_room_id', 'date']);
            $table->index(['hotel_room_id', 'date']);
            $table->foreign('package_id')->references('id')->on('tour_packages')->nullOnDelete();
            $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_room_unavailable_dates');
    }
};
