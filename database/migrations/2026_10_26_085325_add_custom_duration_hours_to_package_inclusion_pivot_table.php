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
        Schema::table('package_inclusion_package', function (Blueprint $table) {
            $table->decimal('custom_duration_hours', 5, 2)->nullable()->after('custom_scheduled_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_inclusion_package', function (Blueprint $table) {
            $table->dropColumn('custom_duration_hours');
        });
    }
};
