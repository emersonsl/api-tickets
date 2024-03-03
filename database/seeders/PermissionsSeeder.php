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
        $adminRole = Role::create(['name' => 'admin']);
        
        $permissions = [
            'promote users',
        ];
        $this->createAddPermissions($adminRole, $permissions);
        return $adminRole;
    }

    private function createPromoterPermissions(): Role{
        $promoteRole = Role::create(['name' => 'promoter']);
        
        $permissions = [
            'create event',
            'create sector',
            'create batch',
            'create coupon'
        ];
        $this->createAddPermissions($promoteRole, $permissions);
        return $promoteRole;
    }

    private function createCustomerPermissions(): Role{
        $customerRole = Role::create(['name' => 'customer']);
        
        $permissions = [
            'reserve ticket',
            'create payment'
        ];
        $this->createAddPermissions($customerRole, $permissions);
        return $customerRole;
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
        $customerRole = $this->createCustomerPermissions();
        $promoterRole = $this->createPromoterPermissions();
        $adminRole = $this->createAdminPermissions();

        // create demo users
        $user = \App\Models\User::factory()->create([
            'name' => 'Example Customer User',
            'email' => 'customer@example.com',
            'password' => 'customer123'
        ]);
        $user->assignRole($customerRole);

        $user = \App\Models\User::factory()->create([
            'name' => 'Example Promoter User',
            'email' => 'promoter@example.com',
            'password' => 'promoter123'
        ]);
        $user->assignRole([$promoterRole, $customerRole]);
        
        $user = \App\Models\User::factory()->create([
            'name' => 'Example Admin User',
            'email' => 'admin@example.com',
            'password' => 'admin123'
        ]);
        $user->assignRole([$adminRole, $promoterRole, $customerRole]);
    }


}