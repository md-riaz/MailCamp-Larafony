<?php

declare(strict_types=1);

namespace App\Middleware;

use Larafony\Framework\Http\Contracts\MiddlewareContract;
use Larafony\Framework\Http\Request;
use Larafony\Framework\Http\Response;

class RoleMiddleware implements MiddlewareContract
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, callable $next): Response
    {
        $user = $request->user();
        $allowedRoles = $request->route()->getMiddlewareOptions()['roles'] ?? [];

        if (!in_array($user->role, $allowedRoles, true)) {
            return new Response('Forbidden', 403);
        }

        return $next($request);
    }
}