<?php

namespace App\Http\Middleware\UserMiddlewares;

use Illuminate\Http\Request;
use Closure;

/**
 * UserWebAuthenticationMiddleware Class responsible for user authentication
 * @package App\Http\Middleware\UserMiddlewares
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class UserWebAuthenticationMiddleware
{
    private $platformCode = "WEB-APP";

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, Closure $next)
    {
        $webAuthUser = $request->session()->get("webAuthUser");
        if ($webAuthUser) {
            return $next($request);
        }
        return redirect("/login")->with("unauthorized-user", "Access from unauthorized user");
    }
}