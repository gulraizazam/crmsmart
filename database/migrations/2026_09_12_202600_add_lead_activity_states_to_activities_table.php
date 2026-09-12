<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activities')) {
            return;
        }

        Schema::table('activities', function (Blueprint $table) {
            if (! Schema::hasColumn('activities', 'previous_state')) {
                $table->text('previous_state')->nullable()->after('description');
            }
            if (! Schema::hasColumn('activities', 'new_state')) {
                $table->text('new_state')->nullable()->after('previous_state');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('activities')) {
            return;
        }

        Schema::table('activities', function (Blueprint $table) {
            foreach (['previous_state', 'new_state'] as $column) {
                if (Schema::hasColumn('activities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
