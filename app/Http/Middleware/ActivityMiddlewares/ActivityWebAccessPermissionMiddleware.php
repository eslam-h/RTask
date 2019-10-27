<?php

namespace App\Http\Middleware\ActivityMiddlewares;

use Illuminate\Http\Request;
use Closure;

/**
 * ActivityWebAccessPermissionMiddleware Class responsible for validating user access permission
 * @package App\Http\Middleware\ActivityMiddlewares
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class ActivityWebAccessPermissionMiddleware
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
            case "displayActivityEditForm":
                if (in_array("display-edit-activity-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "deleteActivityAction":
                if (in_array("delete-activity", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "displayActivityCreationForm":
                if (in_array("display-create-activity-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "updateActivityAction":
                if (in_array("update-activity", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "createNewActivityAction":
                if (in_array("create-activity", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "listActivities":
                if (in_array("list-activity", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "reorderActivities":
                if (in_array("reorder-activity", $userPermissions)) {
                    return $next($request);
                }
                break;
        }
        return redirect()->route('home')->with("access-denied", "You do not have permission to access the page");
    }
}