<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 — Period locks for costing / finance control.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_period_locks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month'); // 1-12
            $table->timestamp('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->unsignedBigInteger('unlocked_by')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'period_year', 'period_month'], 'inv_period_locks_unique');
            $table->index(['account_id', 'period_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_period_locks');
    }
};
