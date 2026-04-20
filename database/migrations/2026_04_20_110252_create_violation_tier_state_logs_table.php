<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_tier_state_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('previous_tier')->unsigned();
            $table->tinyInteger('new_tier')->unsigned();
            $table->timestamp('changed_at');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('violation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_tier_state_logs');
    }
};
