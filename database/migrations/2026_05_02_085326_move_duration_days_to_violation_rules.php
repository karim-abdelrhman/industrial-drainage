<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violation_rules', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_days')->after('to')->comment('مدة المرحلة الواحدة بالأيام');
        });

        Schema::table('violation_rule_tiers', function (Blueprint $table) {
            $table->dropColumn('duration_days');
        });
    }

    public function down(): void
    {
        Schema::table('violation_rule_tiers', function (Blueprint $table) {
            $table->tinyInteger('duration_days')->unsigned()->after('tier_order');
        });

        Schema::table('violation_rules', function (Blueprint $table) {
            $table->dropColumn('duration_days');
        });
    }
};
