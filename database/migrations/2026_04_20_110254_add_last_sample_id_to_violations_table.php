<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->foreignId('establishment_id_fk')
                ->nullable()
                ->after('establishment_id')
                ->constrained('establishments')
                ->nullOnDelete();

            $table->foreignId('last_sample_id')
                ->nullable()
                ->constrained('samples')
                ->nullOnDelete();

            $table->timestamp('last_evaluated_at')->nullable();

            $table->index('last_sample_id');
        });
    }

    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropForeign(['establishment_id_fk']);
            $table->dropForeign(['last_sample_id']);
            $table->dropColumn(['establishment_id_fk', 'last_sample_id', 'last_evaluated_at']);
        });
    }
};
