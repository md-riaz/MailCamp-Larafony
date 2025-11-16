<?php

declare(strict_types=1);

use Larafony\Framework\Config\ServiceProviders\ConfigServiceProvider;
use Larafony\Framework\Console\ServiceProviders\ConsoleServiceProvider;
use Larafony\Framework\Database\ServiceProviders\DatabaseServiceProvider;
use Larafony\Framework\ErrorHandler\ServiceProviders\ErrorHandlerServiceProvider;
use Larafony\Framework\Http\ServiceProviders\HttpServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app = \Larafony\Framework\Console\Application::instance(base_path: dirname(__DIR__));

$app->withServiceProviders([
    // Load configuration first so other providers can depend on it
    ConfigServiceProvider::class,
    // HTTP provides PSR-17 factories used by console output streams
    HttpServiceProvider::class,
    // Console after streams and config are available
    ConsoleServiceProvider::class,
    // Database after config is loaded
    DatabaseServiceProvider::class,
    // Error handler last
    ErrorHandlerServiceProvider::class,
]);

return $app;
