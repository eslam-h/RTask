<?php

namespace App\Http\Middleware\UserMiddlewares;

use Closure;
use Dev\Domain\Service\RoleService\RoleService;

/**
 * UserMiddleware Class responsible for route permission validation for user route
 * @package App\Http\Middleware\UserMiddlewares
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class UserPermissionsMiddleware
{
    /**
     * @var string $topic this refers to topic filed in permissions table
     */
    private $topic = "user";

    /**
     * {@inheritdoc}
     */
    public function handle($request, Closure $next)
    {
        $authenticatedUser = $request->session()->get("authUser");
        $roleService = resolve(RoleService::class);
        $userRoleWithPermissions = $roleService->getRoleWithCriteria([
            "name" => $authenticatedUser->role->role,
            "topic" => $this->topic
        ]);
    }
}