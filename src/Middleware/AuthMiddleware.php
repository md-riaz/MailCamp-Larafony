<?php

declare(strict_types=1);

namespace App\Middleware;

use Larafony\Framework\Auth\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!Auth::check()) {
            // Redirect to login
            return new \Larafony\Framework\Http\Response(
                status: 302,
                headers: ['Location' => '/login']
            );
        }

        return $handler->handle($request);
    }
}
