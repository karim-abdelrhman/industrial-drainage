<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            if (! Schema::hasColumn('samples', 'lab_report_image')) {
                $table->string('lab_report_image')->nullable()->after('sample_date');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'sample_id')) {
                $table->foreignId('sample_id')->nullable()->after('establishment_id')
                    ->constrained('samples')->nullOnDelete();
            }

            // Drop the establishment FK that uses inv_unique_period as backing index
            $table->dropForeign(['establishment_id']);
            $table->dropUnique('inv_unique_period');

            // Re-add establishment FK (will use PK index on establishments)
            $table->foreign('establishment_id')->references('id')->on('establishments')->restrictOnDelete();

            $table->unique(['establishment_id', 'sample_id'], 'inv_unique_sample');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('inv_unique_sample');
            $table->dropForeign(['establishment_id']);
            $table->unique(['establishment_id', 'billing_month'], 'inv_unique_period');
            $table->foreign('establishment_id')->references('id')->on('establishments')->restrictOnDelete();
            $table->dropForeign(['sample_id']);
            $table->dropColumn('sample_id');
        });

        Schema::table('samples', function (Blueprint $table) {
            $table->dropColumn('lab_report_image');
        });
    }
};
