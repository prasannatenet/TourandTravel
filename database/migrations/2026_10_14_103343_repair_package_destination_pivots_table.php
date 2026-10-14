<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_destination_pivots', function (Blueprint $table): void {
            if (! Schema::hasColumn('package_destination_pivots', 'package_id')) {
                $table->foreignId('package_id')->constrained('tour_packages')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('package_destination_pivots', 'destination_type')) {
                $table->string('destination_type');
            }

            if (! Schema::hasColumn('package_destination_pivots', 'destination_id')) {
                $table->unsignedBigInteger('destination_id');
            }

            if (! Schema::hasColumn('package_destination_pivots', 'position')) {
                $table->unsignedInteger('position')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('package_destination_pivots', function (Blueprint $table): void {
            $table->dropColumn(['package_id', 'destination_type', 'destination_id', 'position']);
        });
    }
};
