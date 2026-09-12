<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupConsultTreatPermissions extends Command
{
    protected $signature = 'permissions:cleanup-consult-treat {--dry-run : Show the plan without writing}';

    protected $description = 'Keep only real consultancy and treatment actions, then drop duplicate role-form leftovers';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $this->info($dry ? 'DRY RUN — no writes' : 'Cleaning consultancy and treatment permissions');

        DB::beginTransaction();
        try {
            $this->restoreKeepers();
            $copied = $this->remapExtras();
            $deleted = $this->deleteExtras();

            if ($dry) {
                DB::rollBack();
                $this->warn('Rolled back (dry-run).');
            } else {
                DB::commit();
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());

            return 1;
        }

        $this->info('grants copied from extras: '.$copied);
        $this->info('permissions deleted: '.$deleted);

        return 0;
    }

    private function restoreKeepers(): void
    {
        foreach ($this->keepers() as $parentName => $group) {
            $parent = $this->upsert($parentName, $group['title'], null, $group['sort']);
            foreach ($group['children'] as $index => $child) {
                $this->upsert($child['name'], $child['title'], $parent->name, $index + 1);
            }
        }
    }

    private function upsert(string $name, string $title, ?string $parentName, int $sort): Permission
    {
        $parentId = 0;
        if ($parentName !== null) {
            $parent = Permission::query()->where('name', $parentName)->first();
            $parentId = $parent ? (int) $parent->id : 0;
        }

        $row = Permission::query()->where('name', $name)->first();
        $payload = [
            'title' => $title,
            'main_group' => $parentName === null ? 1 : 0,
            'parent_id' => $parentId,
            'status' => 1,
            'guard_name' => 'web',
            'sort_order' => $sort,
        ];
        if ($row) {
            $row->update($payload);

            return $row->fresh();
        }

        return Permission::create(array_merge($payload, ['name' => $name]));
    }

    private function remapExtras(): int
    {
        $copied = 0;
        $nameToId = Permission::query()->pluck('id', 'name')->all();
        foreach ($this->extraMap() as $from => $targets) {
            if (! isset($nameToId[$from])) {
                continue;
            }
            foreach ($targets as $target) {
                if (! isset($nameToId[$target])) {
                    continue;
                }
                $copied += $this->copyGrants((int) $nameToId[$from], (int) $nameToId[$target]);
            }
        }

        return $copied;
    }

    private function deleteExtras(): int
    {
        $keep = [];
        foreach ($this->keepers() as $parentName => $group) {
            $keep[$parentName] = true;
            foreach ($group['children'] as $child) {
                $keep[$child['name']] = true;
            }
        }

        $extraNames = array_merge(array_keys($this->extraMap()), [
            'treatments',
            'consultations',
        ]);
        $ids = Permission::query()->whereIn('name', $extraNames)->pluck('id')->all();
        $ids = array_values(array_filter($ids, function ($id) use ($keep) {
            $name = Permission::query()->where('id', $id)->value('name');

            return $name !== null && ! isset($keep[$name]);
        }));

        $parentIds = Permission::query()
            ->whereIn('name', ['appointments_manage', 'treatments_manage'])
            ->pluck('id')
            ->all();
        $orphanIds = $parentIds === []
            ? []
            : Permission::query()
                ->whereIn('parent_id', $parentIds)
                ->whereNotIn('name', array_keys($keep))
                ->pluck('id')
                ->all();
        $ids = array_values(array_unique(array_merge($ids, $orphanIds)));
        if ($ids === []) {
            return 0;
        }

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        Permission::query()->whereIn('id', $ids)->delete();

        return count($ids);
    }

    private function copyGrants(int $fromId, int $toId): int
    {
        if ($fromId === $toId) {
            return 0;
        }
        $copied = 0;
        $roleRows = DB::table('role_has_permissions')->where('permission_id', $fromId)->get();
        foreach ($roleRows as $row) {
            $exists = DB::table('role_has_permissions')
                ->where('role_id', $row->role_id)
                ->where('permission_id', $toId)
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('role_has_permissions')->insert([
                'role_id' => $row->role_id,
                'permission_id' => $toId,
            ]);
            $copied++;
        }

        return $copied;
    }

    /**
     * @return array<string, array{title: string, sort: int, children: array<int, array{name: string, title: string}>}>
     */
    private function keepers(): array
    {
        return [
            'appointments_manage' => [
                'title' => 'Consultancies',
                'sort' => 1,
                'children' => [
                    ['name' => 'appointments_consultancy', 'title' => 'View consultancies'],
                    ['name' => 'appointments_create', 'title' => 'Create'],
                    ['name' => 'appointments_edit', 'title' => 'Edit'],
                    ['name' => 'appointments_destroy', 'title' => 'Delete'],
                    ['name' => 'appointments_view', 'title' => 'View details'],
                    ['name' => 'appointments_active', 'title' => 'Activate'],
                    ['name' => 'appointments_inactive', 'title' => 'Inactivate'],
                    ['name' => 'appointments_appointment_status', 'title' => 'Update status'],
                    ['name' => 'appointments_invoice', 'title' => 'Create invoice'],
                    ['name' => 'appointments_invoice_display', 'title' => 'View invoice'],
                    ['name' => 'appointments_patient_card', 'title' => 'Patient card'],
                    ['name' => 'appointments_plans_create', 'title' => 'Create plan'],
                    ['name' => 'appointments_image_manage', 'title' => 'Images'],
                    ['name' => 'appointments_image_upload', 'title' => 'Upload images'],
                    ['name' => 'appointments_image_destroy', 'title' => 'Delete images'],
                    ['name' => 'appointments_measurement_manage', 'title' => 'Measurements'],
                    ['name' => 'appointments_measurement_create', 'title' => 'Create measurement'],
                    ['name' => 'appointments_measurement_edit', 'title' => 'Edit measurement'],
                    ['name' => 'appointments_medical_form_manage', 'title' => 'Medical form'],
                    ['name' => 'appointments_medical_create', 'title' => 'Create medical form'],
                    ['name' => 'appointments_medical_edit', 'title' => 'Edit medical form'],
                    ['name' => 'appointments_log', 'title' => 'Activity log'],
                    ['name' => 'appointments_log_excel', 'title' => 'Export log'],
                    ['name' => 'appointments_export', 'title' => 'Export'],
                    ['name' => 'appointments_export_today', 'title' => 'Export today'],
                    ['name' => 'appointments_export_this_month', 'title' => 'Export this month'],
                    ['name' => 'appointments_export_all', 'title' => 'Export all'],
                    ['name' => 'edit_after_arrived', 'title' => 'Edit after arrived'],
                    ['name' => 'update_consultation_service', 'title' => 'Edit service after arrived'],
                    ['name' => 'update_consultation_doctor', 'title' => 'Edit doctor after arrived'],
                    ['name' => 'update_consultation_schedule', 'title' => 'Edit schedule after arrived'],
                ],
            ],
            'treatments_manage' => [
                'title' => 'Treatments',
                'sort' => 2,
                'children' => [
                    ['name' => 'treatments_services', 'title' => 'View treatments'],
                    ['name' => 'appointments_services', 'title' => 'Manage treatment records'],
                    ['name' => 'treatments_edit', 'title' => 'Edit'],
                    ['name' => 'treatments_destroy', 'title' => 'Delete'],
                    ['name' => 'treatments_today', 'title' => "Today's treatments"],
                    ['name' => 'treatments_appointment_status', 'title' => 'Update status'],
                    ['name' => 'treatments_invoice', 'title' => 'Create invoice'],
                    ['name' => 'treatments_invoice_display', 'title' => 'View invoice'],
                    ['name' => 'treatments_patient_card', 'title' => 'Patient card'],
                    ['name' => 'treatments_export', 'title' => 'Export'],
                    ['name' => 'treatments_edit_after_arrived', 'title' => 'Edit after arrived'],
                    ['name' => 'update_treatment_service', 'title' => 'Edit service after arrived'],
                    ['name' => 'update_treatment_doctor', 'title' => 'Edit doctor after arrived'],
                    ['name' => 'update_treatment_schedule', 'title' => 'Edit schedule after arrived'],
                    ['name' => 'can_edit_service', 'title' => 'Allow service change after arrived'],
                    ['name' => 'can_edit_doctor', 'title' => 'Allow doctor change after arrived'],
                    ['name' => 'can_edit_schedule', 'title' => 'Allow schedule change after arrived'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function extraMap(): array
    {
        return [
            'treatments_consultancy' => ['appointments_consultancy'],
            'treatments_display' => ['treatments_manage'],
            'consultancy_manage' => ['appointments_consultancy'],
            'consultancy_invoice' => ['appointments_invoice'],
            'consultancy_invoice_display' => ['appointments_invoice_display'],
            'patient_card' => ['appointments_patient_card'],
            'appointments_delete' => ['appointments_destroy'],
            'appointments_status' => ['appointments_appointment_status'],
            'edit_doctor_after_arrived_treatment' => ['can_edit_doctor', 'update_treatment_doctor'],
            'edit_service_after_arrived_treatment' => ['can_edit_service', 'update_treatment_service'],
            'edit_schedule_after_arrived_treatment' => ['can_edit_schedule', 'update_treatment_schedule'],
            'treatments_image_manage' => ['appointments_image_manage'],
            'treatments_image_upload' => ['appointments_image_upload'],
            'treatments_image_destroy' => ['appointments_image_destroy'],
            'treatments_measurement_manage' => ['appointments_measurement_manage'],
            'treatments_measurement_create' => ['appointments_measurement_create'],
            'treatments_measurement_edit' => ['appointments_measurement_edit'],
            'treatments_medical_form_manage' => ['appointments_medical_form_manage'],
            'treatments_medical_create' => ['appointments_medical_create'],
            'treatments_medical_edit' => ['appointments_medical_edit'],
            'treatments_plans_create' => ['appointments_plans_create'],
            'treatments_export_today' => ['treatments_export'],
            'treatments_export_this_month' => ['treatments_export'],
            'treatments_export_all' => ['treatments_export'],
            'treatments_log' => ['appointments_log'],
            'treatments_log_excel' => ['appointments_log_excel'],
        ];
    }
}
