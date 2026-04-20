<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pollutant_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pollutant_id')->constrained()->restrictOnDelete();
            $table->string('activity_type', 20);
            $table->decimal('min_value', 10, 4);
            $table->decimal('max_value', 10, 4)->nullable();
            $table->string('status', 30);
            $table->tinyInteger('sort_order')->unsigned()->default(0);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['pollutant_id', 'activity_type', 'effective_from', 'effective_to'], 'pl_lookup');
            $table->index('effective_to');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pollutant_limits');
    }
};
