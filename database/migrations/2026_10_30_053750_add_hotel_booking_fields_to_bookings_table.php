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
            $table->date('check_in_date')->nullable()->after('country');
            $table->date('check_out_date')->nullable()->after('check_in_date');
            $table->integer('room_count')->default(1)->after('check_out_date');
            $table->unsignedBigInteger('hotel_room_id')->nullable()->after('room_count');
            $table->enum('hotel_request_status', ['pending', 'requested', 'confirmed', 'cancelled'])->default('pending')->after('hotel_room_id');
            $table->timestamp('hotel_booking_requested_at')->nullable()->after('hotel_request_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn([
                'check_in_date',
                'check_out_date',
                'room_count',
                'hotel_room_id',
                'hotel_request_status',
                'hotel_booking_requested_at',
            ]);
        });
    }
};
