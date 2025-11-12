<?php

declare(strict_types=1);

use Larafony\Framework\Config\ServiceProviders\ConfigServiceProvider;
use Larafony\Framework\Console\ServiceProviders\ConsoleServiceProvider;
use Larafony\Framework\Database\ServiceProviders\DatabaseServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app = \Larafony\Framework\Console\Application::instance(base_path: dirname(__DIR__));

$app->withServiceProviders([
    ConfigServiceProvider::class,
    DatabaseServiceProvider::class,
    ConsoleServiceProvider::class,
]);

$app->withCommands(function ($loader) {
    $loader->loadAttributeCommands(__DIR__ . '/../src/Console');
});

return $app;
