<?php

namespace App\Http\Middleware\TourGuideLanguageMiddlewares;

use Illuminate\Http\Request;
use Closure;

/**
 * TourGuideLanguageWebAccessPermissionMiddleware Class responsible for validating user access permission
 * @package App\Http\Middleware\TourGuideLanguageMiddlewares
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class TourGuideLanguageWebAccessPermissionMiddleware
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
            case "displayTourGuideLanguageEditForm":
                if (in_array("display-edit-tour-guide-language-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "deleteTourGuideLanguageAction":
                if (in_array("delete-tour-guide-language", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "displayTourGuideLanguageCreationForm":
                if (in_array("display-create-tour-guide-language-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "updateTourGuideLanguageAction":
                if (in_array("update-tour-guide-language", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "createNewTourLanguageGuideAction":
                if (in_array("create-tour-guide-language", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "listTourGuidesLanguages":
                if (in_array("list-tour-guide-language", $userPermissions)) {
                    return $next($request);
                }
                break;
        }
        return redirect()->route('home')->with("access-denied", "You do not have permission to access the page");
    }
}