<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pollutant_id')->constrained()->restrictOnDelete();
            $table->decimal('detected_value', 10, 4);
            $table->timestamps();

            $table->unique(['sample_id', 'pollutant_id'], 'sr_unique_pollutant');
            $table->index('pollutant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_readings');
    }
};
