<?php

declare(strict_types=1);

namespace App\Services;

use Psr\Http\Message\ServerRequestInterface;

final class WebhookSecurityService
{
    /**
     * @return array{ok:bool,reason:string,idempotency_key:string,signature:?string,timestamp:?int,headers:array<string,mixed>}
     */
    public function inspect(ServerRequestInterface $request, string $rawPayload, string $provider = 'smtp'): array
    {
        $headers = array_change_key_case($request->getHeaders(), CASE_LOWER);
        $signature = $this->firstHeaderValue($headers, 'x-webhook-signature')
            ?? $this->firstHeaderValue($headers, 'x-signature')
            ?? $this->firstHeaderValue($headers, 'x-smtp-signature');
        $timestampValue = $this->firstHeaderValue($headers, 'x-webhook-timestamp')
            ?? $this->firstHeaderValue($headers, 'x-timestamp');
        $timestamp = is_numeric($timestampValue) ? (int) $timestampValue : null;

        $secret = trim((string) (getenv('SMTP_REPORT_WEBHOOK_SECRET') ?: ''));
        if ($secret !== '') {
            if ($signature === null || $timestamp === null) {
                return [
                    'ok' => false,
                    'reason' => 'missing_signature_or_timestamp',
                    'idempotency_key' => $this->requestIdempotencyKey($provider, $rawPayload, $timestampValue),
                    'signature' => $signature,
                    'timestamp' => $timestamp,
                    'headers' => $this->headerSnapshot($headers),
                ];
            }

            if (abs(time() - $timestamp) > 300) {
                return [
                    'ok' => false,
                    'reason' => 'timestamp_outside_replay_window',
                    'idempotency_key' => $this->requestIdempotencyKey($provider, $rawPayload, $timestampValue),
                    'signature' => $signature,
                    'timestamp' => $timestamp,
                    'headers' => $this->headerSnapshot($headers),
                ];
            }

            $expected = hash_hmac('sha256', $timestamp . '.' . $rawPayload, $secret);
            if (!hash_equals($expected, $signature)) {
                return [
                    'ok' => false,
                    'reason' => 'invalid_signature',
                    'idempotency_key' => $this->requestIdempotencyKey($provider, $rawPayload, $timestampValue),
                    'signature' => $signature,
                    'timestamp' => $timestamp,
                    'headers' => $this->headerSnapshot($headers),
                ];
            }
        }

        return [
            'ok' => true,
            'reason' => $secret !== '' ? 'verified' : 'unsigned_allowed',
            'idempotency_key' => $this->requestIdempotencyKey($provider, $rawPayload, $timestampValue),
            'signature' => $signature,
            'timestamp' => $timestamp,
            'headers' => $this->headerSnapshot($headers),
        ];
    }

    private function requestIdempotencyKey(string $provider, string $rawPayload, ?string $timestampValue): string
    {
        return hash('sha256', implode('|', [$provider, (string) $timestampValue, substr($rawPayload, 0, 2048)]));
    }

    /**
     * @param array<string, array<int, string>> $headers
     * @return array<string,mixed>
     */
    private function headerSnapshot(array $headers): array
    {
        $keys = ['x-webhook-signature', 'x-signature', 'x-smtp-signature', 'x-webhook-timestamp', 'x-timestamp', 'content-type', 'user-agent'];
        $snapshot = [];
        foreach ($keys as $key) {
            $value = $this->firstHeaderValue($headers, $key);
            if ($value !== null) {
                $snapshot[$key] = $value;
            }
        }

        return $snapshot;
    }

    /**
     * @param array<string, array<int, string>> $headers
     */
    private function firstHeaderValue(array $headers, string $name): ?string
    {
        $values = $headers[strtolower($name)] ?? null;
        return is_string($values[0] ?? null) && trim($values[0]) !== '' ? trim($values[0]) : null;
    }
}
