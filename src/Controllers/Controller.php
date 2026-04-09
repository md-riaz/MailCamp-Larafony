<?php

declare(strict_types=1);

namespace App\Controllers;

use Larafony\Framework\Container\Contracts\ContainerContract;
use Psr\Http\Message\ResponseInterface;

class Controller extends \Larafony\Framework\Web\Controller
{
    public function __construct(ContainerContract $container)
    {
        parent::__construct($container);
    }

    public function redirect(string $url, int $status = 302): ResponseInterface
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '';
            $basePath = rtrim((string) (parse_url($appUrl, PHP_URL_PATH) ?? ''), '/');
            if ($basePath !== '') {
                $url = $basePath . $url;
            }
        }

        return parent::redirect($url, $status);
    }
}
