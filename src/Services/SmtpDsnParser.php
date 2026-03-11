<?php

declare(strict_types=1);

namespace App\Services;

final class SmtpDsnParser
{
    /**
     * @return array{event_type:string, provider_message_id:?string, recipient:?string, smtp_code:?string, bounce_reason:?string, bounce_type:?string, metadata:array<string,mixed>}
     */
    public function parse(string $raw): array
    {
        $raw = trim($raw);

        $result = [
            'event_type' => 'deferred',
            'provider_message_id' => null,
            'recipient' => null,
            'smtp_code' => null,
            'bounce_reason' => null,
            'bounce_type' => null,
            'metadata' => ['source' => 'smtp_dsn'],
        ];

        // Extract message-id
        if (preg_match('/(?:Original-|Final-)?Message-ID:\s*<?([^>\s]+)>?/i', $raw, $m)) {
            $result['provider_message_id'] = trim($m[1]);
        } elseif (preg_match('/Message-ID:\s*<?([^>\s]+)>?/i', $raw, $m)) {
            $result['provider_message_id'] = trim($m[1]);
        }

        // Extract recipient
        if (preg_match('/Final-Recipient:\s*rfc822;\s*([^\s\r\n]+)/i', $raw, $m)) {
            $result['recipient'] = strtolower(trim($m[1]));
        } elseif (preg_match('/Original-Recipient:\s*rfc822;\s*([^\s\r\n]+)/i', $raw, $m)) {
            $result['recipient'] = strtolower(trim($m[1]));
        } elseif (preg_match('/\bTo:\s*([^\s<>]+@[^\s<>]+)/i', $raw, $m)) {
            $result['recipient'] = strtolower(trim($m[1]));
        }

        // Diagnostic status/code
        if (preg_match('/Status:\s*([245]\.\d+\.\d+)/i', $raw, $m)) {
            $result['metadata']['dsn_status'] = $m[1];
        }

        if (preg_match('/(?:Diagnostic-Code:\s*[^;]*;\s*)?([245]\d{2}\s*[245]\.\d+\.\d+[^\r\n]*)/i', $raw, $m)) {
            $line = trim($m[1]);
            $result['bounce_reason'] = $line;
            if (preg_match('/^([245]\d{2})/', $line, $sm)) {
                $result['smtp_code'] = $sm[1];
            }
        } elseif (preg_match('/\b([245]\d{2})\b/', $raw, $m)) {
            $result['smtp_code'] = $m[1];
        }

        $text = strtolower($raw);

        if (str_contains($text, 'feedback-type: abuse') || str_contains($text, 'spam complaint') || str_contains($text, 'x-complaint-type')) {
            $result['event_type'] = 'spam_report';
            return $result;
        }

        $status = (string) ($result['metadata']['dsn_status'] ?? '');
        $smtpCode = (string) ($result['smtp_code'] ?? '');

        if (str_starts_with($status, '2') || str_starts_with($smtpCode, '2')) {
            $result['event_type'] = 'delivered';
            return $result;
        }

        if (str_starts_with($status, '5') || str_starts_with($smtpCode, '5')) {
            $result['event_type'] = 'bounced';
            $reason = strtolower((string) ($result['bounce_reason'] ?? ''));
            $result['bounce_type'] = $this->classifyBounce($reason, $smtpCode, hardDefault: true);
            return $result;
        }

        if (str_starts_with($status, '4') || str_starts_with($smtpCode, '4')) {
            $result['event_type'] = 'deferred';
            $reason = strtolower((string) ($result['bounce_reason'] ?? ''));
            $result['bounce_type'] = $this->classifyBounce($reason, $smtpCode, hardDefault: false);
            return $result;
        }

        // Fallback heuristics
        if (str_contains($text, 'user unknown') || str_contains($text, 'mailbox unavailable') || str_contains($text, 'no such user')) {
            $result['event_type'] = 'bounced';
            $result['bounce_type'] = 'hard';
        }

        return $result;
    }

    private function classifyBounce(string $reason, string $smtpCode, bool $hardDefault): string
    {
        if (str_contains($reason, 'spam') || str_contains($reason, 'blocked') || str_contains($reason, 'policy')) {
            return 'blocked';
        }

        if (str_contains($reason, 'dns') || str_contains($reason, 'domain') || str_contains($reason, 'host not found')) {
            return 'domain_error';
        }

        if (!$hardDefault || str_starts_with($smtpCode, '4')) {
            return 'soft';
        }

        return 'hard';
    }
}
