<?php

namespace Ajency\User\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GenerateRolesPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aj_user:role-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generating Roles & Permissions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function assignPermissionToRole($role_obj, $permission = '') {
        $permission_obj = Permission::where("name", $permission)->first(); // Check if Permission exist

        if(!$permission_obj) { // Create Permission if it doesn't exist
            Permission::create(["name" => $permission]);
        }

        return $role_obj->givePermissionTo($permission);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $roles = config('aj_role_permission_config.roles');
        $permissions = config('aj_role_permission_config.permissions');

        $role_permissions = config('aj_role_permission_config.roles_permissions');

        foreach ($role_permissions as $keyRP => $valueRP) {
            $role = Role::where("name", $roles[$valueRP["role"]])->first(); // Find the Role else is null

            if(!$role) { // If null
                $role = Role::create(["name" => $roles[$valueRP["role"]]]); // Create Role if it doesn't exist
                $this->info("Created Role: " . $roles[$valueRP["role"]]);
            }

            foreach ($valueRP["permissions"] as $keyP => $valueP) { // Map Permissions to the Role
                $this->assignPermissionToRole($role, $permissions[$valueP]);
            }
        }

        $this->info("Roles & it's Permissions created successfully");
        return;
    }
}
