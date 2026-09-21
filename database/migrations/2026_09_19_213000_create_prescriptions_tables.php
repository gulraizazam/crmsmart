<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->date('prescribed_at')->nullable();
            $table->string('diagnosis', 1000)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('issued');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'appointment_id']);
            $table->index(['account_id', 'patient_id']);
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prescription_id');
            $table->unsignedInteger('sort_no')->default(0);
            $table->string('medicine_name');
            $table->string('dose', 100)->nullable();
            $table->string('frequency', 100)->nullable();
            $table->string('duration', 100)->nullable();
            $table->string('instructions', 500)->nullable();
            $table->timestamps();

            $table->foreign('prescription_id')
                ->references('id')
                ->on('prescriptions')
                ->onDelete('cascade');
        });

        $this->seedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');

        $names = [
            'appointments_prescription_manage',
            'appointments_prescription_create',
            'appointments_prescription_edit',
            'appointments_prescription_destroy',
        ];
        Permission::whereIn('name', $names)->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function seedPermissions(): void
    {
        if (Permission::where('name', 'appointments_prescription_manage')->exists()) {
            return;
        }

        $parent = Permission::where('name', 'appointments_manage')->first();
        $parentId = $parent->id ?? 65;
        $maxSort = (int) Permission::where('parent_id', $parentId)->max('sort_order');

        $permissions = [
            ['name' => 'appointments_prescription_manage', 'title' => 'E-Prescription'],
            ['name' => 'appointments_prescription_create', 'title' => 'E-Prescription Create'],
            ['name' => 'appointments_prescription_edit', 'title' => 'E-Prescription Edit'],
            ['name' => 'appointments_prescription_destroy', 'title' => 'E-Prescription Delete'],
        ];

        DB::transaction(function () use ($permissions, $parentId, $maxSort) {
            foreach ($permissions as $index => $permission) {
                Permission::create([
                    'name' => $permission['name'],
                    'title' => $permission['title'],
                    'main_group' => 0,
                    'parent_id' => $parentId,
                    'status' => 1,
                    'guard_name' => 'web',
                    'sort_order' => $maxSort + $index + 1,
                ]);
            }

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $names = array_column($permissions, 'name');
            foreach (Role::all() as $role) {
                $shouldGrant = $role->name === 'Super-Admin';
                if (!$shouldGrant) {
                    try {
                        $shouldGrant = $role->hasPermissionTo('appointments_consultancy');
                    } catch (\Throwable $e) {
                        $shouldGrant = false;
                    }
                }
                if ($shouldGrant) {
                    $role->givePermissionTo($names);
                }
            }
        });
    }
};
