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
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('primary_image')->nullable();
            $table->json('secondary_images')->nullable();
        });

        DB::table('hotels')
            ->select(['id', 'hotel_images'])
            ->whereNotNull('hotel_images')
            ->orderBy('id')
            ->chunkById(100, function ($hotels): void {
                foreach ($hotels as $hotel) {
                    $images = json_decode($hotel->hotel_images, true, flags: JSON_THROW_ON_ERROR);

                    if (! is_array($images)) {
                        continue;
                    }

                    DB::table('hotels')
                        ->where('id', $hotel->id)
                        ->update([
                            'primary_image' => $images[0] ?? null,
                            'secondary_images' => json_encode(array_values(array_slice($images, 1)), JSON_THROW_ON_ERROR),
                        ]);
                }
            });

        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('hotel_images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->json('hotel_images')->nullable();
        });

        DB::table('hotels')
            ->select(['id', 'primary_image', 'secondary_images'])
            ->orderBy('id')
            ->chunkById(100, function ($hotels): void {
                foreach ($hotels as $hotel) {
                    $secondaryImages = $hotel->secondary_images === null
                        ? []
                        : json_decode($hotel->secondary_images, true, flags: JSON_THROW_ON_ERROR);
                    $images = array_values(array_filter(
                        array_merge([$hotel->primary_image], is_array($secondaryImages) ? $secondaryImages : []),
                        fn (?string $image): bool => $image !== null,
                    ));

                    DB::table('hotels')
                        ->where('id', $hotel->id)
                        ->update([
                            'hotel_images' => json_encode($images, JSON_THROW_ON_ERROR),
                        ]);
                }
            });

        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['primary_image', 'secondary_images']);
        });
    }
};
