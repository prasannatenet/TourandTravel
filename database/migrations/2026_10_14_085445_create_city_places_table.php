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
        Schema::create('city_places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('place_description');
            $table->text('description');
            $table->string('primary_image');
            $table->json('secondary_images')->nullable();
            $table->decimal('visit_duration_hours', 4, 1);
            $table->string('best_time_to_visit');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['city_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_places');
    }
};
