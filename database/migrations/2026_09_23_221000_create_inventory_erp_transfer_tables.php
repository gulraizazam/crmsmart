<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3 — Transfers with in-transit tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('transfer_no', 40);
            $table->unsignedBigInteger('from_store_id');
            $table->unsignedBigInteger('to_store_id');
            $table->string('status', 20)->default('draft'); // draft|approved|in_transit|completed|cancelled
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->unsignedBigInteger('dispatched_by')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->unsignedBigInteger('dispatch_document_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['account_id', 'transfer_no']);
            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'from_store_id']);
            $table->index(['account_id', 'to_store_id']);
            $table->index(['account_id', 'transfer_date']);
        });

        Schema::create('inv_transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id');
            $table->unsignedInteger('line_no')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity_requested', 16, 4);
            $table->decimal('quantity_dispatched', 16, 4)->default(0);
            $table->decimal('quantity_received', 16, 4)->default(0);
            $table->decimal('unit_cost', 16, 4)->nullable(); // locked at dispatch from source avg
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->foreign('transfer_id')
                ->references('id')
                ->on('inv_transfers')
                ->onDelete('cascade');
            $table->index(['item_id']);
        });

        Schema::table('inv_stock_document_lines', function (Blueprint $table) {
            $table->unsignedBigInteger('transfer_line_id')->nullable()->after('purchase_order_line_id');
            $table->index('transfer_line_id');
        });
    }

    public function down(): void
    {
        Schema::table('inv_stock_document_lines', function (Blueprint $table) {
            $table->dropIndex(['transfer_line_id']);
            $table->dropColumn('transfer_line_id');
        });

        Schema::dropIfExists('inv_transfer_lines');
        Schema::dropIfExists('inv_transfers');
    }
};
