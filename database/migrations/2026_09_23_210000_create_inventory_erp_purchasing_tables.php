<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — Purchasing: suppliers, purchase orders, GRN line link.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('name');
            $table->string('phone', 50)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('address', 500)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'active']);
            $table->index(['account_id', 'name']);
        });

        Schema::create('inv_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('po_number', 40);
            $table->unsignedBigInteger('supplier_id');
            $table->string('status', 20)->default('draft'); // draft|ordered|closed|cancelled
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['account_id', 'po_number']);
            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'supplier_id']);
            $table->index(['account_id', 'order_date']);
        });

        Schema::create('inv_purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedInteger('line_no')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('store_id'); // default receive store
            $table->decimal('quantity_ordered', 16, 4);
            $table->decimal('quantity_received', 16, 4)->default(0);
            $table->decimal('unit_cost', 16, 4);
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('inv_purchase_orders')
                ->onDelete('cascade');
            $table->index(['item_id', 'store_id']);
        });

        Schema::table('inv_stock_document_lines', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_line_id')->nullable()->after('notes');
            $table->index('purchase_order_line_id');
        });
    }

    public function down(): void
    {
        Schema::table('inv_stock_document_lines', function (Blueprint $table) {
            $table->dropIndex(['purchase_order_line_id']);
            $table->dropColumn('purchase_order_line_id');
        });

        Schema::dropIfExists('inv_purchase_order_lines');
        Schema::dropIfExists('inv_purchase_orders');
        Schema::dropIfExists('inv_suppliers');
    }
};
