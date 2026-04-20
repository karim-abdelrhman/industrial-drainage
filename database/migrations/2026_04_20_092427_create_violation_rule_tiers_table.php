<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_rule_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_rule_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('tier_order')->unsigned();
            $table->tinyInteger('duration_months')->unsigned();
            $table->decimal('price_per_unit', 10, 4);
            $table->timestamps();

            $table->unique(['violation_rule_id', 'tier_order'], 'vrt_unique_order');
            $table->index('violation_rule_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_rule_tiers');
    }
};
