<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('violation_rules', 'activity_type')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        Schema::table('violation_rules', function (Blueprint $table) use ($driver): void {
            if ($driver !== 'sqlite') {
                $table->index('pollutant_id', 'vr_pollutant_idx');
                $table->dropForeign('violation_rules_pollutant_id_foreign');
            }

            $table->dropIndex('vr_pollutant_activity');
            $table->dropColumn('activity_type');

            if ($driver !== 'sqlite') {
                $table->foreign('pollutant_id', 'violation_rules_pollutant_id_foreign')
                    ->references('id')->on('pollutants')->restrictOnDelete();
            }
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('violation_rules', function (Blueprint $table) use ($driver): void {
            $table->string('activity_type', 20)->after('pollutant_id');

            if ($driver !== 'sqlite') {
                $table->dropForeign('violation_rules_pollutant_id_foreign');
                $table->dropIndex('vr_pollutant_idx');
                $table->index(['pollutant_id', 'activity_type'], 'vr_pollutant_activity');
                $table->foreign('pollutant_id', 'violation_rules_pollutant_id_foreign')
                    ->references('id')->on('pollutants')->restrictOnDelete();
            }
        });
    }
};
