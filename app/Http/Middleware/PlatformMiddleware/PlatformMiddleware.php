<?php

namespace App\Http\Middleware\PlatformMiddleware;

use App\Utility\PreDefinedPaths;
use Closure;

/**
 * PlatformMiddleware Class responsible for platforms validations
 * @package App\Http\Middleware\PlatformMiddleware
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class PlatformMiddleware
{
    /**
     * {@inheritdoc}
     */
    public function handle($request, Closure $next)
    {
        $requestPlatformCode = $request->header("Platform");
        if ($requestPlatformCode) {
            if (file_exists(PreDefinedPaths::getPlatformJSONFilePath())) {
                $platformsJSONFilePath = PreDefinedPaths::getPlatformJSONFilePath();
                $platforms = file_get_contents($platformsJSONFilePath);
                if ($platforms && !empty($platforms)) {
                    if (in_array($requestPlatformCode, json_decode($platforms, TRUE))) {
                        return $next($request);
                    }
                }
            }
        }
        $errorResponse = [
            "errors" => [
                "Platform" => "Request from undefined platform"
            ]
        ];
        return response()->json($errorResponse, 400);
    }
}