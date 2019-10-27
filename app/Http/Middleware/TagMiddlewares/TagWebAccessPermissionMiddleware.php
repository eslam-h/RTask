<?php

namespace App\Http\Middleware\TagMiddlewares;

use Illuminate\Http\Request;
use Closure;

/**
 * TagWebAccessPermissionMiddleware Class responsible for validating user access permission
 * @package App\Http\Middleware\TagMiddlewares
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class TagWebAccessPermissionMiddleware
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
            case "displayEditFormAction":
                if (in_array("display-edit-tag-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "deleteTagAction":
                if (in_array("delete-tag", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "displayCreateFormAction":
                if (in_array("display-create-tag-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "updateTagAction":
                if (in_array("update-tag", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "addNewTagAction":
                if (in_array("create-tag", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "listTagAction":
                if (in_array("list-tag", $userPermissions)) {
                    return $next($request);
                }
                break;
        }
        return redirect()->route('home')->with("access-denied", "You do not have permission to access the page");
    }
}