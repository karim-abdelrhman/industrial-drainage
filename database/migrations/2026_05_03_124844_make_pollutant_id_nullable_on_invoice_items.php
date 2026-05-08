<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['pollutant_id']);
            $table->foreignId('pollutant_id')->nullable()->change();
            $table->foreign('pollutant_id')->references('id')->on('pollutants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['pollutant_id']);
            $table->foreignId('pollutant_id')->nullable(false)->change();
            $table->foreign('pollutant_id')->references('id')->on('pollutants')->restrictOnDelete();
        });
    }
};
