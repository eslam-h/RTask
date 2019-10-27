<?php

use Dev\Domain\Service\UserService\UserService;

if (! function_exists("userHasPermission")) {
    /**
     * Check whether use has access permission for specified permission
     * @param string $permission
     * @return bool
     */
    function userHasPermission(string $permission)
    {
        $loggedInUser = auth()->user();
        if (!$loggedInUser) {
            return false;
        }
        $userService = resolve(UserService::class);
        $user = $userService->getUserWithId($loggedInUser->id);
        $userPermissions = $user->getUserPermissionsActions();
        if (in_array($permission, $userPermissions)) {
            return true;
        }
        return false;
    }
}
