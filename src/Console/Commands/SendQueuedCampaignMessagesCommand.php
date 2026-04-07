<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CampaignMessageLifecycleService;
use Larafony\Framework\Console\Attributes\AsCommand;
use Larafony\Framework\Console\Command;

#[AsCommand('app:send-queued-campaign-messages')]
class SendQueuedCampaignMessagesCommand extends Command
{
    public function run(): int
    {
        $campaignId = $this->input->arguments[0] ?? null;
        $limit = $this->input->arguments[1] ?? null;

        if ($campaignId !== null && (!is_numeric($campaignId) || (int) $campaignId <= 0)) {
            $this->output->error('Campaign ID must be a positive integer.');
            return 1;
        }

        if ($limit !== null && (!is_numeric($limit) || (int) $limit < 0)) {
            $this->output->error('Limit must be zero or a positive integer.');
            return 1;
        }

        $processed = (new CampaignMessageLifecycleService())->sendQueuedMessages(
            $campaignId !== null ? (int) $campaignId : null,
            $limit !== null ? (int) $limit : 0,
        );

        $this->output->info(sprintf('processed=%d', $processed));

        return 0;
    }
}
