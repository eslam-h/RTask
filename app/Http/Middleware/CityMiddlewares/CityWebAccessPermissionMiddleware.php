<?php

namespace App\Http\Middleware\CityMiddlewares;

use Illuminate\Http\Request;
use Closure;

/**
 * CityWebAccessPermissionMiddleware Class responsible for validating user access permission
 * @package App\Http\Middleware\CityMiddlewares
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class CityWebAccessPermissionMiddleware
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
            case "displayCityEditForm":
                if (in_array("display-edit-city-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "deleteCityAction":
                if (in_array("delete-city", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "displayCityCreationForm":
                if (in_array("display-create-city-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "updateCityAction":
                if (in_array("update-city", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "createNewCityAction":
                if (in_array("create-city", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "listCities":
                if (in_array("list-city", $userPermissions)) {
                    return $next($request);
                }
                break;
        }
        return redirect()->route('home')->with("access-denied", "You do not have permission to access the page");
    }
}