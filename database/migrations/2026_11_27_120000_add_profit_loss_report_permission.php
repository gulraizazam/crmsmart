<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NAME = 'finance_general_revenue_reports_profit_loss_report';

    public function up(): void
    {
        $parent = Permission::where('name', 'finance_general_revenue_reports_manage')->first();
        if (!$parent) {
            return;
        }

        $permission = Permission::where('name', self::NAME)->first() ?? Permission::create([
            'name' => self::NAME,
            'title' => 'Profit & Loss Report',
            'main_group' => 0,
            'parent_id' => $parent->id,
            'status' => 1,
            'guard_name' => 'web',
            'sort_order' => (Permission::where('parent_id', $parent->id)->max('sort_order') ?? 0) + 1,
        ]);

        $adminRoleId = DB::table('roles')->where('name', 'Administrator')->value('id');
        if ($adminRoleId) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permission->id,
                'role_id' => $adminRoleId,
            ]);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', self::NAME)->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
