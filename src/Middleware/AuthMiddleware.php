<?php

declare(strict_types=1);

namespace App\Middleware;

use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Http\Factories\ResponseFactory;
use Larafony\Framework\Storage\Session\SessionManager;
use Larafony\Framework\Web\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $redirectPath = '/login',
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!Auth::check()) {
            /** @var SessionManager $manager */
            $manager = Application::instance()->get(SessionManager::class);
            $manager->set('redirect_path', $request->getUri()->getPath());

            $location = $this->redirectPath;
            if (str_starts_with($location, '/') && !str_starts_with($location, '//')) {
                $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '';
                $basePath = rtrim((string) (parse_url($appUrl, PHP_URL_PATH) ?? ''), '/');
                if ($basePath !== '') {
                    $location = $basePath . $location;
                }
            }

            return new ResponseFactory()->createResponse(code: 302)->withHeader('Location', $location);
        }

        return $handler->handle($request);
    }
}
