<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameInvoiceTaxPercentageToLegacyTypo extends Migration
{
    /**
     * App code (and original Cutera schema) uses tax_percenatage.
     * Fresh crmsmart tables were created with tax_percentage, so invoice save 500s.
     */
    public function up()
    {
        $tables = [
            'invoice_details',
            'package_bundles',
            'package_services',
            'bundle_package_services',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'tax_percenatage')) {
                continue;
            }
            if (! Schema::hasColumn($table, 'tax_percentage')) {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` CHANGE `tax_percentage` `tax_percenatage` DOUBLE(11,2) NOT NULL DEFAULT 0.00");
        }

        if (Schema::hasTable('invoices') && Schema::hasTable('invoice_details')) {
            DB::table('invoices')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('invoice_details')
                        ->whereColumn('invoice_details.invoice_id', 'invoices.id')
                        ->whereNull('invoice_details.deleted_at');
                })
                ->where('created_at', '>=', now()->subDays(2))
                ->delete();
        }
    }

    public function down()
    {
        $tables = [
            'invoice_details',
            'package_bundles',
            'package_services',
            'bundle_package_services',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'tax_percentage')) {
                continue;
            }
            if (! Schema::hasColumn($table, 'tax_percenatage')) {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` CHANGE `tax_percenatage` `tax_percentage` DOUBLE(11,2) NOT NULL DEFAULT 0.00");
        }
    }
}
