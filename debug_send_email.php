<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Services\CampaignMessageLifecycleService;
use Larafony\Framework\Log\Logger;

Logger::debug('Debug Email script launched');

try {
    $jobCount = (new CampaignMessageLifecycleService())->sendQueuedMessages(3, 1);
    echo "Success: Processed $jobCount queued email(s).\n";
} catch (\Throwable $exc) {
    Logger::error('Email send failed: ' . $exc->getMessage());
    echo 'Error: ' . $exc->getMessage() . "\n";
    exit(1);
}