<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('establishments', 'customer_zone')) {
            Schema::table('establishments', function (Blueprint $table) {
                $table->string('customer_zone', 30)->default('city')->after('location_type');
            });
        }

        if (! Schema::hasColumn('pollutant_limits', 'customer_zone')) {
            Schema::table('pollutant_limits', function (Blueprint $table) {
                $table->string('customer_zone', 30)->nullable()->after('pollutant_id');
            });
        }

        if (! Schema::hasColumn('pollutant_limits', 'min_inclusive')) {
            Schema::table('pollutant_limits', function (Blueprint $table) {
                $table->boolean('min_inclusive')->default(true)->after('max_value');
                $table->boolean('max_inclusive')->default(true)->after('min_inclusive');
            });
        }

        if (Schema::hasColumn('pollutant_limits', 'activity_type')) {
            DB::table('pollutant_limits')->orderBy('id')->each(function (object $limit): void {
                DB::table('pollutant_limits')->where('id', $limit->id)->update([
                    'customer_zone' => $limit->activity_type === 'industrial' ? 'industrial_zone' : 'city',
                ]);
            });

            $indexNames = collect(Schema::getIndexes('pollutant_limits'))->pluck('name');

            if (! $indexNames->contains('pl_pollutant_id_idx')) {
                Schema::table('pollutant_limits', function (Blueprint $table) {
                    $table->index('pollutant_id', 'pl_pollutant_id_idx');
                });
            }

            foreach (Schema::getIndexes('pollutant_limits') as $index) {
                if (! in_array('activity_type', $index['columns'], true) || ($index['primary'] ?? false)) {
                    continue;
                }

                Schema::table('pollutant_limits', function (Blueprint $table) use ($index): void {
                    $table->dropIndex($index['name']);
                });
            }

            Schema::table('pollutant_limits', function (Blueprint $table) {
                $table->dropColumn('activity_type');
            });
        }

        $limitIndexNames = collect(Schema::getIndexes('pollutant_limits'))->pluck('name');
        if (! $limitIndexNames->contains('pl_pollutant_zone')) {
            Schema::table('pollutant_limits', function (Blueprint $table) {
                $table->index(['pollutant_id', 'customer_zone'], 'pl_pollutant_zone');
            });
        }

        if (! Schema::hasColumn('violation_rules', 'from_inclusive')) {
            Schema::table('violation_rules', function (Blueprint $table) {
                $table->boolean('from_inclusive')->default(true)->after('from');
                $table->boolean('to_inclusive')->default(false)->after('to');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('violation_rules', 'from_inclusive')) {
            Schema::table('violation_rules', function (Blueprint $table) {
                $table->dropColumn(['from_inclusive', 'to_inclusive']);
            });
        }

        if (! Schema::hasColumn('pollutant_limits', 'activity_type')) {
            Schema::table('pollutant_limits', function (Blueprint $table) {
                $table->string('activity_type', 20)->nullable()->after('pollutant_id');
            });
        }

        DB::table('pollutant_limits')->orderBy('id')->each(function (object $limit): void {
            DB::table('pollutant_limits')->where('id', $limit->id)->update([
                'activity_type' => $limit->customer_zone === 'industrial_zone' ? 'industrial' : 'commercial',
            ]);
        });

        Schema::table('pollutant_limits', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('pollutant_limits'))->pluck('name');
            if ($indexes->contains('pl_pollutant_zone')) {
                $table->dropIndex('pl_pollutant_zone');
            }
            if ($indexes->contains('pl_pollutant_id_idx')) {
                $table->dropIndex('pl_pollutant_id_idx');
            }
        });

        Schema::table('pollutant_limits', function (Blueprint $table) {
            if (Schema::hasColumn('pollutant_limits', 'customer_zone')) {
                $table->dropColumn(['customer_zone', 'min_inclusive', 'max_inclusive']);
            }
        });

        if (Schema::hasColumn('establishments', 'customer_zone')) {
            Schema::table('establishments', function (Blueprint $table) {
                $table->dropColumn('customer_zone');
            });
        }
    }
};
