<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailEvent;
use App\Models\Message;
use Larafony\Framework\Database\Base\Query\Enums\OrderDirection;

final class ObservabilityService
{
    /**
     * @return array<string, int|float>
     */
    public function dashboardMetrics(int $organizationId): array
    {
        $sent = Message::query()
            ->where('organization_id', '=', $organizationId)
            ->whereIn('status', ['sent', 'delivered', 'opened', 'clicked', 'unsubscribed', 'complained', 'bounced'])
            ->count();

        $delivered = $this->eventCount($organizationId, 'delivered');
        $bounced = $this->eventCount($organizationId, 'bounced');
        $opened = $this->eventCount($organizationId, 'opened');
        $clicked = $this->eventCount($organizationId, 'clicked');
        $unsubscribed = $this->eventCount($organizationId, 'unsubscribed');

        if ($delivered === 0) {
            $delivered = Message::query()
                ->where('organization_id', '=', $organizationId)
                ->whereIn('status', ['delivered', 'opened', 'clicked', 'unsubscribed', 'complained'])
                ->count();
        }

        if ($opened === 0) {
            $opened = Message::query()
                ->where('organization_id', '=', $organizationId)
                ->whereIn('status', ['opened', 'clicked', 'unsubscribed'])
                ->count();
        }

        if ($clicked === 0) {
            $clicked = Message::query()
                ->where('organization_id', '=', $organizationId)
                ->where('status', '=', 'clicked')
                ->count();
        }

        if ($unsubscribed === 0) {
            $unsubscribed = Message::query()
                ->where('organization_id', '=', $organizationId)
                ->where('status', '=', 'unsubscribed')
                ->count();
        }

        return [
            'sent' => $sent,
            'delivered' => $delivered,
            'bounced' => $bounced,
            'opened' => $opened,
            'clicked' => $clicked,
            'unsubscribed' => $unsubscribed,
            'delivery_rate' => $sent > 0 ? round(($delivered / $sent) * 100, 1) : 0.0,
            'open_rate' => $delivered > 0 ? round(($opened / $delivered) * 100, 1) : 0.0,
            'ctr' => $delivered > 0 ? round(($clicked / $delivered) * 100, 1) : 0.0,
            'ctor' => $opened > 0 ? round(($clicked / $opened) * 100, 1) : 0.0,
        ];
    }

    /**
     * @return array{data:array<int,array<string,mixed>>,meta:array<string,int|string|null>}
     */
    public function campaignEvents(int $organizationId, int $campaignId, array $filters = []): array
    {
        $query = EmailEvent::query()
            ->where('organization_id', '=', $organizationId)
            ->where('campaign_id', '=', $campaignId);

        return $this->buildEventCollection($query, $filters);
    }

    /**
     * @return array{data:array<int,array<string,mixed>>,meta:array<string,int|string|null>}
     */
    public function messageEvents(int $organizationId, int $messageId, array $filters = []): array
    {
        $query = EmailEvent::query()
            ->where('organization_id', '=', $organizationId)
            ->where('message_id', '=', $messageId);

        return $this->buildEventCollection($query, $filters);
    }

    /**
     * @return array<string,int|float>
     */
    public function campaignMetrics(int $organizationId, int $campaignId): array
    {
        $sent = Message::query()
            ->where('organization_id', '=', $organizationId)
            ->where('campaign_id', '=', $campaignId)
            ->whereIn('status', ['sent', 'delivered', 'opened', 'clicked', 'unsubscribed', 'complained', 'bounced'])
            ->count();

        $delivered = $this->campaignEventCount($organizationId, $campaignId, 'delivered');
        $bounced = $this->campaignEventCount($organizationId, $campaignId, 'bounced');
        $opened = $this->campaignEventCount($organizationId, $campaignId, 'opened');
        $clicked = $this->campaignEventCount($organizationId, $campaignId, 'clicked');
        $queued = $this->campaignEventCount($organizationId, $campaignId, 'queued');

        if ($delivered === 0) {
            $delivered = Message::query()
                ->where('organization_id', '=', $organizationId)
                ->where('campaign_id', '=', $campaignId)
                ->whereIn('status', ['delivered', 'opened', 'clicked', 'unsubscribed', 'complained'])
                ->count();
        }

        return [
            'queued' => $queued,
            'sent' => $sent,
            'delivered' => $delivered,
            'bounced' => $bounced,
            'opened' => $opened,
            'clicked' => $clicked,
            'delivery_rate' => $sent > 0 ? round(($delivered / $sent) * 100, 1) : 0.0,
            'open_rate' => $delivered > 0 ? round(($opened / $delivered) * 100, 1) : 0.0,
            'ctr' => $delivered > 0 ? round(($clicked / $delivered) * 100, 1) : 0.0,
            'ctor' => $opened > 0 ? round(($clicked / $opened) * 100, 1) : 0.0,
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recentCampaignEvents(int $organizationId, int $campaignId, int $limit = 20): array
    {
        $events = EmailEvent::query()
            ->where('organization_id', '=', $organizationId)
            ->where('campaign_id', '=', $campaignId)
            ->orderBy('timestamp', OrderDirection::DESC)
            ->orderBy('id', OrderDirection::DESC)
            ->limit(max(1, min($limit, 100)))
            ->get();

        return array_map(static function (EmailEvent $event): array {
            $metadata = json_decode((string) ($event->metadata ?? ''), true);
            return [
                'id' => $event->id,
                'message_id' => $event->message_id,
                'recipient_id' => $event->recipient_id ?? null,
                'event_type' => $event->event_type,
                'provider' => $event->provider ?? 'smtp',
                'timestamp' => $event->timestamp,
                'provider_message_id' => $event->provider_message_id,
                'metadata' => is_array($metadata) ? $metadata : null,
            ];
        }, $events);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recentOrganizationEvents(int $organizationId, int $limit = 20): array
    {
        $events = EmailEvent::query()
            ->where('organization_id', '=', $organizationId)
            ->orderBy('timestamp', OrderDirection::DESC)
            ->orderBy('id', OrderDirection::DESC)
            ->limit(max(1, min($limit, 100)))
            ->get();

        return array_map(static function (EmailEvent $event): array {
            return [
                'id' => $event->id,
                'campaign_id' => $event->campaign_id,
                'message_id' => $event->message_id,
                'recipient_id' => $event->recipient_id ?? null,
                'event_type' => $event->event_type,
                'timestamp' => $event->timestamp,
            ];
        }, $events);
    }

    private function eventCount(int $organizationId, string $eventType): int
    {
        return EmailEvent::query()
            ->where('organization_id', '=', $organizationId)
            ->where('event_type', '=', $eventType)
            ->count();
    }

    private function campaignEventCount(int $organizationId, int $campaignId, string $eventType): int
    {
        return EmailEvent::query()
            ->where('organization_id', '=', $organizationId)
            ->where('campaign_id', '=', $campaignId)
            ->where('event_type', '=', $eventType)
            ->count();
    }

    /**
     * @param object $query
     * @param array<string,mixed> $filters
     * @return array{data:array<int,array<string,mixed>>,meta:array<string,int|string|null>}
     */
    private function buildEventCollection(object $query, array $filters): array
    {
        $eventType = trim((string) ($filters['event_type'] ?? ''));
        $limit = max(1, min((int) ($filters['limit'] ?? 50), 200));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        $sort = strtolower((string) ($filters['sort'] ?? 'desc')) === 'asc'
            ? OrderDirection::ASC
            : OrderDirection::DESC;
        $after = trim((string) ($filters['after'] ?? ''));
        $before = trim((string) ($filters['before'] ?? ''));

        if ($eventType !== '') {
            $query->where('event_type', '=', $eventType);
        }

        if ($after !== '') {
            $query->where('timestamp', '>=', $after);
        }

        if ($before !== '') {
            $query->where('timestamp', '<=', $before);
        }

        $total = $query->count();

        $events = $query
            ->orderBy('timestamp', $sort)
            ->orderBy('id', $sort)
            ->offset($offset)
            ->limit($limit)
            ->get();

        $data = array_map(static function (EmailEvent $event): array {
            $metadata = json_decode((string) ($event->metadata ?? ''), true);

            return [
                'id' => $event->id,
                'message_id' => $event->message_id,
                'campaign_id' => $event->campaign_id,
                'organization_id' => $event->organization_id ?? null,
                'subscriber_id' => $event->subscriber_id,
                'recipient_id' => $event->recipient_id ?? null,
                'event_type' => $event->event_type,
                'provider' => $event->provider ?? 'smtp',
                'provider_message_id' => $event->provider_message_id,
                'timestamp' => $event->timestamp,
                'ip_address' => $event->ip_address,
                'user_agent' => $event->user_agent,
                'metadata' => is_array($metadata) ? $metadata : null,
            ];
        }, $events);

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'event_type' => $eventType !== '' ? $eventType : null,
                'sort' => $sort === OrderDirection::ASC ? 'asc' : 'desc',
                'after' => $after !== '' ? $after : null,
                'before' => $before !== '' ? $before : null,
            ],
        ];
    }
}
