<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['violation_id']);
            $table->dropForeign(['violation_rule_id']);

            $table->foreignId('violation_id')->nullable()->change();
            $table->foreignId('violation_rule_id')->nullable()->change();
            $table->tinyInteger('tier_order')->unsigned()->nullable()->change();

            $table->foreign('violation_id')->references('id')->on('violations')->nullOnDelete();
            $table->foreign('violation_rule_id')->references('id')->on('violation_rules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['violation_id']);
            $table->dropForeign(['violation_rule_id']);

            $table->foreignId('violation_id')->nullable(false)->change();
            $table->foreignId('violation_rule_id')->nullable(false)->change();
            $table->tinyInteger('tier_order')->unsigned()->nullable(false)->change();

            $table->foreign('violation_id')->references('id')->on('violations')->restrictOnDelete();
            $table->foreign('violation_rule_id')->references('id')->on('violation_rules')->restrictOnDelete();
        });
    }
};
