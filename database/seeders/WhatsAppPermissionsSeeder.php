<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhatsAppPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $existing = Permission::where('name', 'whatsapp_manage')->first();
        if ($existing) {
            $this->command?->info('WhatsApp permissions already exist. Skipping.');
            return;
        }

        $guardName = 'web';
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

        $this->command?->info('WhatsApp permissions seeded (1 parent + 3 children).');
    }
};
