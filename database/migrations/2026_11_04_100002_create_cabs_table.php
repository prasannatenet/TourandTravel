<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cabs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cab_associate_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('registration_number')->unique();
            $table->string('type');
            $table->integer('capacity');
            $table->decimal('rate_per_km', 10, 2);
            $table->decimal('base_fare', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamps();

            $table->index('cab_associate_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabs');
    }
};
