<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    private function createAddPermissions(Role $role, array $permissions){
        foreach($permissions as $permission){
            try{
                Permission::create(['name' => $permission]);
            }finally{
                $role->givePermissionTo($permission);
            }
        }
    }

    private function createAdminPermissions(): Role{
        $role = Role::create(['name' => 'admin']);
        
        $permissions = [
            'promote to administrator',
        ];
        $this->createAddPermissions($role, $permissions);
        return $role;
    }

    private function createPromoterPermissions(): Role{
        $role = Role::create(['name' => 'promoter']);
        
        $permissions = [
            'create event',
        ];
        $this->createAddPermissions($role, $permissions);
        return $role;
    }

    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $adminRole = $this->createAdminPermissions();
        $PromoterRole = $this->createPromoterPermissions();

        // create demo users
        $user = \App\Models\User::factory()->create([
            'name' => 'Example Admin User',
            'email' => 'admin@example.com',
        ]);
        $user->assignRole($adminRole);

        $user = \App\Models\User::factory()->create([
            'name' => 'Example Promoter User',
            'email' => 'promoter@example.com',
        ]);
        $user->assignRole($PromoterRole);
    }


}