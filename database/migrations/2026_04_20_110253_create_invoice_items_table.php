<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('violation_id')->constrained()->restrictOnDelete();
            $table->foreignId('pollutant_id')->constrained()->restrictOnDelete();
            $table->foreignId('violation_rule_id')->constrained()->restrictOnDelete();
            $table->tinyInteger('tier_order')->unsigned();
            $table->decimal('price_per_unit', 10, 4);
            $table->decimal('detected_value', 10, 4);
            $table->decimal('amount', 12, 4);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('violation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
