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
        Schema::create('cab_unavailable_dates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cab_id')->constrained('cabs')->cascadeOnDelete();
            $table->date('date');
            $table->timestamps();

            $table->unique(['cab_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cab_unavailable_dates');
    }
};
