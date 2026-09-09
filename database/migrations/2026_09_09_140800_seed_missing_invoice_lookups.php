<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedMissingInvoiceLookups extends Migration
{
    /**
     * Fresh crmsmart DBs were copied without invoice_statuses / payment_modes.
     * Create Invoice then 500s on InvoiceStatuses::where('slug','paid')->id.
     */
    public function up()
    {
        $now = now();
        $accountId = DB::table('accounts')->orderBy('id')->value('id');

        if ((int) DB::table('invoice_statuses')->count() === 0) {
            DB::table('invoice_statuses')->insert([
                ['id' => 1, 'name' => 'Pending', 'slug' => 'pending', 'account_id' => $accountId, 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 2, 'name' => 'Invoiced', 'slug' => 'invoiced', 'account_id' => $accountId, 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 3, 'name' => 'Paid', 'slug' => 'paid', 'account_id' => $accountId, 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 4, 'name' => 'Cancelled', 'slug' => 'cancelled', 'account_id' => $accountId, 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ]);
        } elseif (! DB::table('invoice_statuses')->where('slug', 'paid')->exists()) {
            DB::table('invoice_statuses')->insert([
                'name' => 'Paid',
                'slug' => 'paid',
                'account_id' => $accountId,
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ((int) DB::table('payment_modes')->count() === 0) {
            DB::table('payment_modes')->insert([
                ['id' => 1, 'name' => 'Cash', 'type' => 'application', 'payment_type' => '1', 'sort_number' => 1, 'account_id' => $accountId, 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 2, 'name' => 'Card', 'type' => 'application', 'payment_type' => '2', 'sort_number' => 2, 'account_id' => $accountId, 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 3, 'name' => 'Bank/Wire Transfer', 'type' => 'application', 'payment_type' => '4', 'sort_number' => 3, 'account_id' => $accountId, 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 4, 'name' => 'Settle Amount', 'type' => 'system', 'payment_type' => '6', 'sort_number' => 4, 'account_id' => $accountId, 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ]);
        } elseif (! DB::table('payment_modes')->where('payment_type', '6')->exists()) {
            DB::table('payment_modes')->insert([
                'name' => 'Settle Amount',
                'type' => 'system',
                'payment_type' => '6',
                'sort_number' => 99,
                'account_id' => $accountId,
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        if (DB::table('invoices')->count() === 0) {
            DB::table('invoice_statuses')->whereIn('slug', ['pending', 'invoiced', 'paid', 'cancelled'])->delete();
        }

        if (DB::table('package_advances')->count() === 0) {
            DB::table('payment_modes')->whereIn('name', ['Cash', 'Card', 'Bank/Wire Transfer', 'Settle Amount'])->delete();
        }
    }
}
