<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

$service = new \App\Services\CampaignMessageLifecycleService();
$processed = $service->sendQueuedMessages(3, 1);

echo 'processed=' . $processed . PHP_EOL;
