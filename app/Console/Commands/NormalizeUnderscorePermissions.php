<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ReflectionMethod;

class NormalizeUnderscorePermissions extends Command
{
    protected $signature = 'permissions:normalize-underscore
                            {--dry-run : Show the plan without writing}
                            {--verify : Only check that code-checked names exist in the DB}';

    protected $description = 'Restore underscore permissions, remap dotted CRM3 grants, and delete dotted duplicates';

    public function handle(): int
    {
        if ($this->option('verify')) {
            return $this->verifyCodePermissions();
        }

        $dry = (bool) $this->option('dry-run');
        $this->info($dry ? 'DRY RUN — no writes' : 'Applying underscore permission normalize');

        $created = [];
        $remapped = 0;
        $collapsed = 0;
        $deleted = 0;

        DB::beginTransaction();
        try {
            $created = $this->restoreCatalog();
            $this->activateDashboardManage();

            $nameToId = Permission::query()->pluck('id', 'name')->all();
            $dotted = Permission::query()->where('name', 'like', '%.%')->get();
            $map = $this->dottedMap();
            $collapse = $this->collapsePrefixes();

            foreach ($dotted as $permission) {
                $target = $map[$permission->name] ?? $this->collapseTarget($permission->name, $collapse, $nameToId);
                if ($target === null) {
                    $this->line('  unmapped (will delete): '.$permission->name);
                    continue;
                }
                if (! isset($nameToId[$target])) {
                    $this->warn('  target missing, skip remap: '.$permission->name.' -> '.$target);
                    continue;
                }
                $count = $this->copyGrants((int) $permission->id, (int) $nameToId[$target]);
                if (isset($map[$permission->name])) {
                    $remapped += $count;
                } else {
                    $collapsed += $count;
                }
            }

            foreach ($this->leftoverMap() as $from => $target) {
                if (! isset($nameToId[$from], $nameToId[$target])) {
                    continue;
                }
                $collapsed += $this->copyGrants((int) $nameToId[$from], (int) $nameToId[$target]);
            }

            $deleted = $this->deleteDottedAndLeftovers();

            $this->newLine();
            $this->info('created: '.count($created));
            if ($created !== []) {
                $this->line('  '.implode(', ', array_slice($created, 0, 80)).(count($created) > 80 ? ' …' : ''));
            }
            $this->info('grants remapped (1:1): '.$remapped);
            $this->info('grants collapsed to parent: '.$collapsed);
            $this->info('permissions deleted: '.$deleted);

            $verify = $this->verifyCodePermissions();

            if ($dry) {
                DB::rollBack();
                $this->warn('Rolled back (dry-run).');
            } else {
                DB::commit();
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }

            return $verify;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());

            return 1;
        }
    }

    /**
     * @return array<int, string>
     */
    private function restoreCatalog(): array
    {
        $created = [];
        foreach ($this->catalog() as $row) {
            $existing = Permission::query()->where('name', $row['name'])->first();
            $parentId = 0;
            if ($row['parent'] !== null) {
                $parent = Permission::query()->where('name', $row['parent'])->first();
                $parentId = $parent ? (int) $parent->id : 0;
            }
            if ($existing) {
                $existing->update([
                    'title' => $row['title'],
                    'main_group' => $row['parent'] === null ? 1 : 0,
                    'parent_id' => $parentId,
                    'status' => 1,
                    'guard_name' => $existing->guard_name ?: 'web',
                ]);
                continue;
            }
            $created[] = $row['name'];
            Permission::create([
                'name' => $row['name'],
                'title' => $row['title'],
                'main_group' => $row['parent'] === null ? 1 : 0,
                'parent_id' => $parentId,
                'status' => 1,
                'guard_name' => 'web',
            ]);
        }

        return $created;
    }

    private function activateDashboardManage(): void
    {
        Permission::query()->where('name', 'dashboard_manage')->update(['status' => 1]);
    }

    /**
     * @return array<int, array{name: string, title: string, parent: string|null}>
     */
    private function catalog(): array
    {
        $rows = [];
        $skip = ['new test patient', 'gfhfg', 'fsdf'];
        $seeder = new PermissionSeeder();
        $method = new ReflectionMethod($seeder, 'permissions');
        $method->setAccessible(true);
        /** @var array<int, array<string, mixed>> $seeded */
        $seeded = $method->invoke($seeder);

        $parents = [];
        foreach ($seeded as $item) {
            if ((int) $item['main_group'] === 1) {
                $parents[] = $item['name'];
            }
        }
        foreach ($this->extraParents() as $name => $title) {
            $parents[] = $name;
            $rows[] = ['name' => $name, 'title' => $title, 'parent' => null];
        }

        foreach ($seeded as $item) {
            $name = (string) $item['name'];
            if (in_array($name, $skip, true)) {
                continue;
            }
            $parent = null;
            if ((int) $item['main_group'] !== 1) {
                $parent = $this->inferParent($name, $parents) ?? $this->specialParent($name);
            }
            $rows[] = [
                'name' => $name,
                'title' => (string) $item['title'],
                'parent' => $parent,
            ];
        }

        foreach ($this->extraChildren() as $child) {
            $rows[] = $child;
        }

        $unique = [];
        foreach ($rows as $row) {
            $unique[$row['name']] = $row;
        }

        return array_values($unique);
    }

    /**
     * @return array<string, string>
     */
    private function extraParents(): array
    {
        return [
            'treatments_manage' => 'Treatments',
            'cashflow_manage' => 'Cash Flow Management',
            'memberships_manage' => 'Memberships',
            'membershiptypes_manage' => 'Membership Types',
            'vouchers_manage' => 'Vouchers',
            'voucher_types_manage' => 'Voucher Types',
            'inventory_manage' => 'Inventory',
            'brand_manage' => 'Brands',
            'product_manage' => 'Products',
            'order_manage' => 'Orders',
            'feedbacks_manage' => 'Feedbacks',
            'conversion_report_manage' => 'Conversion Report',
            'follow_up_manage' => 'Follow Up Report',
            'inventory_report_manage' => 'Inventory Report',
            'non_converted_customers_manage' => 'Non Converted Customers',
            'followuppatient_manage' => 'Follow-up Patients',
            'staff_wise_arrival_manage' => 'Staff Wise Arrival',
            'csr_dashboard_report' => 'CSR Dashboard',
            'cancellation_reasons_manage' => 'Cancellation Reasons',
            'upselling_report' => 'Upselling Report',
            'consultant_revenue_report' => 'Consultant Revenue Report',
            'packagesadvances_manage' => 'Package Advances',
            'transfer_product_manage' => 'Transfer Products',
            'inventory_refund_manage' => 'Inventory Refunds',
        ];
    }

    /**
     * @return array<int, array{name: string, title: string, parent: string|null}>
     */
    private function extraChildren(): array
    {
        $cashflow = [
            ['cashflow_dashboard', 'View Cash Flow Dashboard'],
            ['cashflow_fdm_view', 'View FDM Screen (Branch Cash)'],
            ['cashflow_expense_create', 'Create Expenses'],
            ['cashflow_expense_edit', 'Edit Expenses (Admin)'],
            ['cashflow_expense_approve', 'Approve/Reject Expenses'],
            ['cashflow_expense_void', 'Void Expenses'],
            ['cashflow_transfer_create', 'Create Cash Transfers'],
            ['cashflow_transfer_edit', 'Edit Cash Transfers'],
            ['cashflow_transfer_void', 'Void Cash Transfers'],
            ['cashflow_vendor_manage', 'Manage Vendors'],
            ['cashflow_vendor_ledger_view', 'View Vendor Ledger'],
            ['cashflow_vendor_transaction', 'Record Vendor Transactions'],
            ['cashflow_staff_advance', 'Manage Staff Advances'],
            ['cashflow_staff_advance_edit', 'Edit Staff Advances'],
            ['cashflow_staff_advance_void', 'Void Staff Advances/Returns'],
            ['cashflow_category_manage', 'Manage Expense Categories'],
            ['cashflow_pool_manage', 'Manage Cash Pools'],
            ['cashflow_period_lock', 'Lock/Unlock Periods'],
            ['cashflow_audit_view', 'View Audit Trail'],
            ['cashflow_settings', 'Manage Cash Flow Settings'],
            ['cashflow_reports', 'View Cash Flow Reports'],
            ['cashflow_reports_export', 'Export Cash Flow Reports'],
        ];
        $treatments = [
            ['treatments_display', 'Display'],
            ['treatments_consultancy', 'Manage Consultancy'],
            ['treatments_services', 'Manage Services'],
            ['treatments_edit', 'Edit'],
            ['treatments_export', 'Export'],
            ['treatments_appointment_status', 'Update Appointment Status'],
            ['treatments_invoice', 'Appointment Invoice'],
            ['treatments_patient_card', 'Patient Card'],
            ['treatments_invoice_display', 'Invoice Display'],
            ['treatments_medical_form_manage', 'Medical History Form'],
            ['treatments_edit_after_arrived', 'Edit Appointment After Arrived'],
            ['treatments_destroy', 'Delete'],
            ['treatments_plans_create', 'Plan Create'],
            ['treatments_today', "Today's Appointments"],
            ['treatments_image_manage', 'Images'],
            ['treatments_image_upload', 'Images Upload'],
            ['treatments_image_destroy', 'Images Delete'],
            ['treatments_measurement_manage', 'Measurement'],
            ['treatments_measurement_create', 'Measurements Create'],
            ['treatments_measurement_edit', 'Measurements Edit'],
            ['treatments_medical_create', 'Medical Form Create'],
            ['treatments_medical_edit', 'Medical Form Edit'],
            ['treatments_export_today', 'Today'],
            ['treatments_export_this_month', 'This Month'],
            ['treatments_export_all', 'All'],
            ['treatments_log', 'Log'],
            ['treatments_log_excel', 'Log Excel'],
        ];
        $extras = [
            ['memberships_create', 'Create', 'memberships_manage'],
            ['memberships_edit', 'Edit', 'memberships_manage'],
            ['vouchers_create', 'Create', 'vouchers_manage'],
            ['vouchers_edit', 'Edit', 'vouchers_manage'],
            ['voucher_types_create', 'Create', 'voucher_types_manage'],
            ['voucher_types_edit', 'Edit', 'voucher_types_manage'],
            ['voucher_types_allocate', 'Allocate', 'voucher_types_manage'],
            ['voucher_types_assign', 'Assign', 'voucher_types_manage'],
            ['voucher_types_destroy', 'Delete', 'voucher_types_manage'],
            ['roles_duplicate', 'Duplicate', 'roles_manage'],
            ['brand_create', 'Create', 'brand_manage'],
            ['brand_edit', 'Edit', 'brand_manage'],
            ['brand_destroy', 'Delete', 'brand_manage'],
            ['product_create', 'Create', 'product_manage'],
            ['product_edit', 'Edit', 'product_manage'],
            ['product_destroy', 'Delete', 'product_manage'],
            ['order_create', 'Create', 'order_manage'],
            ['order_edit', 'Edit', 'order_manage'],
            ['order_destroy', 'Delete', 'order_manage'],
            ['dashboard_staff_wise_arrival', 'Staff Wise Arrival', 'dashboard_manage'],
            ['dashboard_doctor_wise_conversion', 'Doctor Wise Conversion', 'dashboard_manage'],
            ['dashboard_upselling_report', 'Upselling Report', 'dashboard_manage'],
            ['view_inactive_discounts', 'View Inactive Discounts', 'discounts_manage'],
            ['appointments_create', 'Create', 'appointments_manage'],
            ['appointments_active', 'Activate', 'appointments_manage'],
            ['appointments_inactive', 'Inactivate', 'appointments_manage'],
            ['appointments_delete', 'Delete', 'appointments_manage'],
            ['appointments_view', 'View', 'appointments_manage'],
            ['appointments_status', 'Status', 'appointments_manage'],
            ['can_edit_doctor', 'Can Edit Doctor', 'appointments_manage'],
            ['can_edit_schedule', 'Can Edit Schedule', 'appointments_manage'],
            ['can_edit_service', 'Can Edit Service', 'appointments_manage'],
            ['update_consultation_doctor', 'Update Consultation Doctor', 'appointments_manage'],
            ['update_consultation_schedule', 'Update Consultation Schedule', 'appointments_manage'],
            ['update_consultation_service', 'Update Consultation Service', 'appointments_manage'],
            ['consultancy_manage', 'Manage Consultancy', 'appointments_manage'],
            ['consultancy_invoice', 'Consultancy Invoice', 'appointments_manage'],
            ['consultancy_invoice_display', 'Consultancy Invoice Display', 'appointments_manage'],
            ['patient_card', 'Patient Card', 'appointments_manage'],
            ['centre_targets_active', 'Activate', 'centre_targets_manage'],
            ['centre_targets_inactive', 'Inactivate', 'centre_targets_manage'],
            ['centre_targets_allocate', 'Allocate', 'centre_targets_manage'],
            ['custom_form_feedbacks_active', 'Activate', 'custom_form_feedbacks_manage'],
            ['custom_form_feedbacks_inactive', 'Inactivate', 'custom_form_feedbacks_manage'],
            ['custom_form_feedbacks_destroy', 'Delete', 'custom_form_feedbacks_manage'],
            ['discounts_active', 'Activate', 'discounts_manage'],
            ['feedbacks_destroy', 'Delete', 'feedbacks_manage'],
            ['finance_general_revenue_reports_account_sales_report', 'Account Sales Report', 'finance_general_revenue_reports_manage'],
            ['inventory_refund', 'Refund', 'inventory_refund_manage'],
            ['invoices_create', 'Create', 'invoices_manage'],
            ['leads_active', 'Activate', 'leads_manage'],
            ['leads_inactive', 'Inactivate', 'leads_manage'],
            ['leads_view', 'View', 'leads_manage'],
            ['memberships_active', 'Activate', 'memberships_manage'],
            ['memberships_inactive', 'Inactivate', 'memberships_manage'],
            ['memberships_destroy', 'Delete', 'memberships_manage'],
            ['memberships_import', 'Import', 'memberships_manage'],
            ['memberships_sort', 'Sort', 'memberships_manage'],
            ['membershiptypes_create', 'Create', 'membershiptypes_manage'],
            ['membershiptypes_edit', 'Edit', 'membershiptypes_manage'],
            ['membershiptypes_destroy', 'Delete', 'membershiptypes_manage'],
            ['membershiptypes_active', 'Activate', 'membershiptypes_manage'],
            ['membershiptypes_inactive', 'Inactivate', 'membershiptypes_manage'],
            ['order_refund_manage', 'Refund', 'order_manage'],
            ['patients_delete', 'Delete', 'patients_manage'],
            ['plans_edit_sold_by', 'Edit Sold By', 'plans_manage'],
            ['product_log', 'Log', 'product_manage'],
            ['product_transfer', 'Transfer', 'product_manage'],
            ['refunds_create', 'Create', 'refunds_manage'],
            ['refunds_edit', 'Edit', 'refunds_manage'],
            ['refunds_destroy', 'Delete', 'refunds_manage'],
            ['refunds_active', 'Activate', 'refunds_manage'],
            ['refunds_inactive', 'Inactivate', 'refunds_manage'],
            ['services_sort', 'Sort', 'services_manage'],
            ['towns_sort', 'Sort', 'towns_manage'],
            ['transfer_product_create', 'Create', 'transfer_product_manage'],
            ['transfer_product_edit', 'Edit', 'transfer_product_manage'],
            ['transfer_product_destroy', 'Delete', 'transfer_product_manage'],
            ['update_treatment_doctor', 'Update Treatment Doctor', 'treatments_manage'],
            ['update_treatment_schedule', 'Update Treatment Schedule', 'treatments_manage'],
            ['update_treatment_service', 'Update Treatment Service', 'treatments_manage'],
            ['view_inactive_appointmentstatuses', 'View Inactive', 'appointment_statuses_manage'],
            ['view_inactive_leadsources', 'View Inactive', 'lead_sources_manage'],
            ['view_inactive_leadstatuses', 'View Inactive', 'lead_statuses_manage'],
            ['view_inactive_paymentmodes', 'View Inactive', 'payment_modes_manage'],
            ['view_inactive_records', 'View Inactive', 'pabao_records_manage'],
            ['view_inactive_smstemplates', 'View Inactive', 'sms_templates_manage'],
            ['view_inactive_warehouse', 'View Inactive', 'inventory_manage'],
            ['voucher_types_active', 'Activate', 'voucher_types_manage'],
            ['voucher_types_inactive', 'Inactivate', 'voucher_types_manage'],
            ['vouchers_destroy', 'Delete', 'vouchers_manage'],
            ['vouchers_view', 'View', 'vouchers_manage'],
        ];

        $rows = [];
        foreach ($cashflow as $item) {
            $rows[] = ['name' => $item[0], 'title' => $item[1], 'parent' => 'cashflow_manage'];
        }
        foreach ($treatments as $item) {
            $rows[] = ['name' => $item[0], 'title' => $item[1], 'parent' => 'treatments_manage'];
        }
        foreach ($extras as $item) {
            $rows[] = ['name' => $item[0], 'title' => $item[1], 'parent' => $item[2]];
        }

        return $rows;
    }

    /**
     * @param  array<int, string>  $parents
     */
    private function inferParent(string $name, array $parents): ?string
    {
        $best = null;
        $bestLen = -1;
        foreach ($parents as $parent) {
            $prefix = preg_replace('/_manage$/', '', $parent);
            if ($prefix === $parent || $prefix === '') {
                continue;
            }
            $needle = $prefix.'_';
            if (strpos($name, $needle) === 0 && strlen($needle) > $bestLen) {
                $best = $parent;
                $bestLen = strlen($needle);
            }
        }

        return $best;
    }

    private function specialParent(string $name): ?string
    {
        $special = [
            'edit_doctor_after_arrived_treatment' => 'appointments_manage',
            'edit_service_after_arrived_treatment' => 'appointments_manage',
            'edit_schedule_after_arrived_treatment' => 'appointments_manage',
            'treatments_services' => 'treatments_manage',
        ];

        return $special[$name] ?? null;
    }

    /**
     * @return array<string, string>
     */
    private function dottedMap(): array
    {
        return [
            'cashflow.manage' => 'cashflow_manage',
            'cashflow.dashboard.view' => 'cashflow_dashboard',
            'cashflow.fdm.view' => 'cashflow_fdm_view',
            'cashflow.expense.create' => 'cashflow_expense_create',
            'cashflow.expense.edit' => 'cashflow_expense_edit',
            'cashflow.expense.approve' => 'cashflow_expense_approve',
            'cashflow.expense.void' => 'cashflow_expense_void',
            'cashflow.transfer.create' => 'cashflow_transfer_create',
            'cashflow.transfer.edit' => 'cashflow_transfer_edit',
            'cashflow.transfer.void' => 'cashflow_transfer_void',
            'cashflow.vendor.manage' => 'cashflow_vendor_manage',
            'cashflow.vendor.ledger.view' => 'cashflow_vendor_ledger_view',
            'cashflow.vendor.transaction.create' => 'cashflow_vendor_transaction',
            'cashflow.staff_advance.view' => 'cashflow_staff_advance',
            'cashflow.staff_advance.create' => 'cashflow_staff_advance',
            'cashflow.staff_advance.edit' => 'cashflow_staff_advance_edit',
            'cashflow.staff_advance.void' => 'cashflow_staff_advance_void',
            'cashflow.category.manage' => 'cashflow_category_manage',
            'cashflow.pool.manage' => 'cashflow_pool_manage',
            'cashflow.period.lock' => 'cashflow_period_lock',
            'cashflow.audit.view' => 'cashflow_audit_view',
            'cashflow.settings.manage' => 'cashflow_settings',
            'cashflow.reports.view' => 'cashflow_reports',
            'cashflow.reports.export' => 'cashflow_reports_export',
            'patients.list.view' => 'patients_manage',
            'patients.edit' => 'patients_edit',
            'patients.delete' => 'patients_destroy',
            'patients.activate' => 'patients_active',
            'patients.deactivate' => 'patients_inactive',
            'patients.card.view' => 'appointments_patient_card',
            'patients.documents.view' => 'patients_document_manage',
            'patients.documents.upload' => 'patients_document_create',
            'patients.documents.edit' => 'patients_document_edit',
            'patients.documents.delete' => 'patients_document_destroy',
            'patients.plans.view' => 'patients_plan_manage',
            'patients.invoices.view' => 'patients_invoice_manage',
            'patients.refunds.view' => 'patients_refund_manage',
            'patients.membership.assign' => 'patients_assign_membership',
            'patients.referral.add' => 'patients_add_referrals',
            'patients.consultations.view' => 'patients_appointment_manage',
            'patients.consultations.create' => 'patients_appointment_manage',
            'patients.treatments.view' => 'treatments_manage',
            'patients.treatments.create' => 'treatments_manage',
            'services.list.view' => 'services_manage',
            'services.create' => 'services_create',
            'services.edit' => 'services_edit',
            'services.destroy' => 'services_destroy',
            'services.detail.view' => 'services_detail',
            'services.duplicate' => 'services_duplicate',
            'services.activate' => 'services_active',
            'services.deactivate' => 'services_inactive',
            'leads.list.view' => 'leads_manage',
            'leads.create' => 'leads_create',
            'leads.edit' => 'leads_edit',
            'leads.delete' => 'leads_destroy',
            'leads.import' => 'leads_import',
            'leads.export' => 'leads_export',
            'leads.convert' => 'leads_convert',
            'leads.update_status' => 'leads_lead_status',
            'leads.update_city' => 'leads_city',
            'leads.list.view_junk' => 'leads_junk',
            'plans.list.view' => 'plans_manage',
            'plans.create' => 'plans_create',
            'plans.edit' => 'plans_edit',
            'plans.activate' => 'plans_active',
            'plans.deactivate' => 'plans_inactive',
            'plans.destroy' => 'plans_destroy',
            'plans.service.delete' => 'plans_service_delete',
            'plans.cash.edit' => 'plans_cash_edit',
            'plans.cash.delete' => 'plans_cash_delete',
            'plans.cash.edit_payment_mode' => 'plans_cash_edit_payment_mode',
            'plans.cash.edit_amount' => 'plans_cash_edit_amount',
            'plans.cash.edit_date' => 'plans_cash_edit_date',
            'plans.log.view' => 'plans_log',
            'plans.log.export' => 'plans_log_excel',
            'plans.sms_log.view' => 'plans_sms_log',
            'packages.list.view' => 'packages_manage',
            'packages.create' => 'packages_create',
            'packages.edit' => 'packages_edit',
            'packages.activate' => 'packages_active',
            'packages.deactivate' => 'packages_inactive',
            'packages.destroy' => 'packages_destroy',
            'bundles.list.view' => 'packages_manage',
            'bundles.create' => 'packages_create',
            'bundles.edit' => 'packages_edit',
            'bundles.activate' => 'packages_active',
            'bundles.deactivate' => 'packages_inactive',
            'bundles.destroy' => 'packages_destroy',
            'invoices.list.view' => 'invoices_manage',
            'invoices.cancel' => 'invoices_cancel',
            'invoices.log.view' => 'invoices_log',
            'invoices.sms_log.view' => 'invoices_sms_log',
            'discounts.list.view' => 'discounts_manage',
            'discounts.create' => 'discounts_create',
            'discounts.edit' => 'discounts_edit',
            'discounts.destroy' => 'discounts_destroy',
            'discounts.allocate' => 'discounts_allocate',
            'discounts.activate' => 'discounts_manage',
            'discounts.deactivate' => 'discounts_inactive',
            'payment_modes.list.view' => 'payment_modes_manage',
            'payment_modes.create' => 'payment_modes_create',
            'payment_modes.edit' => 'payment_modes_edit',
            'payment_modes.activate' => 'payment_modes_active',
            'payment_modes.deactivate' => 'payment_modes_inactive',
            'payment_modes.destroy' => 'payment_modes_destroy',
            'payment_modes.sort' => 'payment_modes_sort',
            'refunds.list.view' => 'refunds_manage',
            'refunds.refund' => 'refunds_refund',
            'treatments.list.view' => 'treatments_manage',
            'treatments.edit' => 'treatments_edit',
            'treatments.delete' => 'treatments_destroy',
            'treatments.export' => 'treatments_export',
            'treatments.update_status' => 'treatments_appointment_status',
            'treatments.invoice.create' => 'treatments_invoice',
            'treatments.invoice.view' => 'treatments_invoice_display',
            'treatments.patient_card.view' => 'treatments_patient_card',
            'treatments.plans.create' => 'treatments_plans_create',
            'treatments.image.view' => 'treatments_image_manage',
            'treatments.image.upload' => 'treatments_image_upload',
            'treatments.image.destroy' => 'treatments_image_destroy',
            'treatments.measurement.view' => 'treatments_measurement_manage',
            'treatments.measurement.create' => 'treatments_measurement_create',
            'treatments.measurement.edit' => 'treatments_measurement_edit',
            'treatments.medical_form.view' => 'treatments_medical_form_manage',
            'treatments.medical_form.create' => 'treatments_medical_create',
            'treatments.medical_form.edit' => 'treatments_medical_edit',
            'treatments.edit.doctor.after_arrived' => 'edit_doctor_after_arrived_treatment',
            'treatments.edit.service.after_arrived' => 'edit_service_after_arrived_treatment',
            'treatments.edit.schedule.after_arrived' => 'edit_schedule_after_arrived_treatment',
            'consultations.list.view' => 'appointments_manage',
            'consultations.edit' => 'appointments_edit',
            'consultations.delete' => 'appointments_destroy',
            'consultations.export' => 'appointments_export',
            'consultations.update_status' => 'appointments_appointment_status',
            'consultations.invoice.create' => 'appointments_invoice',
            'consultations.invoice.view' => 'appointments_invoice_display',
            'consultations.patient_card.view' => 'appointments_patient_card',
            'consultations.plans.create' => 'appointments_plans_create',
            'consultations.image.view' => 'appointments_image_manage',
            'consultations.image.upload' => 'appointments_image_upload',
            'consultations.image.destroy' => 'appointments_image_destroy',
            'consultations.measurement.view' => 'appointments_measurement_manage',
            'consultations.measurement.create' => 'appointments_measurement_create',
            'consultations.measurement.edit' => 'appointments_measurement_edit',
            'consultations.medical_form.view' => 'appointments_medical_form_manage',
            'consultations.medical_form.create' => 'appointments_medical_create',
            'consultations.medical_form.edit' => 'appointments_medical_edit',
            'consultations.edit.doctor.after_arrived' => 'edit_doctor_after_arrived_treatment',
            'consultations.edit.service.after_arrived' => 'edit_service_after_arrived_treatment',
            'consultations.edit.schedule.after_arrived' => 'edit_schedule_after_arrived_treatment',
            'business_closures.list.view' => 'business_closures_manage',
            'business_closures.create' => 'business_closures_create',
            'business_closures.edit' => 'business_closures_edit',
            'business_closures.delete' => 'business_closures_delete',
            'scheduling_shifts.list.view' => 'resourcerotas_manage',
            'scheduling_shifts.create' => 'resourcerotas_create',
            'scheduling_shifts.edit' => 'resourcerotas_edit',
            'scheduling_shifts.delete' => 'resourcerotas_destroy',
            'memberships.list.view' => 'memberships_manage',
            'memberships.create' => 'memberships_create',
            'memberships.edit' => 'memberships_edit',
            'vouchers.list.view' => 'vouchers_manage',
            'vouchers.create' => 'vouchers_create',
            'vouchers.edit' => 'vouchers_edit',
            'voucher_types.list.view' => 'voucher_types_manage',
            'voucher_types.create' => 'voucher_types_create',
            'voucher_types.edit' => 'voucher_types_edit',
            'voucher_types.allocate' => 'voucher_types_allocate',
            'voucher_types.assign' => 'voucher_types_assign',
            'voucher_types.destroy' => 'voucher_types_destroy',
            'inventory.manage' => 'inventory_manage',
            'inventory.brand.view' => 'brand_manage',
            'inventory.brand.create' => 'brand_create',
            'inventory.brand.edit' => 'brand_edit',
            'inventory.brand.delete' => 'brand_destroy',
            'inventory.product.view' => 'product_manage',
            'inventory.product.create' => 'product_create',
            'inventory.product.edit' => 'product_edit',
            'inventory.product.delete' => 'product_destroy',
            'inventory.order.view' => 'order_manage',
            'inventory.order.create' => 'order_create',
            'inventory.order.edit' => 'order_edit',
            'inventory.order.delete' => 'order_destroy',
            'leads_reports.view' => 'leads_reports_manage',
            'centers_reports.view' => 'centers_reports_manage',
            'hr_reports.view' => 'Hr_reports_manage',
            'marketing_reports.view' => 'marketing_reports_manage',
            'finance_ledger_reports.view' => 'finance_ledger_reports_manage',
            'finance_revenue_breakup_reports.view' => 'finance_revenue_breakup_reports_manage',
            'staff_listing_reports.view' => 'staff_listing_reports_manage',
            'staff_revenue_reports.view' => 'staff_revenue_reports_manage',
            'logs.view' => 'logs_manage',
            'pabao_records.view' => 'pabao_records_manage',
            'contact.view' => 'contact',
            'consultant_revenue_report.view' => 'consultant_revenue_report',
            'upselling_report.view' => 'upselling_report',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function collapsePrefixes(): array
    {
        return [
            'cashflow.' => 'cashflow_manage',
            'patients.' => 'patients_manage',
            'services.' => 'services_manage',
            'leads.' => 'leads_manage',
            'plans.' => 'plans_manage',
            'packages.' => 'packages_manage',
            'bundles.' => 'packages_manage',
            'invoices.' => 'invoices_manage',
            'discounts.' => 'discounts_manage',
            'payment_modes.' => 'payment_modes_manage',
            'refunds.' => 'refunds_manage',
            'treatments.' => 'treatments_manage',
            'consultations.' => 'appointments_manage',
            'business_closures.' => 'business_closures_manage',
            'business_working_days.' => 'settings_manage',
            'scheduling_shifts.' => 'resourcerotas_manage',
            'memberships.' => 'memberships_manage',
            'membership_types.' => 'memberships_manage',
            'vouchers.' => 'vouchers_manage',
            'voucher_types.' => 'voucher_types_manage',
            'inventory.' => 'inventory_manage',
            'dashboard.' => 'dashboard_manage',
            'management_dashboard.' => 'dashboard_manage',
            'leads_reports.' => 'leads_reports_manage',
            'centers_reports.' => 'centers_reports_manage',
            'hr_reports.' => 'Hr_reports_manage',
            'marketing_reports.' => 'marketing_reports_manage',
            'finance_ledger_reports.' => 'finance_ledger_reports_manage',
            'finance_revenue_breakup_reports.' => 'finance_revenue_breakup_reports_manage',
            'staff_listing_reports.' => 'staff_listing_reports_manage',
            'staff_revenue_reports.' => 'staff_revenue_reports_manage',
            'logs.' => 'logs_manage',
            'pabao_records.' => 'pabao_records_manage',
            'whatsapp.' => 'leads_manage',
        ];
    }

    /**
     * @param  array<string, string>  $collapse
     * @param  array<string, int>  $nameToId
     */
    private function collapseTarget(string $dotted, array $collapse, array $nameToId): ?string
    {
        $best = null;
        $bestLen = -1;
        foreach ($collapse as $prefix => $target) {
            if (strpos($dotted, $prefix) === 0 && strlen($prefix) > $bestLen && isset($nameToId[$target])) {
                $best = $target;
                $bestLen = strlen($prefix);
            }
        }

        return $best;
    }

    /**
     * @return array<string, string>
     */
    private function leftoverMap(): array
    {
        return [
            'patients' => 'patients_manage',
            'leads' => 'leads_manage',
            'services' => 'services_manage',
            'packages' => 'packages_manage',
            'plans' => 'plans_manage',
            'invoices' => 'invoices_manage',
            'discounts' => 'discounts_manage',
            'cashflow' => 'cashflow_manage',
            'refunds' => 'refunds_manage',
            'payment_modes' => 'payment_modes_manage',
            'memberships' => 'memberships_manage',
            'membership_types' => 'membershiptypes_manage',
            'vouchers' => 'vouchers_manage',
            'voucher_types' => 'voucher_types_manage',
            'inventory' => 'inventory_manage',
            'logs' => 'logs_manage',
            'pabao_records' => 'pabao_records_manage',
            'bundles' => 'packages_manage',
            'consultations' => 'appointments_manage',
            'scheduling_shifts' => 'resourcerotas_manage',
            'business_working_days' => 'settings_manage',
            'dashboard' => 'dashboard_manage',
            'dashboard_fdm' => 'dashboard_manage',
            'dashboard_marketing' => 'dashboard_manage',
            'dashboard_overview' => 'dashboard_manage',
            'dashboard_practitioners' => 'dashboard_manage',
            'management_dashboard' => 'dashboard_manage',
            'whatsapp' => 'leads_manage',
            'leads_reports' => 'leads_reports_manage',
            'centers_reports' => 'centers_reports_manage',
            'finance_ledger_reports' => 'finance_ledger_reports_manage',
            'finance_revenue_breakup_reports' => 'finance_revenue_breakup_reports_manage',
            'hr_reports' => 'Hr_reports_manage',
            'marketing_reports' => 'marketing_reports_manage',
            'staff_listing_reports' => 'staff_listing_reports_manage',
            'staff_revenue_reports' => 'staff_revenue_reports_manage',
        ];
    }

    private function copyGrants(int $fromId, int $toId): int
    {
        $copied = 0;
        $roleRows = DB::table('role_has_permissions')->where('permission_id', $fromId)->get();
        foreach ($roleRows as $row) {
            $exists = DB::table('role_has_permissions')
                ->where('role_id', $row->role_id)
                ->where('permission_id', $toId)
                ->exists();
            if (! $exists) {
                $copied++;
                DB::table('role_has_permissions')->insert([
                    'role_id' => $row->role_id,
                    'permission_id' => $toId,
                ]);
            }
        }

        $modelRows = DB::table('model_has_permissions')->where('permission_id', $fromId)->get();
        foreach ($modelRows as $row) {
            $exists = DB::table('model_has_permissions')
                ->where('permission_id', $toId)
                ->where('model_type', $row->model_type)
                ->where('model_id', $row->model_id)
                ->exists();
            if (! $exists) {
                $copied++;
                DB::table('model_has_permissions')->insert([
                    'permission_id' => $toId,
                    'model_type' => $row->model_type,
                    'model_id' => $row->model_id,
                ]);
            }
        }

        return $copied;
    }

    private function deleteDottedAndLeftovers(): int
    {
        $keep = [];
        foreach ($this->catalog() as $row) {
            $keep[$row['name']] = true;
        }

        $leftovers = array_keys($this->leftoverMap());
        $dottedIds = Permission::query()->where('name', 'like', '%.%')->pluck('id')->all();
        $leftoverIds = Permission::query()->whereIn('name', $leftovers)->pluck('id')->all();
        $childIds = $leftoverIds === []
            ? []
            : Permission::query()->whereIn('parent_id', $leftoverIds)->pluck('id')->all();

        $ids = array_values(array_unique(array_merge($dottedIds, $leftoverIds, $childIds)));
        if ($ids === []) {
            return 0;
        }

        $protected = Permission::query()
            ->whereIn('id', $ids)
            ->whereIn('name', array_keys($keep))
            ->pluck('id')
            ->all();
        $ids = array_values(array_diff($ids, $protected));
        if ($ids === []) {
            return 0;
        }

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        Permission::query()->whereIn('id', $ids)->delete();

        return count($ids);
    }

    private function verifyCodePermissions(): int
    {
        $this->newLine();
        $this->info('Verifying permission names used in code…');
        $names = $this->extractCodePermissions();
        $existing = Permission::query()->whereIn('name', $names)->pluck('name')->all();
        $missing = array_values(array_diff($names, $existing));
        sort($missing);

        $this->info('code-checked unique names: '.count($names));
        $this->info('present in DB: '.(count($names) - count($missing)));
        if ($missing === []) {
            $this->info('All code-checked permissions exist.');

            return 0;
        }

        $this->warn('Missing ('.count($missing).'):');
        foreach ($missing as $name) {
            $this->line('  '.$name);
        }

        return 0;
    }

    /**
     * @return array<int, string>
     */
    private function extractCodePermissions(): array
    {
        $roots = [
            base_path('app'),
            base_path('resources/views'),
            base_path('routes'),
        ];
        $found = [];
        $patterns = [
            '/@can\(\s*[\'"]([a-zA-Z][a-zA-Z0-9_.]*)[\'"]/',
            '/Gate::allows\(\s*[\'"]([a-zA-Z][a-zA-Z0-9_.]*)[\'"]/',
            '/middleware\(\s*[\'"]permission:([a-zA-Z][a-zA-Z0-9_]*)[\'"]/',
            '/->can\(\s*[\'"]([a-zA-Z][a-zA-Z0-9_.]*)[\'"]/',
        ];

        foreach ($roots as $root) {
            if (! File::isDirectory($root)) {
                continue;
            }
            foreach (File::allFiles($root) as $file) {
                $ext = $file->getExtension();
                if (! in_array($ext, ['php', 'blade.php'], true) && substr($file->getFilename(), -10) !== '.blade.php') {
                    if ($ext !== 'php') {
                        continue;
                    }
                }
                $text = File::get($file->getPathname());
                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $text, $matches)) {
                        foreach ($matches[1] as $name) {
                            if (strpos($name, '.') !== false) {
                                continue;
                            }
                            $found[$name] = true;
                        }
                    }
                }
            }
        }

        $names = array_keys($found);
        sort($names);

        return $names;
    }
}
