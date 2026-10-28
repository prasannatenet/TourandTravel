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
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('hotel_room_types')->restrictOnDelete();
            $table->string('name', 100);
            $table->json('images')->nullable();
            $table->text('description');
            $table->text('highlights')->nullable();
            $table->text('key_points')->nullable();
            $table->text('amenities')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'room_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_rooms');
    }
};
