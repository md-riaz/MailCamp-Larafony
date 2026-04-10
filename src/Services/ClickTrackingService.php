<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailEvent;
use App\Models\Link;
use App\Models\Message;
use App\Models\Recipient;
use DOMDocument;
use DOMElement;
use Psr\Http\Message\ServerRequestInterface;

final class ClickTrackingService
{
    public function injectTrackedLinks(string $html, Message $message): string
    {
        if (!$message->id || trim($html) === '') {
            return $html;
        }

        if (!class_exists(DOMDocument::class)) {
            return $html;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $loaded = $document->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        if (!$loaded) {
            return $html;
        }

        $links = $document->getElementsByTagName('a');
        /** @var DOMElement $element */
        foreach ($links as $element) {
            $href = trim((string) $element->getAttribute('href'));
            if (!$this->shouldTrackHref($href)) {
                continue;
            }

            $normalized = $this->normalizeUrl($href);
            $trackingUrl = $this->buildTrackingUrl((int) $message->id, $normalized);
            $element->setAttribute('href', $trackingUrl);
            $this->storeTrackedLink($message, $normalized);
        }

        return (string) $document->saveHTML();
    }

    /**
     * @return array<string,mixed>
     */
    public function track(string $messageId, ?string $url, ServerRequestInterface $request): array
    {
        if (!ctype_digit($messageId)) {
            return ['ok' => false, 'reason' => 'invalid_message_id', 'status' => 404];
        }

        $targetUrl = $this->normalizeUrl((string) $url);
        if (!$this->shouldTrackHref($targetUrl)) {
            return ['ok' => false, 'reason' => 'invalid_target_url', 'status' => 404];
        }

        /** @var Message|null $message */
        $message = Message::query()->where('id', '=', (int) $messageId)->first();
        if (!$message) {
            return ['ok' => false, 'reason' => 'message_not_found', 'status' => 404];
        }

        // Verify the URL was actually tracked for this message to prevent open redirects
        $urlHash = hash('sha256', $targetUrl);
        $existingLink = Link::query()
            ->where('message_id', '=', $message->id)
            ->where('url_hash', '=', $urlHash)
            ->first();

        if (!$existingLink) {
            return ['ok' => false, 'reason' => 'url_not_tracked_for_message', 'status' => 404];
        }

        $occurredAt = date('Y-m-d H:i:s');
        $headers = array_change_key_case($request->getHeaders(), CASE_LOWER);
        $userAgent = $this->firstHeaderValue($headers, 'user-agent');
        $referer = $this->firstHeaderValue($headers, 'referer');
        $acceptLanguage = $this->firstHeaderValue($headers, 'accept-language');
        $ipAddress = $this->detectIpAddress($request, $headers);

        $link = $this->storeTrackedLink($message, $targetUrl);
        $link->click_count = (int) ($link->click_count ?? 0) + 1;
        $link->last_clicked_at = $occurredAt;
        $link->save();

        $event = new EmailEvent()->fill([
            'message_id' => $message->id,
            'campaign_id' => $message->campaign_id,
            'organization_id' => $message->organization_id,
            'subscriber_id' => $message->subscriber_id,
            'recipient_id' => $message->recipient_id,
            'event_type' => 'clicked',
            'provider' => $message->provider ?? 'smtp',
            'timestamp' => $occurredAt,
            'provider_message_id' => $message->provider_message_id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => json_encode([
                'clicked_url' => $targetUrl,
                'url_hash' => hash('sha256', $targetUrl),
                'referer' => $referer,
                'accept_language' => $acceptLanguage,
                'tracking_source' => 'smtp_click_redirect',
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
        $event->save();

        $this->applyClickState($message, $occurredAt);

        return [
            'ok' => true,
            'status' => 302,
            'redirect_url' => $targetUrl,
            'message' => $message,
            'link' => $link,
            'event_id' => $event->id,
        ];
    }

    private function buildTrackingUrl(int $messageId, string $url): string
    {
        $appUrl = rtrim((string) ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost'), '/');
        return $appUrl . '/click/' . $messageId . '?url=' . rawurlencode($url);
    }

    private function shouldTrackHref(string $href): bool
    {
        if ($href === '' || str_starts_with($href, '#')) {
            return false;
        }

        $lower = strtolower($href);
        if (str_starts_with($lower, 'mailto:') || str_starts_with($lower, 'tel:') || str_starts_with($lower, 'javascript:')) {
            return false;
        }

        return filter_var($href, FILTER_VALIDATE_URL) !== false;
    }

    private function normalizeUrl(string $url): string
    {
        return trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function storeTrackedLink(Message $message, string $url): Link
    {
        $urlHash = hash('sha256', $url);

        /** @var Link|null $link */
        $link = Link::query()
            ->where('message_id', '=', $message->id)
            ->where('url_hash', '=', $urlHash)
            ->first();

        if ($link) {
            return $link;
        }

        $link = new Link()->fill([
            'message_id' => $message->id,
            'campaign_id' => $message->campaign_id,
            'organization_id' => $message->organization_id,
            'subscriber_id' => $message->subscriber_id,
            'recipient_id' => $message->recipient_id,
            'url' => $url,
            'url_hash' => $urlHash,
            'click_count' => 0,
        ]);
        $link->save();

        return $link;
    }

    private function applyClickState(Message $message, string $occurredAt): void
    {
        $message->status = 'clicked';
        $message->save();

        if (!$message->recipient_id) {
            return;
        }

        /** @var Recipient|null $recipient */
        $recipient = Recipient::query()->where('id', '=', $message->recipient_id)->first();
        if (!$recipient) {
            return;
        }

        $recipient->status = 'clicked';
        if (!$recipient->opened_at) {
            $recipient->opened_at = $occurredAt;
        }
        if (!$recipient->clicked_at) {
            $recipient->clicked_at = $occurredAt;
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
     * @param array<string, array<int, string>> $headers
     */
    private function detectIpAddress(ServerRequestInterface $request, array $headers): ?string
    {
        foreach (['cf-connecting-ip', 'x-real-ip', 'x-forwarded-for'] as $header) {
            $value = $this->firstHeaderValue($headers, $header);
            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            $candidate = trim(explode(',', $value)[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }

        $candidate = $request->getServerParams()['REMOTE_ADDR'] ?? null;
        return is_string($candidate) && filter_var($candidate, FILTER_VALIDATE_IP) ? $candidate : null;
    }
}
