<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\CampaignMessageLifecycleService;
use Larafony\Framework\Scheduler\Contracts\JobContract;
use Larafony\Framework\Scheduler\Job;

final class SendQueuedCampaignMessagesJob extends Job implements JobContract
{
    public function handle(): void
    {
        (new CampaignMessageLifecycleService())->sendQueuedMessages();
    }

    public function handleException(\Throwable $e): void
    {
        throw $e;
    }
}
