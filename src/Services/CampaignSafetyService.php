<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campaign;
use App\Models\EmailEvent;
use App\Models\Recipient;
use App\Models\SmtpSetting;
use App\Models\Template;

final class CampaignSafetyService
{
    /**
     * @return array{
     *   ok:bool,
     *   should_pause:bool,
     *   risk_level:string,
     *   errors:array<int,string>,
     *   warnings:array<int,string>,
     *   metrics:array<string,int|float|string|null>
     * }
     */
    public function evaluate(Campaign $campaign, ?Template $template = null, ?SmtpSetting $smtpSetting = null): array
    {
        $errors = [];
        $warnings = [];

        $recipientCount = Recipient::query()->where('campaign_id', '=', $campaign->id)->count();
        $bounced = EmailEvent::query()->where('campaign_id', '=', $campaign->id)->where('event_type', '=', 'bounced')->count();
        $complaints = EmailEvent::query()->where('campaign_id', '=', $campaign->id)->where('event_type', '=', 'spam_report')->count();
        $delivered = EmailEvent::query()->where('campaign_id', '=', $campaign->id)->where('event_type', '=', 'delivered')->count();
        $opened = EmailEvent::query()->where('campaign_id', '=', $campaign->id)->where('event_type', '=', 'opened')->count();
        $clicked = EmailEvent::query()->where('campaign_id', '=', $campaign->id)->where('event_type', '=', 'clicked')->count();

        $bounceRate = $recipientCount > 0 ? round(($bounced / $recipientCount) * 100, 2) : 0.0;
        $complaintRate = $recipientCount > 0 ? round(($complaints / $recipientCount) * 100, 2) : 0.0;
        $openRate = $delivered > 0 ? round(($opened / $delivered) * 100, 2) : 0.0;
        $clickRate = $delivered > 0 ? round(($clicked / $delivered) * 100, 2) : 0.0;

        if ($recipientCount === 0) {
            $errors[] = 'Campaign has no recipients.';
        }

        if (!$template) {
            $errors[] = 'Campaign template is missing.';
        }

        if (!$smtpSetting || !$smtpSetting->validate()) {
            $errors[] = 'SMTP settings are missing or invalid.';
        }

        if ($template) {
            $html = strtolower((string) ($template->html_content ?? ''));
            $subject = strtolower((string) ($template->subject ?? ''));

            if (!str_contains($html, '{{unsubscribe_url}}')) {
                $errors[] = 'Template is missing required {{unsubscribe_url}}.';
            }

            if (!str_contains($html, '<a ') || !str_contains($html, 'href=')) {
                $warnings[] = 'Template contains no obvious links, so click tracking and CTA performance will be limited.';
            }

            if (!str_contains($html, '{{name}}') && !str_contains($subject, '{{name}}')) {
                $warnings[] = 'Template has no visible personalization such as {{name}}.';
            }

            if (strlen(strip_tags($html)) < 80) {
                $warnings[] = 'Template body looks very short and may perform poorly or appear suspicious.';
            }
        }

        $fromEmail = strtolower((string) ($smtpSetting?->from_email ?? ''));
        if ($fromEmail === '' || !str_contains($fromEmail, '@')) {
            $errors[] = 'SMTP sender email is not configured correctly.';
        } else {
            $domain = substr(strrchr($fromEmail, '@') ?: '', 1);
            if ($domain === '' || !str_contains($domain, '.')) {
                $errors[] = 'SMTP sender domain looks invalid.';
            }
            if (preg_match('/(gmail|yahoo|hotmail|outlook)\./', $domain)) {
                $warnings[] = 'Using a free-mail sender domain can hurt deliverability for campaigns.';
            }
        }

        if ($bounceRate > 8.0) {
            $errors[] = sprintf('Bounce rate %.2f%% exceeds autopause threshold of 8%%.', $bounceRate);
        }

        if ($complaintRate > 0.3) {
            $errors[] = sprintf('Complaint rate %.2f%% exceeds threshold of 0.3%%.', $complaintRate);
        }

        if ($recipientCount > 0 && $recipientCount < 5) {
            $warnings[] = 'Very small recipient batches can make deliverability metrics noisy.';
        }

        $riskLevel = 'low';
        if ($errors !== []) {
            $riskLevel = 'high';
        } elseif (count($warnings) >= 2) {
            $riskLevel = 'medium';
        }

        return [
            'ok' => $errors === [],
            'should_pause' => $bounceRate > 8.0 || $complaintRate > 0.3,
            'risk_level' => $riskLevel,
            'errors' => $errors,
            'warnings' => $warnings,
            'metrics' => [
                'recipients' => $recipientCount,
                'delivered' => $delivered,
                'opened' => $opened,
                'clicked' => $clicked,
                'bounced' => $bounced,
                'complaints' => $complaints,
                'bounce_rate' => $bounceRate,
                'complaint_rate' => $complaintRate,
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
                'sender_email' => $smtpSetting?->from_email,
            ],
        ];
    }
}
