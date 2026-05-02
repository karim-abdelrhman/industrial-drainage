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
        Schema::table('pollutant_limits', function (Blueprint $table) {
            $table->decimal('price_per_unit', 10, 4)->after('max_value')->comment('Flat rate EGP per m³ for compliant readings');
        });
    }

    public function down(): void
    {
        Schema::table('pollutant_limits', function (Blueprint $table) {
            $table->dropColumn('price_per_unit');
        });
    }
};
