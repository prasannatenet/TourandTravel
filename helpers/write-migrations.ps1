$paths = @(
    'd:\LARAVEL\tourandtravel\ tour-travel\database\migrations\2026_10_14_093227_create_tour_packages_table.php',
    'd:\LARAVEL\tourandtravel\ tour-travel\database\migrations\2026_10_14_093226_create_package_destination_pivots.php'
);

$packageMigration = @'
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
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('departure_city')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->unsignedSmallInteger('days')->default(1);
            $table->unsignedSmallInteger('nights')->default(1);
            $table->unsignedInteger('views')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_packages');
    }
};
'@;

$date = Get-Date -Format 'yyyy_MM_dd_HHmmss';

foreach ($p in $paths) {
    $name = Split-Path $p -Leaf;
    $new = $packageMigration -replace '2026_10_14_093227_create_tour_packages_table', $date + '_' + $name;
    Set-Content -Path $p -Value $new -NoNewline -Encoding UTF8;
    Write-Host 'WROTE' $p;
}
