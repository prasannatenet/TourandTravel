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
        Schema::table('package_inclusion_package', function (Blueprint $table) {
            $table->index(['package_inclusion_id', 'tour_package_id'], 'package_inclusion_package_foreign_keys_index');
        });

        Schema::table('package_inclusion_package', function (Blueprint $table) {
            $table->dropPrimary();
            $table->id()->first();
        });

        DB::table('package_inclusion_package')
            ->where('quantity', '>', 1)
            ->orderBy('id')
            ->each(function (object $item): void {
                $quantity = (int) $item->quantity;
                $attributes = (array) $item;
                unset($attributes['id']);
                $attributes['quantity'] = 1;

                DB::table('package_inclusion_package')
                    ->where('id', $item->id)
                    ->update(['quantity' => 1]);

                for ($copy = 1; $copy < $quantity; $copy++) {
                    DB::table('package_inclusion_package')->insert($attributes);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('package_inclusion_package')
            ->select('tour_package_id', 'package_inclusion_id')
            ->groupBy('tour_package_id', 'package_inclusion_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists()) {
            throw new RuntimeException('Repeated package inclusion entries must be consolidated before rolling back this migration.');
        }

        Schema::table('package_inclusion_package', function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id');
            $table->primary(['package_inclusion_id', 'tour_package_id']);
            $table->dropIndex('package_inclusion_package_foreign_keys_index');
        });
    }
};
