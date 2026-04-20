<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pollutant_id')->constrained()->restrictOnDelete();
            $table->string('activity_type', 20);
            $table->decimal('min_value', 10, 4);
            $table->decimal('max_value', 10, 4)->nullable();
            $table->timestamps();

            $table->index(['pollutant_id', 'activity_type'], 'vr_pollutant_activity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_rules');
    }
};
