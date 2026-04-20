<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establishments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('activity_type', 20);
            $table->string('address', 300)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('activity_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishments');
    }
};
