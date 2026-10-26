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
        Schema::create('package_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_place_id')->nullable()->constrained('city_places')->nullOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->time('start_time');
            $table->decimal('additional_price', 10, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['tour_package_id', 'day_number', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_plan_items');
    }
};
