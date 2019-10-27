<?php

namespace App\Http\Middleware\UserMiddlewares;

use Closure;
use Dev\Domain\Service\UserService\UserService;
use Illuminate\Http\Request;

/**
 * UserApiAuthenticationMiddleware Class responsible for user authentication process
 * @package App\Http\Middleware\UserMiddlewares
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class UserApiAuthenticationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth("api")->user()) {
            return $next($request);
        }
        $errorResponse = [
            "errors" => [
                "user" => "Request from unauthorized user"
            ]
        ];
        return response()->json($errorResponse, 401);
    }
}