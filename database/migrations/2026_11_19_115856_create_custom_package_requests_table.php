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
        Schema::create('custom_package_requests', function (Blueprint $table) {
            $table->id();
            $table->string('owner_token', 64)->index();
            $table->string('name', 150);
            $table->string('departure_city', 100);
            $table->unsignedTinyInteger('days');
            $table->unsignedTinyInteger('nights');
            $table->text('description')->nullable();
            $table->json('itinerary');
            $table->json('inclusions')->nullable();
            $table->json('booking_details')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->text('admin_response')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_package_requests');
    }
};
