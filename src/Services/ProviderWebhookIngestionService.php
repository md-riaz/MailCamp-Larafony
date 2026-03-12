<?php

declare(strict_types=1);

namespace App\Services;

final class ProviderWebhookIngestionService
{
    /**
     * @return array{provider:string,accepted:bool,event_count:int,normalized:array<int,array<string,mixed>>,message:string}
     */
    public function normalize(string $provider, string $rawPayload): array
    {
        $decoded = json_decode($rawPayload, true);
        if (!is_array($decoded)) {
            return [
                'provider' => $provider,
                'accepted' => false,
                'event_count' => 0,
                'normalized' => [],
                'message' => 'Invalid JSON payload.',
            ];
        }

        $events = $decoded;
        if (array_is_list($decoded) === false) {
            $events = [$decoded];
        }

        $normalized = [];
        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }

            $normalized[] = [
                'provider' => $provider,
                'provider_message_id' => $event['message_id'] ?? $event['smtp-id'] ?? $event['mail']['messageId'] ?? null,
                'recipient' => $event['email'] ?? $event['recipient'] ?? $event['mail']['destination'][0] ?? null,
                'event_type' => $this->mapEventType($provider, (string) ($event['event'] ?? $event['notificationType'] ?? $event['eventType'] ?? 'unknown')),
                'payload' => $event,
            ];
        }

        return [
            'provider' => $provider,
            'accepted' => true,
            'event_count' => count($normalized),
            'normalized' => $normalized,
            'message' => 'Normalized provider webhook payload.',
        ];
    }

    private function mapEventType(string $provider, string $eventType): string
    {
        $eventType = strtolower(trim($eventType));

        return match ($provider . ':' . $eventType) {
            'sendgrid:delivered', 'mailgun:delivered', 'ses:delivery' => 'delivered',
            'sendgrid:bounce', 'mailgun:failed', 'ses:bounce' => 'bounced',
            'sendgrid:dropped', 'mailgun:complained', 'ses:complaint' => 'spam_report',
            'sendgrid:deferred', 'mailgun:temporary_fail' => 'deferred',
            'sendgrid:open', 'mailgun:opened' => 'opened',
            'sendgrid:click', 'mailgun:clicked' => 'clicked',
            'sendgrid:unsubscribe', 'mailgun:unsubscribed' => 'unsubscribed',
            default => $eventType !== '' ? $eventType : 'unknown',
        };
    }
}
