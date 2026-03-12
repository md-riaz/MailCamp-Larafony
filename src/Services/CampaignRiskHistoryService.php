<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campaign;
use App\Models\Log;

final class CampaignRiskHistoryService
{
    /**
     * @param array<string,mixed> $evaluation
     */
    public function record(Campaign $campaign, string $type, array $evaluation): void
    {
        $log = new Log();
        $log->campaign_id = $campaign->id;
        $log->type = $type;
        $log->data = json_encode([
            'risk_level' => $evaluation['risk_level'] ?? 'unknown',
            'should_pause' => (bool) ($evaluation['should_pause'] ?? false),
            'errors' => $evaluation['errors'] ?? [],
            'warnings' => $evaluation['warnings'] ?? [],
            'metrics' => $evaluation['metrics'] ?? [],
            'deliverability' => $evaluation['deliverability'] ?? [],
            'recorded_at' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $log->save();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recent(Campaign $campaign, int $limit = 10): array
    {
        $logs = Log::query()
            ->where('campaign_id', '=', $campaign->id)
            ->whereIn('type', ['campaign_safety_snapshot', 'campaign_autopaused'])
            ->orderBy('id', \Larafony\Framework\Database\Base\Query\Enums\OrderDirection::DESC)
            ->limit(max(1, min($limit, 50)))
            ->get();

        return array_map(static function (Log $log): array {
            $data = json_decode((string) ($log->data ?? ''), true);
            return [
                'id' => $log->id,
                'type' => $log->type,
                'data' => is_array($data) ? $data : null,
            ];
        }, $logs);
    }
}
