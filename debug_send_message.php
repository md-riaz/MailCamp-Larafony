<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

$message = \App\Models\Message::query()->where('id', '=', 1)->first();
if (!$message) {
    echo "message not found\n";
    exit(1);
}

try {
    (new \App\Services\CampaignMessageLifecycleService())->sendQueuedMessage($message);
    echo "sent\n";
} catch (\Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
