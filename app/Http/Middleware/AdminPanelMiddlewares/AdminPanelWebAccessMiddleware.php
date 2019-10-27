<?php

namespace App\Http\Middleware\AdminPanelMiddlewares;

use Illuminate\Http\Request;

/**
 * AdminPanelWebAccessMiddleware Class responsible for admin panel access permission validation
 * @package App\Http\Middleware\AdminPanelMiddlewares
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class AdminPanelWebAccessMiddleware
{
    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, \Closure $next)
    {
        $webAuthUser = $request->session()->get("webAuthUser");
        if (!$webAuthUser) {
            return redirect()->route('home');
        }
        $userPermissions = $webAuthUser->getUserPermissionsActions();
        if (in_array("admin-panel-access", $userPermissions)) {
            return $next($request);
        }
        $request->session()->forget("webAuthUser");
        auth()->logout();
        return redirect("/login")->with("unauthorized-user", "Access from unauthorized user");
    }
}