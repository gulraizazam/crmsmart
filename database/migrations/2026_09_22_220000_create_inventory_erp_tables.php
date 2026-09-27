<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Phase 0 — Inventory ERP foundation.
 * New module (inv_*). Does not alter legacy inventory / products / stocks tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('sku', 64);
            $table->string('name');
            $table->string('item_type', 20)->default('tradable'); // tradable|consumable
            $table->string('uom', 20)->default('pcs');
            $table->boolean('active')->default(true);
            $table->boolean('track_batch')->default(false); // reserved for Phase 7
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['account_id', 'sku']);
            $table->index(['account_id', 'active']);
        });

        Schema::create('inv_stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('name');
            $table->string('store_type', 30)->default('warehouse'); // warehouse|centre_store|retail
            $table->unsignedBigInteger('location_id')->nullable(); // optional link to CRM centre
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'active']);
            $table->index(['account_id', 'location_id']);
        });

        Schema::create('inv_document_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('document_type', 30);
            $table->unsignedSmallInteger('year');
            $table->string('prefix', 20);
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['account_id', 'document_type', 'year'], 'inv_doc_seq_unique');
        });

        Schema::create('inv_stock_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('document_no', 40);
            $table->string('document_type', 30);
            $table->string('status', 20)->default('draft'); // draft|posted|reversed
            $table->date('document_date');
            $table->text('notes')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('reversal_of_id')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['account_id', 'document_no']);
            $table->index(['account_id', 'document_type', 'status']);
            $table->index(['account_id', 'document_date']);
            $table->index(['reversal_of_id']);
        });

        Schema::create('inv_stock_document_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedInteger('line_no')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('store_id');
            $table->string('direction', 10); // in|out
            $table->decimal('quantity', 16, 4);
            $table->decimal('unit_cost', 16, 4)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->foreign('document_id')
                ->references('id')
                ->on('inv_stock_documents')
                ->onDelete('cascade');
            $table->index(['item_id', 'store_id']);
        });

        Schema::create('inv_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('document_line_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('store_id');
            $table->string('direction', 10); // in|out
            $table->decimal('quantity', 16, 4);
            $table->decimal('unit_cost', 16, 4)->default(0);
            $table->decimal('balance_qty_after', 16, 4);
            $table->decimal('avg_cost_after', 16, 4)->default(0);
            $table->timestamp('moved_at');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'item_id', 'store_id']);
            $table->index(['document_id']);
            $table->index(['moved_at']);
        });

        Schema::create('inv_stock_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('store_id');
            $table->decimal('quantity', 16, 4)->default(0);
            $table->decimal('avg_cost', 16, 4)->default(0);
            $table->timestamps();

            $table->unique(['item_id', 'store_id']);
            $table->index(['account_id', 'store_id']);
        });

        $this->seedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_stock_balances');
        Schema::dropIfExists('inv_stock_movements');
        Schema::dropIfExists('inv_stock_document_lines');
        Schema::dropIfExists('inv_stock_documents');
        Schema::dropIfExists('inv_document_sequences');
        Schema::dropIfExists('inv_stores');
        Schema::dropIfExists('inv_items');

        $names = [
            'inv_erp_manage',
            'inv_item_manage',
            'inv_store_manage',
            'inv_move_manage',
            'inv_purchase_manage',
            'inv_transfer_manage',
            'inv_adjust_manage',
            'inv_report_manage',
        ];

        $parent = Permission::where('name', 'inv_erp_manage')->first();
        if ($parent) {
            Permission::where('parent_id', $parent->id)->delete();
            $parent->delete();
        } else {
            Permission::whereIn('name', $names)->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function seedPermissions(): void
    {
        $guardName = 'web';
        $parent = Permission::where('name', 'inv_erp_manage')->first();

        if (! $parent) {
            $maxSort = (int) (Permission::where('main_group', 1)->max('sort_order') ?? 0);
            $parent = Permission::create([
                'name' => 'inv_erp_manage',
                'title' => 'Inventory ERP',
                'main_group' => 1,
                'parent_id' => 0,
                'status' => 1,
                'guard_name' => $guardName,
                'sort_order' => $maxSort + 1,
            ]);
        }

        $children = [
            ['name' => 'inv_item_manage', 'title' => 'Items'],
            ['name' => 'inv_store_manage', 'title' => 'Stores'],
            ['name' => 'inv_move_manage', 'title' => 'Post / Reverse Stock'],
            ['name' => 'inv_purchase_manage', 'title' => 'Purchasing'],
            ['name' => 'inv_transfer_manage', 'title' => 'Transfers'],
            ['name' => 'inv_adjust_manage', 'title' => 'Adjustments'],
            ['name' => 'inv_report_manage', 'title' => 'Inventory Reports'],
        ];

        foreach ($children as $index => $child) {
            Permission::firstOrCreate(
                ['name' => $child['name'], 'guard_name' => $guardName],
                [
                    'title' => $child['title'],
                    'main_group' => 0,
                    'parent_id' => $parent->id,
                    'status' => 1,
                    'sort_order' => $index + 1,
                ]
            );
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $names = array_merge(['inv_erp_manage'], array_column($children, 'name'));
        $superAdmin = Role::where('name', 'Super-Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($names);
        }
    }
};
