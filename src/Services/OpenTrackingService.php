<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailEvent;
use App\Models\Message;
use App\Models\Recipient;
use Larafony\Framework\Database\Base\Query\Enums\OrderDirection;
use Psr\Http\Message\ServerRequestInterface;

final class OpenTrackingService
{
    private const DUPLICATE_WINDOW_SECONDS = 900;

    private const TRANSPARENT_PIXEL_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a6d8AAAAASUVORK5CYII=';

    /**
     * @return array<string, mixed>
     */
    public function track(string $messageId, ServerRequestInterface $request): array
    {
        if (!ctype_digit($messageId)) {
            return [
                'ok' => false,
                'reason' => 'invalid_message_id',
                'status' => 404,
            ];
        }

        /** @var Message|null $message */
        $message = Message::query()->where('id', '=', (int) $messageId)->first();
        if (!$message) {
            return [
                'ok' => false,
                'reason' => 'message_not_found',
                'status' => 404,
            ];
        }

        $context = $this->buildContext($request, $message);
        $duplicate = $this->findDuplicateOpen($message, $context['fingerprint'], $context['occurred_at']);

        if ($duplicate !== null) {
            return [
                'ok' => true,
                'reason' => 'duplicate_suppressed',
                'status' => 200,
                'message' => $message,
                'context' => $context,
                'counted' => false,
                'duplicate_event_id' => $duplicate->id,
            ];
        }

        $event = new EmailEvent()->fill([
            'message_id' => $message->id,
            'campaign_id' => $message->campaign_id,
            'subscriber_id' => $message->subscriber_id,
            'event_type' => 'opened',
            'timestamp' => $context['occurred_at'],
            'provider_message_id' => $message->provider_message_id,
            'ip_address' => $context['ip_address'],
            'user_agent' => $context['user_agent'],
        ]);
        $event->setMetadataArray([
            'provider' => 'smtp',
            'tracking_source' => 'smtp_open_pixel',
            'fingerprint' => $context['fingerprint'],
            'ip_source' => $context['ip_source'],
            'headers' => [
                'accept' => $context['accept'],
                'accept_language' => $context['accept_language'],
                'referer' => $context['referer'],
                'via' => $context['via'],
                'purpose' => $context['purpose'],
                'sec_purpose' => $context['sec_purpose'],
                'x_purpose' => $context['x_purpose'],
            ],
            'flags' => $context['flags'],
            'counted' => !$context['flags']['is_bot_like'],
            'normalized_at' => date('c'),
        ]);
        $event->save();

        if (!$context['flags']['is_bot_like']) {
            $this->applyOpenState($message, $context['occurred_at']);
        }

        return [
            'ok' => true,
            'reason' => $context['flags']['is_bot_like'] ? 'tracked_not_counted' : 'tracked',
            'status' => 200,
            'message' => $message,
            'context' => $context,
            'counted' => !$context['flags']['is_bot_like'],
            'event_id' => $event->id,
        ];
    }

    public function pixelBinary(): string
    {
        return (string) base64_decode(self::TRANSPARENT_PIXEL_PNG, true);
    }

    public function injectTrackingPixel(string $html, Message $message): string
    {
        if (!$message->id) {
            return $html;
        }

        $trackingUrl = $this->buildTrackingUrl((int) $message->id);
        $pixelTag = sprintf(
            '<img src="%s" alt="" width="1" height="1" style="display:block;border:0;outline:none;text-decoration:none;width:1px;height:1px;" />',
            htmlspecialchars($trackingUrl, ENT_QUOTES, 'UTF-8')
        );

        if (stripos($html, '</body>') !== false) {
            return (string) preg_replace('/<\/body>/i', $pixelTag . '</body>', $html, 1);
        }

        return $html . $pixelTag;
    }

    private function buildTrackingUrl(int $messageId): string
    {
        $appUrl = rtrim((string) ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost'), '/');
        return $appUrl . '/open/' . $messageId . '.png';
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(ServerRequestInterface $request, Message $message): array
    {
        $occurredAt = date('Y-m-d H:i:s');
        $headers = array_change_key_case($request->getHeaders(), CASE_LOWER);
        $userAgent = $this->firstHeaderValue($headers, 'user-agent');
        $accept = $this->firstHeaderValue($headers, 'accept');
        $acceptLanguage = $this->firstHeaderValue($headers, 'accept-language');
        $referer = $this->firstHeaderValue($headers, 'referer');
        $via = $this->firstHeaderValue($headers, 'via');
        $purpose = $this->firstHeaderValue($headers, 'purpose');
        $secPurpose = $this->firstHeaderValue($headers, 'sec-purpose');
        $xPurpose = $this->firstHeaderValue($headers, 'x-purpose');
        ['ip' => $ipAddress, 'source' => $ipSource] = $this->detectIpAddress($request, $headers);

        $flags = $this->detectFlags([
            'user_agent' => $userAgent,
            'accept' => $accept,
            'via' => $via,
            'purpose' => $purpose,
            'sec_purpose' => $secPurpose,
            'x_purpose' => $xPurpose,
            'ip_address' => $ipAddress,
        ]);

        return [
            'occurred_at' => $occurredAt,
            'ip_address' => $ipAddress,
            'ip_source' => $ipSource,
            'user_agent' => $userAgent,
            'accept' => $accept,
            'accept_language' => $acceptLanguage,
            'referer' => $referer,
            'via' => $via,
            'purpose' => $purpose,
            'sec_purpose' => $secPurpose,
            'x_purpose' => $xPurpose,
            'fingerprint' => sha1(implode('|', [
                (string) $message->id,
                (string) ($ipAddress ?? ''),
                strtolower(trim((string) ($userAgent ?? ''))),
                strtolower(trim((string) ($acceptLanguage ?? ''))),
            ])),
            'flags' => $flags,
        ];
    }

    /**
     * @param array<string, array<int, string>> $headers
     * @return array{ip:?string, source:string}
     */
    private function detectIpAddress(ServerRequestInterface $request, array $headers): array
    {
        foreach (['cf-connecting-ip', 'x-real-ip', 'x-forwarded-for'] as $header) {
            $value = $this->firstHeaderValue($headers, $header);
            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            $candidate = trim(explode(',', $value)[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return ['ip' => $candidate, 'source' => $header];
            }
        }

        $serverParams = $request->getServerParams();
        $candidate = $serverParams['REMOTE_ADDR'] ?? null;
        if (is_string($candidate) && filter_var($candidate, FILTER_VALIDATE_IP)) {
            return ['ip' => $candidate, 'source' => 'remote_addr'];
        }

        return ['ip' => null, 'source' => 'unknown'];
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, bool>
     */
    private function detectFlags(array $context): array
    {
        $userAgent = strtolower((string) ($context['user_agent'] ?? ''));
        $accept = strtolower((string) ($context['accept'] ?? ''));
        $via = strtolower((string) ($context['via'] ?? ''));
        $purpose = strtolower(trim(implode(' ', array_filter([
            $context['purpose'] ?? null,
            $context['sec_purpose'] ?? null,
            $context['x_purpose'] ?? null,
        ], static fn ($value): bool => is_string($value) && $value !== ''))));

        $isProxy = $this->containsAny($userAgent, [
            'googleimageproxy',
            'gmailimageproxy',
            'google image proxy',
            'googleusercontent',
            'yahoomailproxy',
            'mail.ru proxy',
            'imageproxy',
        ]) || $this->containsAny($via, ['google', 'yahoo', 'imageproxy']);

        $isPrefetch = $this->containsAny($purpose, ['prefetch', 'preview', 'prerender']);
        $isScanner = $this->containsAny($userAgent, [
            'curl/', 'wget/', 'python-requests', 'go-http-client', 'scanner', 'antispam',
            'barracuda', 'proofpoint', 'mimecast', 'outlook-ios', 'outlook-android',
            'safelinks', 'urlscan', 'virus', 'spam', 'security', 'fireeye', 'symantec',
        ]);
        $acceptsImages = $accept === '' || str_contains($accept, 'image/') || str_contains($accept, '*/*');
        $isHeadlessLike = $userAgent === '' || $this->containsAny($userAgent, ['headless', 'phantomjs']);
        $isBotLike = $isProxy || $isPrefetch || $isScanner || !$acceptsImages || $isHeadlessLike;

        return [
            'is_proxy' => $isProxy,
            'is_prefetch' => $isPrefetch,
            'is_scanner' => $isScanner,
            'is_headless_like' => $isHeadlessLike,
            'is_bot_like' => $isBotLike,
        ];
    }

    private function findDuplicateOpen(Message $message, string $fingerprint, string $occurredAt): ?EmailEvent
    {
        $events = EmailEvent::query()
            ->where('message_id', '=', $message->id)
            ->where('event_type', '=', 'opened')
            ->orderBy('id', OrderDirection::DESC)
            ->get();

        $occurredAtTs = strtotime($occurredAt) ?: time();

        foreach ($events as $event) {
            $metadata = json_decode((string) ($event->metadata ?? ''), true);
            $existingFingerprint = is_array($metadata) ? ($metadata['fingerprint'] ?? null) : null;
            if (!is_string($existingFingerprint) || $existingFingerprint === '') {
                continue;
            }

            if (!hash_equals($existingFingerprint, $fingerprint)) {
                continue;
            }

            $eventTs = strtotime((string) $event->timestamp);
            if ($eventTs !== false && abs($occurredAtTs - $eventTs) <= self::DUPLICATE_WINDOW_SECONDS) {
                return $event;
            }

            if ($message->status === 'opened') {
                return $event;
            }
        }

        return null;
    }

    private function applyOpenState(Message $message, string $occurredAt): void
    {
        if ($message->status !== 'clicked') {
            $message->status = 'opened';
        }
        $message->save();

        if (!$message->recipient_id) {
            return;
        }

        /** @var Recipient|null $recipient */
        $recipient = Recipient::query()->where('id', '=', $message->recipient_id)->first();
        if (!$recipient) {
            return;
        }

        if ($recipient->status !== 'clicked') {
            $recipient->status = 'opened';
        }
        if (!$recipient->opened_at) {
            $recipient->opened_at = $occurredAt;
        }
        $recipient->save();
    }

    /**
     * @param array<string, array<int, string>> $headers
     */
    private function firstHeaderValue(array $headers, string $name): ?string
    {
        $values = $headers[strtolower($name)] ?? null;
        return is_string($values[0] ?? null) && trim($values[0]) !== '' ? trim($values[0]) : null;
    }

    /**
     * @param list<string> $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, strtolower($needle))) {
                return true;
            }
        }

        return false;
    }
}
