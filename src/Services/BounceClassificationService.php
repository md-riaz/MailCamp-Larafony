<?php

declare(strict_types=1);

namespace App\Services;

final class BounceClassificationService
{
    /**
     * @return array{bounce_type:string,category:string,severity:string,normalized_reason:string}
     */
    public function classify(?string $reason, ?string $smtpCode, ?string $dsnStatus = null, string $eventType = 'bounced'): array
    {
        $reasonText = strtolower(trim((string) $reason));
        $smtpCode = trim((string) $smtpCode);
        $dsnStatus = trim((string) $dsnStatus);

        if ($eventType === 'spam_report') {
            return [
                'bounce_type' => 'blocked',
                'category' => 'complaint',
                'severity' => 'high',
                'normalized_reason' => $reasonText !== '' ? $reasonText : 'recipient complaint / spam report',
            ];
        }

        if ($this->containsAny($reasonText, ['spam', 'complaint', 'policy', 'reputation', 'blacklist', 'blocked'])) {
            return [
                'bounce_type' => 'blocked',
                'category' => 'policy',
                'severity' => 'high',
                'normalized_reason' => $reasonText !== '' ? $reasonText : 'blocked by remote policy',
            ];
        }

        if ($this->containsAny($reasonText, ['dns', 'domain', 'host not found', 'no such domain', 'unrouteable'])) {
            return [
                'bounce_type' => 'domain_error',
                'category' => 'domain',
                'severity' => 'medium',
                'normalized_reason' => $reasonText !== '' ? $reasonText : 'domain resolution or routing error',
            ];
        }

        if ($this->containsAny($reasonText, ['mailbox full', 'quota exceeded', 'over quota', 'try again later', 'temporarily unavailable', 'temporary'])) {
            return [
                'bounce_type' => 'soft',
                'category' => 'temporary',
                'severity' => 'low',
                'normalized_reason' => $reasonText !== '' ? $reasonText : 'temporary delivery failure',
            ];
        }

        if ($this->containsAny($reasonText, ['user unknown', 'no such user', 'mailbox unavailable', 'recipient address rejected', 'invalid recipient'])) {
            return [
                'bounce_type' => 'hard',
                'category' => 'recipient',
                'severity' => 'high',
                'normalized_reason' => $reasonText !== '' ? $reasonText : 'recipient does not exist',
            ];
        }

        if (str_starts_with($smtpCode, '4') || str_starts_with($dsnStatus, '4')) {
            return [
                'bounce_type' => 'soft',
                'category' => 'temporary',
                'severity' => 'low',
                'normalized_reason' => $reasonText !== '' ? $reasonText : 'temporary smtp deferral',
            ];
        }

        if (str_starts_with($smtpCode, '5') || str_starts_with($dsnStatus, '5')) {
            return [
                'bounce_type' => 'hard',
                'category' => 'permanent',
                'severity' => 'high',
                'normalized_reason' => $reasonText !== '' ? $reasonText : 'permanent smtp failure',
            ];
        }

        return [
            'bounce_type' => $eventType === 'deferred' ? 'soft' : 'unknown',
            'category' => 'unknown',
            'severity' => $eventType === 'deferred' ? 'low' : 'medium',
            'normalized_reason' => $reasonText !== '' ? $reasonText : 'unclassified delivery issue',
        ];
    }

    /**
     * @param array<int,string> $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
