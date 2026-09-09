<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPlanidToActivitiesTableIfMissing extends Migration
{
    /**
     * Cutera-era code writes activities.planId (camelCase). Fresh crmsmart
     * copies only have plan_id, so plan save fails on activity insert.
     */
    public function up()
    {
        if (! Schema::hasColumn('activities', 'planId')) {
            Schema::table('activities', function (Blueprint $table) {
                if (Schema::hasColumn('activities', 'plan_id')) {
                    $table->unsignedBigInteger('planId')->nullable()->after('plan_id');
                } else {
                    $table->unsignedBigInteger('planId')->nullable();
                }
            });
        }

        if (Schema::hasColumn('activities', 'planId') && Schema::hasColumn('activities', 'plan_id')) {
            DB::statement('UPDATE activities SET `planId` = `plan_id` WHERE `planId` IS NULL AND `plan_id` IS NOT NULL');
        }
    }

    public function down()
    {
        if (Schema::hasColumn('activities', 'planId')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->dropColumn('planId');
            });
        }
    }
}
