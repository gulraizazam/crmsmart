<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lead_departments')) {
            Schema::create('lead_departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('active')->default(1);
                $table->unsignedBigInteger('account_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('leads') && ! Schema::hasColumn('leads', 'department_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('location_id');
            });
        }

        if (Schema::hasTable('leads') && ! Schema::hasColumn('leads', 'assigned_to')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('referred_by');
            });
        }

        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'assignee_id')) {
            DB::statement('UPDATE leads SET assigned_to = assignee_id WHERE assigned_to IS NULL AND assignee_id IS NOT NULL');
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('assignee_id');
            });
        }

        if (! Schema::hasTable('lead_department_location')) {
            Schema::create('lead_department_location', function (Blueprint $table) {
                $table->unsignedBigInteger('lead_department_id');
                $table->unsignedBigInteger('location_id');
                $table->primary(['lead_department_id', 'location_id'], 'lead_dept_location_pk');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lead_department_location')) {
            Schema::dropIfExists('lead_department_location');
        }

        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'assigned_to') && ! Schema::hasColumn('leads', 'assignee_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->unsignedBigInteger('assignee_id')->nullable()->after('referred_by');
            });
            DB::statement('UPDATE leads SET assignee_id = assigned_to WHERE assignee_id IS NULL AND assigned_to IS NOT NULL');
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('assigned_to');
            });
        }
    }
};
