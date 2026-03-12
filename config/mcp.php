<?php

declare(strict_types=1);

return [
    'name' => env('MCP_SERVER_NAME', env('APP_NAME', 'MailCamp MCP Server')),
    'version' => env('MCP_VERSION', '1.0.0'),
    'instructions' => <<<'TEXT'
This is the MailCamp Larafony application.
Use attribute-based routing (#[Route]), Larafony ORM models, and the existing MailCamp project conventions.
Prefer working within the current app structure under src/, config/, resources/, routes/, and database/.
TEXT,
    'discovery' => [
        'path' => base_path(),
        'dirs' => ['src/MCP'],
    ],
];
