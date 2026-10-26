<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('package_plan_items', function (Blueprint $table) {
            $table->foreignId('package_inclusion_package_id')
                ->nullable()
                ->after('package_inclusion_id')
                ->constrained('package_inclusion_package')
                ->cascadeOnDelete();
        });

        DB::table('package_plan_items')
            ->whereNotNull('package_inclusion_id')
            ->orderBy('id')
            ->each(function (object $planItem): void {
                $inclusionItemId = DB::table('package_inclusion_package')
                    ->where('tour_package_id', $planItem->tour_package_id)
                    ->where('package_inclusion_id', $planItem->package_inclusion_id)
                    ->value('id');

                if ($inclusionItemId) {
                    DB::table('package_plan_items')
                        ->where('id', $planItem->id)
                        ->update(['package_inclusion_package_id' => $inclusionItemId]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_plan_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_inclusion_package_id');
        });
    }
};
