<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $guardName = 'web';

        if (Permission::where('name', 'whatsapp_manage')->exists()) {
            return;
        }

        $maxSort = Permission::where('main_group', 1)->max('sort_order') ?? 0;

        DB::transaction(function () use ($guardName, $maxSort) {
            $parent = Permission::create([
                'name' => 'whatsapp_manage',
                'title' => 'WhatsApp Inbox',
                'main_group' => 1,
                'parent_id' => 0,
                'status' => 1,
                'guard_name' => $guardName,
                'sort_order' => $maxSort + 1,
            ]);

            $children = [
                ['name' => 'whatsapp_inbox', 'title' => 'Open WhatsApp Inbox'],
                ['name' => 'whatsapp_send', 'title' => 'Send WhatsApp Messages'],
                ['name' => 'whatsapp_settings', 'title' => 'Manage WhatsApp Settings'],
            ];

            foreach ($children as $index => $child) {
                Permission::create([
                    'name' => $child['name'],
                    'title' => $child['title'],
                    'main_group' => 0,
                    'parent_id' => $parent->id,
                    'status' => 1,
                    'guard_name' => $guardName,
                    'sort_order' => $index + 1,
                ]);
            }

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $role = \Spatie\Permission\Models\Role::where('name', 'FDM')->first();
            if ($role) {
                $role->givePermissionTo([
                    'whatsapp_manage',
                    'whatsapp_inbox',
                    'whatsapp_send',
                ]);
            }
        });
    }

    public function down(): void
    {
        $parent = Permission::where('name', 'whatsapp_manage')->first();
        if ($parent) {
            Permission::where('parent_id', $parent->id)->delete();
            $parent->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
