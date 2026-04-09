<?php

use App\Controllers\AudienceController;

$router->post('/audiences', [AudienceController::class, 'create']);
$router->get('/audiences', [AudienceController::class, 'list']);
