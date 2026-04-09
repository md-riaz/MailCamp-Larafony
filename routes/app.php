<?php

use App\Controllers\CampaignController;
use App\Middleware\RoleMiddleware;

return [
    '/campaigns' => [
        'GET' => [CampaignController::class, 'index'],
        'POST' => [CampaignController::class, 'store'],
        'middleware' => [
            [RoleMiddleware::class, 'roles' => ['Admin', 'Agent']]
        ],
    ],
    '/campaigns/{id}' => [
        'DELETE' => [CampaignController::class, 'destroy'],
        'middleware' => [
            [RoleMiddleware::class, 'roles' => ['Admin']],
        ],
    ],
];