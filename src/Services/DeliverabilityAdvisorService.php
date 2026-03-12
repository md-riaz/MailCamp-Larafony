<?php

declare(strict_types=1);

namespace App\Services;

final class DeliverabilityAdvisorService
{
    /**
     * @return array{
     *   domain:?string,
     *   checks:array<string,array{status:string,details:string}>,
     *   warnings:array<int,string>,
     *   recommendations:array<int,string>
     * }
     */
    public function analyze(?string $senderEmail): array
    {
        $domain = $this->extractDomain($senderEmail);
        $checks = [];
        $warnings = [];
        $recommendations = [];

        if ($domain === null) {
            return [
                'domain' => null,
                'checks' => [
                    'sender_domain' => ['status' => 'fail', 'details' => 'Sender email is missing or invalid.'],
                ],
                'warnings' => ['Sender email is missing or invalid, so deliverability checks cannot run.'],
                'recommendations' => ['Configure a valid sender email before launch.'],
            ];
        }

        $mxRecords = $this->lookup($domain, 'MX');
        $spfRecords = array_values(array_filter($this->lookup($domain, 'TXT'), static fn (string $record): bool => str_contains(strtolower($record), 'v=spf1')));
        $dmarcRecords = array_values(array_filter($this->lookup('_dmarc.' . $domain, 'TXT'), static fn (string $record): bool => str_contains(strtolower($record), 'v=dmarc1')));
        $dkimSelectors = ['default', 'selector1', 'selector2', 'mail', 'smtp'];
        $dkimHits = [];
        foreach ($dkimSelectors as $selector) {
            $records = $this->lookup($selector . '._domainkey.' . $domain, 'TXT');
            foreach ($records as $record) {
                if (str_contains(strtolower($record), 'k=rsa') || str_contains(strtolower($record), 'v=dkim1')) {
                    $dkimHits[$selector] = $record;
                    break;
                }
            }
        }

        $checks['mx'] = [
            'status' => $mxRecords !== [] ? 'pass' : 'warn',
            'details' => $mxRecords !== [] ? 'MX record found.' : 'No MX record detected for sender domain.',
        ];
        $checks['spf'] = [
            'status' => $spfRecords !== [] ? 'pass' : 'warn',
            'details' => $spfRecords !== [] ? $spfRecords[0] : 'No SPF TXT record found.',
        ];
        $checks['dmarc'] = [
            'status' => $dmarcRecords !== [] ? 'pass' : 'warn',
            'details' => $dmarcRecords !== [] ? $dmarcRecords[0] : 'No DMARC TXT record found at _dmarc.',
        ];
        $checks['dkim'] = [
            'status' => $dkimHits !== [] ? 'pass' : 'warn',
            'details' => $dkimHits !== []
                ? 'Detected selector(s): ' . implode(', ', array_keys($dkimHits))
                : 'No DKIM TXT records found for common selectors (default, selector1, selector2, mail, smtp).',
        ];

        if ($mxRecords === []) {
            $warnings[] = 'Sender domain has no MX record, which is unusual for production mail operations.';
            $recommendations[] = 'Publish a valid MX record for the sender domain.';
        }

        if ($spfRecords === []) {
            $warnings[] = 'SPF is missing for the sender domain.';
            $recommendations[] = 'Publish an SPF TXT record that authorizes your SMTP sending path.';
        }

        if ($dmarcRecords === []) {
            $warnings[] = 'DMARC is missing for the sender domain.';
            $recommendations[] = 'Add a DMARC TXT record at _dmarc.' . $domain . ' to improve trust and reporting.';
        }

        if ($dkimHits === []) {
            $warnings[] = 'DKIM could not be detected on common selectors for the sender domain.';
            $recommendations[] = 'Publish DKIM keys and confirm the selector used by your mail flow.';
        }

        if (preg_match('/(gmail|yahoo|hotmail|outlook)\./', $domain)) {
            $warnings[] = 'Free-mail sender domains are poor fits for production campaign delivery.';
            $recommendations[] = 'Use a dedicated business domain for campaign sending.';
        }

        return [
            'domain' => $domain,
            'checks' => $checks,
            'warnings' => array_values(array_unique($warnings)),
            'recommendations' => array_values(array_unique($recommendations)),
        ];
    }

    private function extractDomain(?string $senderEmail): ?string
    {
        $senderEmail = strtolower(trim((string) $senderEmail));
        if ($senderEmail === '' || !str_contains($senderEmail, '@')) {
            return null;
        }

        $domain = substr(strrchr($senderEmail, '@') ?: '', 1);
        return $domain !== '' ? $domain : null;
    }

    /**
     * @return array<int,string>
     */
    private function lookup(string $target, string $type): array
    {
        $type = strtoupper($type);
        $cmd = sprintf('dig +short %s %s 2>/dev/null', escapeshellarg($target), escapeshellarg($type));
        $output = shell_exec($cmd);
        if (!is_string($output) || trim($output) === '') {
            return [];
        }

        $records = array_values(array_filter(array_map(static fn (string $line): string => trim($line, " \t\n\r\0\x0B\""), preg_split('/\r\n|\r|\n/', $output) ?: [])));
        return $records;
    }
}
