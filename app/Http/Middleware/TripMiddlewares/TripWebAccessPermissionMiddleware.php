<?php

namespace App\Http\Middleware\TripMiddlewares;

use Illuminate\Http\Request;
use Closure;

/**
 * TripWebAccessPermissionMiddleware Class responsible for checking for trip permissions access
 * @package App\Http\Middleware\TripMiddlewares
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class TripWebAccessPermissionMiddleware
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
            case "displayTripCreationForm":
                if (in_array("display-trip-creation-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "createNewTripAction":
                if (in_array("create-new-trip", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "listTrips":
                if (in_array("list-all-trips", $userPermissions)) {
                    return $next($request);
                }
                if (in_array("list-own-trips", $userPermissions)) {
                    $request->attributes->add(["created-by" => $webAuthUser->id]);
                    return $next($request);
                }
                break;
            case "viewTripItem":
                if (in_array("view-all-trips", $userPermissions)) {
                    return $next($request);
                }
                if (in_array("view-own-trip", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "deleteTrip":
                if (in_array("delete-trip", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "displayTripModificationForm":
                if (in_array("display-trip-modification-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "updateTripAction":
                if (in_array("update-trip", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "cropImage":
                return $next($request);
                break;
            case "deleteFormTripGalleryImage":
                return $next($request);
                break;
        }
        return redirect()->route('home')->with("access-denied", "You do not have permission to access the page");
    }
}