<?php

namespace Ajency\User\Ajency\permissions;

use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AccessPermission {
    /**
     * checks if the accessing user has the required permissions to access the
     * endpoint or class
     * @param  $key  endpoint slug or class name
     * @param  $userId user who's permissions need to be checked
     * @return       true/false
     */
    public function checkAccessPermissions($key, $userId) {

        // get the endpoint/classname to permission mapping from config
        $permissionMapping = config('aj_user_middleware_auth');

        // get the access permissions required
        $accessPermissions = $permissionMapping[$key];

        // fetch the user's permissions
        $user = User::find($userId);
        $userPermissions = $this->getAllUserPermissions($user->getAllPermissions());

        // check if there is atleast one overlapping permission
        if(count(array_intersect($userPermissions,$accessPermissions)) == 0)
            return false;
        else
            return true;

    }

    /**
     * returns only permission names
     * @param  array  all user's permissions
     * @return array of permissions
     */
    public function getAllUserPermissions($allPermissions) {
        $permissions = [];
        foreach($allPermissions as $perm) {
            array_push($permissions,$perm->name);
        }
        return $permissions;
    }
}
