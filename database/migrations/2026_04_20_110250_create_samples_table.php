<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained()->restrictOnDelete();
            $table->string('sample_number', 50)->unique();
            $table->date('sample_date');
            $table->string('collected_by', 150)->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();

            $table->index(['establishment_id', 'sample_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
