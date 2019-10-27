<?php

namespace App\Http\Middleware\CurrencyMiddlewares;

use Illuminate\Http\Request;
use Closure;

/**
 * CurrencyWebAccessPermissionMiddleware Class responsible for validating user access permission
 * @package App\Http\Middleware\CurrencyMiddlewares
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class CurrencyWebAccessPermissionMiddleware
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
            case "displayCurrencyEditForm":
                if (in_array("display-edit-currency-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "deleteCurrencyAction":
                if (in_array("delete-currency", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "displayCurrencyCreationForm":
                if (in_array("display-create-currency-form", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "updateCurrencyAction":
                if (in_array("update-currency", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "createNewCurrencyAction":
                if (in_array("create-currency", $userPermissions)) {
                    return $next($request);
                }
                break;
            case "listCurrencies":
                if (in_array("list-currency", $userPermissions)) {
                    return $next($request);
                }
                break;
        }
        return redirect()->route('home')->with("access-denied", "You do not have permission to access the page");
    }
}