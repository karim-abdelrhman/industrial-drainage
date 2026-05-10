<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violation_rules', function (Blueprint $table) {
            $table->index('pollutant_id', 'vr_pollutant_idx');
            $table->dropForeign('violation_rules_pollutant_id_foreign');
            $table->dropIndex('vr_pollutant_activity');
            $table->dropColumn('activity_type');
            $table->foreign('pollutant_id', 'violation_rules_pollutant_id_foreign')
                ->references('id')->on('pollutants')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('violation_rules', function (Blueprint $table) {
            $table->string('activity_type', 20)->after('pollutant_id');
            $table->dropForeign('violation_rules_pollutant_id_foreign');
            $table->dropIndex('vr_pollutant_idx');
            $table->index(['pollutant_id', 'activity_type'], 'vr_pollutant_activity');
            $table->foreign('pollutant_id', 'violation_rules_pollutant_id_foreign')
                ->references('id')->on('pollutants')->restrictOnDelete();
        });
    }
};
