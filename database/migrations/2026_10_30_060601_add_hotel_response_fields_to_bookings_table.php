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
        Schema::table('bookings', function (Blueprint $table): void {
            $table->text('hotel_response')->nullable()->after('hotel_booking_requested_at');
            $table->timestamp('hotel_responded_at')->nullable()->after('hotel_response');
            $table->unsignedInteger('extra_bed_count')->default(0)->after('room_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['hotel_response', 'hotel_responded_at', 'extra_bed_count']);
        });
    }
};
