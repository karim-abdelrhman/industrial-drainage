<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_violation_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sample_reading_id')->constrained()->cascadeOnDelete();
            $table->foreignId('establishment_id')->constrained()->restrictOnDelete();
            $table->foreignId('pollutant_id')->constrained()->restrictOnDelete();
            $table->foreignId('violation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('violation_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('detected_value', 10, 4);
            $table->tinyInteger('tier_order_at_time')->unsigned()->nullable();
            $table->decimal('price_per_unit_at_time', 10, 4)->nullable();
            $table->string('evaluation_result', 20);
            $table->timestamps();

            $table->index(['establishment_id', 'pollutant_id']);
            $table->index('sample_id');
            $table->index('violation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_violation_snapshots');
    }
};
