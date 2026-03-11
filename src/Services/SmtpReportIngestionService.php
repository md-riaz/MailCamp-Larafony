<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bounce;
use App\Models\EmailEvent;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\Subscription;
use App\Models\Webhook;

final class SmtpReportIngestionService
{
    public function __construct(
        private readonly SmtpDsnParser $parser = new SmtpDsnParser(),
    ) {
    }

    /**
     * @return array{received:int,processed:int,duplicates:int,unmatched:int,event_type:string}
     */
    public function ingest(string $rawPayload, string $source = 'smtp-report'): array
    {
        $parsed = $this->parser->parse($rawPayload);

        $idempotencyKey = $this->buildIdempotencyKey($source, $parsed, $rawPayload);
        $existing = Webhook::query()->where('idempotency_key', '=', $idempotencyKey)->first();
        if ($existing) {
            return [
                'received' => 1,
                'processed' => 0,
                'duplicates' => 1,
                'unmatched' => 0,
                'event_type' => (string) ($parsed['event_type'] ?? 'unknown'),
            ];
        }

        $message = $this->resolveMessage($parsed['provider_message_id'], $parsed['recipient']);

        $webhook = new Webhook();
        $webhook->provider = 'smtp';
        $webhook->event_type = (string) $parsed['event_type'];
        $webhook->provider_message_id = $parsed['provider_message_id'];
        $webhook->idempotency_key = $idempotencyKey;
        $webhook->processing_status = $message ? 'processed' : 'failed';
        $webhook->payload = json_encode(['raw' => $rawPayload], JSON_UNESCAPED_UNICODE);
        $webhook->headers = json_encode(['source' => $source], JSON_UNESCAPED_UNICODE);
        $webhook->campaign_id = $message?->campaign_id;
        $webhook->message_id = $message?->id;
        $webhook->subscriber_id = $message?->subscriber_id;
        $webhook->processed_at = date('Y-m-d H:i:s');
        $webhook->save();

        if (!$message) {
            return [
                'received' => 1,
                'processed' => 0,
                'duplicates' => 0,
                'unmatched' => 1,
                'event_type' => (string) ($parsed['event_type'] ?? 'unknown'),
            ];
        }

        $event = new EmailEvent();
        $event->message_id = $message->id;
        $event->campaign_id = $message->campaign_id;
        $event->subscriber_id = $message->subscriber_id;
        $event->event_type = (string) $parsed['event_type'];
        $event->timestamp = date('Y-m-d H:i:s');
        $event->provider_message_id = $parsed['provider_message_id'] ?: $message->provider_message_id;
        $event->metadata = json_encode([
            'source' => 'smtp-report',
            'smtp_code' => $parsed['smtp_code'],
            'bounce_reason' => $parsed['bounce_reason'],
            'bounce_type' => $parsed['bounce_type'],
            'extra' => $parsed['metadata'],
        ], JSON_UNESCAPED_UNICODE);
        $event->save();

        $this->updateMessageStatus($message, (string) $parsed['event_type']);

        if (in_array($parsed['event_type'], ['bounced', 'deferred'], true)) {
            $bounce = new Bounce();
            $bounce->message_id = $message->id;
            $bounce->campaign_id = $message->campaign_id;
            $bounce->subscriber_id = $message->subscriber_id;
            $bounce->provider_message_id = $parsed['provider_message_id'] ?: $message->provider_message_id;
            $bounce->bounce_type = (string) ($parsed['bounce_type'] ?? ($parsed['event_type'] === 'deferred' ? 'soft' : 'unknown'));
            $bounce->smtp_code = $parsed['smtp_code'];
            $bounce->bounce_reason = $parsed['bounce_reason'];
            $bounce->metadata = json_encode($parsed['metadata'], JSON_UNESCAPED_UNICODE);
            $bounce->bounced_at = date('Y-m-d H:i:s');
            $bounce->save();
        }

        return [
            'received' => 1,
            'processed' => 1,
            'duplicates' => 0,
            'unmatched' => 0,
            'event_type' => (string) $parsed['event_type'],
        ];
    }

    private function buildIdempotencyKey(string $source, array $parsed, string $raw): string
    {
        $base = implode('|', [
            'smtp',
            $source,
            (string) ($parsed['event_type'] ?? ''),
            (string) ($parsed['provider_message_id'] ?? ''),
            (string) ($parsed['recipient'] ?? ''),
            (string) ($parsed['smtp_code'] ?? ''),
        ]);

        return hash('sha256', $base . '|' . substr($raw, 0, 1024));
    }

    private function resolveMessage(?string $providerMessageId, ?string $recipient): ?Message
    {
        if (!empty($providerMessageId)) {
            $msg = Message::query()->where('provider_message_id', '=', $providerMessageId)->first();
            if ($msg instanceof Message) {
                return $msg;
            }
        }

        if (!empty($recipient)) {
            $recipientRow = Recipient::query()->where('email', '=', $recipient)->first();
            if ($recipientRow instanceof Recipient) {
                $msg = Message::query()->where('recipient_id', '=', $recipientRow->id)->orderBy('id', \Larafony\Framework\Database\Base\Query\Enums\OrderDirection::DESC)->first();
                if ($msg instanceof Message) {
                    return $msg;
                }
            }

            $subscriptionRow = Subscription::query()->where('email', '=', $recipient)->first();
            if ($subscriptionRow instanceof Subscription) {
                $msg = Message::query()->where('subscriber_id', '=', $subscriptionRow->id)->orderBy('id', \Larafony\Framework\Database\Base\Query\Enums\OrderDirection::DESC)->first();
                if ($msg instanceof Message) {
                    return $msg;
                }
            }
        }

        return null;
    }

    private function updateMessageStatus(Message $message, string $eventType): void
    {
        if ($eventType === 'delivered') {
            $message->status = 'delivered';
            $message->delivered_at = date('Y-m-d H:i:s');
            $message->save();
            return;
        }

        if ($eventType === 'bounced') {
            $message->status = 'bounced';
            $message->save();
            return;
        }

        if ($eventType === 'deferred') {
            $message->status = 'failed';
            $message->save();
            return;
        }

        if ($eventType === 'spam_report') {
            $message->status = 'complained';
            $message->save();
        }
    }
}
