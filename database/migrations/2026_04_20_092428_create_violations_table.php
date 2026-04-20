<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('establishment_id');
            $table->foreignId('pollutant_id')->constrained()->restrictOnDelete();
            $table->foreignId('violation_rule_id')->constrained()->restrictOnDelete();
            $table->decimal('detected_value', 10, 4);
            $table->date('start_date');
            $table->tinyInteger('current_tier')->unsigned()->default(1);
            $table->date('current_tier_start_date');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('establishment_id');
            $table->index(['status', 'establishment_id'], 'v_status_establishment');
            $table->index('current_tier_start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
