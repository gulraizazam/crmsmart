<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leads') || Schema::hasColumn('leads', 'assignee_id')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('assignee_id')->nullable()->after('referred_by');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('leads') || ! Schema::hasColumn('leads', 'assignee_id')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('assignee_id');
        });
    }
};
