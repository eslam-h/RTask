<?php

namespace App\Http\Middleware\UserMiddlewares;

use Illuminate\Http\Request;
use Closure;

/**
 * UserInvitationPermissionsMiddleware Class responsible for route permission validation for user invitation route
 * @package App\Http\Middleware\UserMiddlewares
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class UserInvitationPermissionsMiddleware
{
    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, Closure $next)
    {
        $webAuthUser = $request->session()->get("webAuthUser");
        if (!$webAuthUser) {
            return redirect()->route('home');
        }
        $route = $request->route();
        $action = $route->getActionMethod();
        $userPermissions = $webAuthUser->getUserPermissionsActions();
        switch ($action) {
            case "displayInvitedUserEditForm":
                if (in_array("display-edit-user-invitation-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "deleteInvitedUserAction":
                if (in_array("delete-user-invitation", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "displayUserInvitationCreationForm":
                if (in_array("display-create-user-invitation-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "updateInvitedUserAction":
                if (in_array("update-user-invitation", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "createNewUserInvitationAction":
                if (in_array("create-user-invitation", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "listUsers":
                if (in_array("list-user-invitation", $userPermissions)) {
                    return $next($request);
                }
                break;
        }
        return redirect()->route('home')->with("access-denied", "You do not have permission to access the page");
    }
}