<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5 — Adjustments & controls.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_adjustment_reasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('code', 40);
            $table->string('name');
            $table->string('direction', 10)->default('both'); // in|out|both
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['account_id', 'code']);
            $table->index(['account_id', 'active']);
        });

        Schema::create('inv_item_store_controls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('store_id');
            $table->decimal('reorder_level', 16, 4)->default(0);
            $table->decimal('reorder_qty', 16, 4)->default(0);
            $table->timestamps();

            $table->unique(['item_id', 'store_id']);
            $table->index(['account_id', 'store_id']);
        });

        Schema::table('inv_stock_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('adjustment_reason_id')->nullable()->after('notes');
            $table->timestamp('approved_at')->nullable()->after('updated_by');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            $table->index('adjustment_reason_id');
        });

        // Seed default reasons for accounts that already have inv items/stores
        $accountIds = DB::table('inv_items')->distinct()->pluck('account_id');
        if ($accountIds->isEmpty()) {
            $accountIds = DB::table('users')->distinct()->pluck('account_id')->filter();
        }

        $defaults = [
            ['code' => 'damage', 'name' => 'Damage', 'direction' => 'out', 'sort_order' => 1],
            ['code' => 'expiry', 'name' => 'Expiry', 'direction' => 'out', 'sort_order' => 2],
            ['code' => 'count_variance', 'name' => 'Count variance', 'direction' => 'both', 'sort_order' => 3],
            ['code' => 'theft', 'name' => 'Theft / loss', 'direction' => 'out', 'sort_order' => 4],
            ['code' => 'other', 'name' => 'Other', 'direction' => 'both', 'sort_order' => 5],
        ];

        $now = now();
        foreach ($accountIds as $accountId) {
            foreach ($defaults as $row) {
                DB::table('inv_adjustment_reasons')->updateOrInsert(
                    ['account_id' => $accountId, 'code' => $row['code']],
                    array_merge($row, [
                        'account_id' => $accountId,
                        'active' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                );
            }
        }
    }

    public function down(): void
    {
        Schema::table('inv_stock_documents', function (Blueprint $table) {
            $table->dropIndex(['adjustment_reason_id']);
            $table->dropColumn(['adjustment_reason_id', 'approved_at', 'approved_by']);
        });

        Schema::dropIfExists('inv_item_store_controls');
        Schema::dropIfExists('inv_adjustment_reasons');
    }
};
