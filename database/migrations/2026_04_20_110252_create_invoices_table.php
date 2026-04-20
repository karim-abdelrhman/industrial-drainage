<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained()->restrictOnDelete();
            $table->date('billing_month');
            $table->string('status', 20)->default('draft');
            $table->decimal('total_amount', 12, 4)->default(0);
            $table->timestamp('issued_at')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['establishment_id', 'billing_month'], 'inv_unique_period');
            $table->index('status');
            $table->index('billing_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
