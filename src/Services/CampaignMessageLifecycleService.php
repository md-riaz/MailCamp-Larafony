<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campaign;
use App\Models\EmailEvent;
use App\Models\Message;
use App\Models\QueueJob;
use App\Models\Recipient;
use App\Models\RecipientSuppression;
use App\Models\SmtpSetting;
use App\Models\SmtpUnsubscription;
use App\Models\Subscription;
use App\Models\Template;
use Larafony\Framework\Mail\Address;
use Larafony\Framework\Mail\Message\Email;
use Larafony\Framework\Mail\Transport\SmtpConfig;
use Larafony\Framework\Mail\Transport\SmtpTransport;
use Larafony\Framework\Web\Config;

final class CampaignMessageLifecycleService
{
    public function queueCampaign(Campaign $campaign): int
    {
        /** @var Template|null $template */
        $template = Template::query()->where('id', '=', $campaign->template_id)->first();
        if (!$template) {
            throw new \RuntimeException('Campaign template not found.');
        }

        $recipients = Recipient::query()->where('campaign_id', '=', $campaign->id)->get();
        $queuedCount = 0;
        $scheduledAt = $campaign->scheduled_at ?? date('Y-m-d H:i:s');

        $validation = $this->validateCampaignTemplate($campaign, $template);
        if (!$validation['ok']) {
            throw new \RuntimeException(implode(' ', $validation['errors']));
        }

        /** @var SmtpSetting|null $smtp */
        $smtp = SmtpSetting::query()
            ->where('organization_id', '=', $campaign->organization_id)
            ->where('id', '=', $campaign->smtp_setting_id)
            ->where('is_active', '=', 1)
            ->first();
        if (!$smtp) {
            $smtp = SmtpSetting::query()
                ->where('organization_id', '=', $campaign->organization_id)
                ->where('is_active', '=', 1)
                ->first();
            if ($smtp && !$campaign->smtp_setting_id) {
                $campaign->smtp_setting_id = $smtp->id;
                $campaign->save();
            }
        }

        if (!$smtp || !$smtp->validate()) {
            throw new \RuntimeException('Active SMTP settings are missing for campaign send.');
        }

        foreach ($recipients as $recipient) {
            if ($this->isRecipientSuppressed($campaign->organization_id, (int) $smtp->id, (string) $recipient->email)) {
                $recipient->status = 'unsubscribed';
                $recipient->save();
                continue;
            }

            $existingMessage = Message::query()
                ->where('campaign_id', '=', $campaign->id)
                ->where('recipient_id', '=', $recipient->id)
                ->first();

            if ($existingMessage) {
                continue;
            }

            $queueJob = QueueJob::query()
                ->where('campaign_id', '=', $campaign->id)
                ->where('recipient_id', '=', $recipient->id)
                ->first();

            if (!$queueJob) {
                $queueJob = new QueueJob()->fill([
                    'organization_id' => $campaign->organization_id,
                    'campaign_id' => $campaign->id,
                    'recipient_id' => $recipient->id,
                    'payload' => json_encode([
                        'campaign_id' => $campaign->id,
                        'recipient_id' => $recipient->id,
                    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'status' => 'pending',
                    'attempts' => 0,
                    'available_at' => $scheduledAt,
                ]);
                $queueJob->save();
            }

            $subject = $template->renderSubject($recipient->getCustomData());

            $message = new Message()->fill([
                'campaign_id' => $campaign->id,
                'organization_id' => $campaign->organization_id,
                'recipient_id' => $recipient->id,
                'recipient_email' => $recipient->email,
                'status' => 'queued',
                'provider' => 'smtp',
                'subject' => $subject,
            ]);
            $message->save();

            $this->recordEvent($message, 'queued', [
                'queue_job_id' => $queueJob->id,
                'available_at' => $queueJob->available_at,
                'recipient_email' => $recipient->email,
            ]);

            $queuedCount++;
        }

        $campaign->total_recipients = count($recipients);
        $campaign->save();

        return $queuedCount;
    }

    public function sendQueuedMessages(?int $campaignId = null, int $limit = 0): int
    {
        $query = QueueJob::query()
            ->where('status', '=', 'pending')
            ->where('available_at', '<=', date('Y-m-d H:i:s'));

        if ($campaignId !== null) {
            $query->where('campaign_id', '=', $campaignId);
        }

        $queueJobs = $query->orderBy('available_at')->get();
        $sent = 0;

        $interMessageDelayMicros = $this->interMessageDelayMicros();

        foreach ($queueJobs as $queueJob) {
            if ($limit > 0 && $sent >= $limit) {
                break;
            }

            $queueJob->status = 'processing';
            $queueJob->reserved_at = date('Y-m-d H:i:s');
            $queueJob->attempts = (int) $queueJob->attempts + 1;
            $queueJob->save();

            /** @var Message|null $message */
            $message = Message::query()
                ->where('campaign_id', '=', $queueJob->campaign_id)
                ->where('recipient_id', '=', $queueJob->recipient_id)
                ->where('status', '=', 'queued')
                ->first();

            if (!$message) {
                $this->markQueueJobFailed($queueJob);
                continue;
            }

            try {
                $this->sendQueuedMessage($message);
                $sent++;

                if ($interMessageDelayMicros > 0) {
                    usleep($interMessageDelayMicros);
                }
            } catch (\Throwable $exception) {
                $this->markMessageSendFailed($queueJob, $message, $exception);
            }
        }

        return $sent;
    }

    public function sendQueuedMessage(Message $message): void
    {
        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->where('id', '=', $message->campaign_id)->first();
        /** @var Recipient|null $recipient */
        $recipient = Recipient::query()->where('id', '=', $message->recipient_id)->first();

        if (!$campaign || !$recipient) {
            throw new \RuntimeException('Message campaign or recipient is missing.');
        }

        /** @var Template|null $template */
        $template = Template::query()->where('id', '=', $campaign->template_id)->first();
        /** @var SmtpSetting|null $smtp */
        $smtp = SmtpSetting::query()
            ->where('organization_id', '=', $campaign->organization_id)
            ->where('id', '=', $campaign->smtp_setting_id)
            ->where('is_active', '=', 1)
            ->first();
        if (!$smtp) {
            $smtp = SmtpSetting::query()
                ->where('organization_id', '=', $campaign->organization_id)
                ->where('is_active', '=', 1)
                ->first();
            if ($smtp && !$campaign->smtp_setting_id) {
                $campaign->smtp_setting_id = $smtp->id;
                $campaign->save();
            }
        }

        if (!$template || !$smtp || !$smtp->validate()) {
            throw new \RuntimeException('Active SMTP settings or template are missing for campaign send.');
        }

        $validation = $this->validateCampaignTemplate($campaign, $template);
        if (!$validation['ok']) {
            throw new \RuntimeException(implode(' ', $validation['errors']));
        }

        $subscription = $this->findOrCreateSubscription($campaign->organization_id, (int) $smtp->id, $recipient);

        if ($this->isRecipientSuppressed($campaign->organization_id, (int) $smtp->id, (string) $recipient->email)) {
            $message->status = 'failed';
            $message->save();
            throw new \RuntimeException('Recipient is suppressed and cannot be sent to.');
        }

        $payload = $this->buildRecipientPayload($recipient, $subscription, (int) $smtp->id);
        $subject = $template->renderSubject($payload);
        $html = $template->render($payload);
        $clickTracking = new ClickTrackingService();
        $html = $clickTracking->injectTrackedLinks($html, $message);
        $html = (new OpenTrackingService())->injectTrackingPixel($html, $message);

        $dsn = $this->buildDsn($smtp);
        $transport = new SmtpTransport(SmtpConfig::fromDsn($dsn));
        $unsubscribeUrl = (string) $payload['unsubscribe_url'];
        $email = (new Email())
            ->from(new Address($smtp->from_email, $smtp->from_name))
            ->to(new Address((string) $recipient->email, $recipient->name))
            ->subject($subject)
            ->html($html)
            ->text($this->buildTextBody($html, $unsubscribeUrl))
            ->header('List-Unsubscribe', '<' . $unsubscribeUrl . '>')
            ->header('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

        $transport->send($email);
        $sentAt = date('Y-m-d H:i:s');

        $message->status = 'sent';
        $message->subject = $subject;
        $message->sent_at = $sentAt;
        $message->provider_message_id = null;
        $message->save();

        $recipient->markAsSent();
        $recipient->save();

        $queueJob = QueueJob::query()
            ->where('campaign_id', '=', $campaign->id)
            ->where('recipient_id', '=', $recipient->id)
            ->first();

        if ($queueJob) {
            $queueJob->status = 'completed';
            $queueJob->completed_at = $sentAt;
            $queueJob->reserved_at = $queueJob->reserved_at ?? $sentAt;
            $queueJob->save();
        }

        $campaign->sent_count = (int) $campaign->sent_count + 1;
        $this->refreshCampaignStatusAfterAttempt($campaign, $sentAt);
        $campaign->save();

        $this->recordEvent($message, 'sent', [
            'transport' => 'smtp',
            'smtp_host' => $smtp->host,
            'smtp_port' => (int) $smtp->port,
            'smtp_encryption' => $smtp->encryption,
            'accepted_at' => $sentAt,
            'provider_message_id' => null,
            'recipient_email' => $recipient->email,
        ]);
    }

    private function markMessageSendFailed(QueueJob $queueJob, Message $message, \Throwable $exception): void
    {
        $failedAt = date('Y-m-d H:i:s');

        $queueJob->status = 'failed';
        $queueJob->completed_at = $failedAt;
        $queueJob->save();

        $message->status = 'failed';
        $message->save();

        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->where('id', '=', $message->campaign_id)->first();
        if ($campaign) {
            $campaign->failed_count = (int) $campaign->failed_count + 1;
            $this->refreshCampaignStatusAfterAttempt($campaign, $failedAt);
            $campaign->save();
        }

        $this->recordEvent($message, 'failed', [
            'queue_job_id' => $queueJob->id,
            'error' => $exception->getMessage(),
            'failed_at' => $failedAt,
        ]);
    }

    private function markQueueJobFailed(QueueJob $queueJob): void
    {
        $failedAt = date('Y-m-d H:i:s');
        $queueJob->status = 'failed';
        $queueJob->completed_at = $failedAt;
        $queueJob->save();

        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->where('id', '=', $queueJob->campaign_id)->first();
        if ($campaign) {
            $campaign->failed_count = (int) $campaign->failed_count + 1;
            $this->refreshCampaignStatusAfterAttempt($campaign, $failedAt);
            $campaign->save();
        }
    }

    private function refreshCampaignStatusAfterAttempt(Campaign $campaign, string $processedAt): void
    {
        $remainingJobs = QueueJob::query()
            ->where('campaign_id', '=', $campaign->id)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'failed')
            ->count();

        if ($remainingJobs > 0) {
            $campaign->status = (int) $campaign->sent_count > 0 ? 'sending' : 'active';
            return;
        }

        $campaign->completed_at = $processedAt;
        $campaign->status = (int) $campaign->sent_count >= (int) $campaign->total_recipients && (int) $campaign->total_recipients > 0
            ? 'sent'
            : 'failed';
    }

    private function interMessageDelayMicros(): int
    {
        $configuredDelaySeconds = (float) Config::get('queue.send_delay_seconds', 0);
        if ($configuredDelaySeconds > 0) {
            return max(0, (int) round($configuredDelaySeconds * 1_000_000));
        }

        $throttlePerHour = (int) Config::get('queue.throttle_per_hour', 0);
        if ($throttlePerHour <= 0) {
            return 0;
        }

        return (int) ceil((3600 / $throttlePerHour) * 1_000_000);
    }

    private function isRecipientSuppressed(int $organizationId, int $smtpId, string $email): bool
    {
        $normalizedEmail = strtolower(trim($email));
        if ($normalizedEmail === '') {
            return true;
        }

        /** @var Subscription|null $subscription */
        $subscription = Subscription::query()
            ->where('organization_id', '=', $organizationId)
            ->where('email', '=', $normalizedEmail)
            ->first();

        if ($subscription instanceof Subscription && (string) $subscription->status === 'bounced') {
            return true;
        }

        $globalSuppression = RecipientSuppression::query()
            ->where('organization_id', '=', $organizationId)
            ->where('email', '=', $normalizedEmail)
            ->first();
        if ($globalSuppression) {
            return true;
        }

        return SmtpUnsubscription::query()
            ->where('organization_id', '=', $organizationId)
            ->where('smtp_setting_id', '=', $smtpId)
            ->where('email', '=', $normalizedEmail)
            ->first() !== null;
    }

    private function findOrCreateSubscription(int $organizationId, int $smtpId, Recipient $recipient): Subscription
    {
        $normalizedEmail = strtolower(trim((string) $recipient->email));

        /** @var Subscription|null $subscription */
        $subscription = Subscription::query()
            ->where('organization_id', '=', $organizationId)
            ->where('email', '=', $normalizedEmail)
            ->first();

        if (!$subscription) {
            $subscription = new Subscription()->fill([
                'organization_id' => $organizationId,
                'email' => $normalizedEmail,
                'name' => $recipient->name,
                'status' => 'subscribed',
                'subscription_date' => date('Y-m-d H:i:s'),
            ]);
        }

        if (!$subscription->name && $recipient->name) {
            $subscription->name = $recipient->name;
        }

        $subscription->ensureToken($organizationId, $smtpId);
        $subscription->save();

        return $subscription;
    }

    /**
     * @return array<string, string>
     */
    private function buildRecipientPayload(Recipient $recipient, Subscription $subscription, int $smtpId): array
    {
        $payload = array_map(
            static fn (mixed $value): string => is_scalar($value) ? (string) $value : '',
            $recipient->getCustomData()
        );

        $payload['email'] = (string) $recipient->email;
        $payload['name'] = (string) ($recipient->name ?? $payload['name'] ?? '');
        $payload['unsubscribe_url'] = $this->buildUnsubscribeUrl(
            Subscription::generateToken((int) $subscription->organization_id, $smtpId, (string) $subscription->email)
        );

        return $payload;
    }

    private function buildUnsubscribeUrl(string $token): string
    {
        $appUrl = rtrim((string) ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost'), '/');
        return $appUrl . '/unsubscribe/' . rawurlencode($token);
    }

    private function buildTextBody(string $html, string $unsubscribeUrl): string
    {
        $text = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>'], ["\n", "\n", "\n", "\n\n", "\n"], $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\n{3,}/", "\n\n", (string) $text) ?? (string) $text;
        $text = trim((string) $text);

        if (!str_contains($text, $unsubscribeUrl)) {
            $text .= ($text !== '' ? "\n\n" : '') . 'Unsubscribe: ' . $unsubscribeUrl;
        }

        return $text;
    }

    private function buildDsn(SmtpSetting $smtp): string
    {
        $scheme = match ($smtp->encryption) {
            'ssl' => 'smtps',
            'tls' => 'smtp+tls',
            default => 'smtp',
        };

        $user = rawurlencode((string) $smtp->username);
        $pass = rawurlencode($smtp->decryptPassword());

        return sprintf('%s://%s:%s@%s:%d', $scheme, $user, $pass, $smtp->host, (int) $smtp->port);
    }

    /**
     * @return array{ok:bool,errors:array<int,string>,warnings:array<int,string>,variables:array<int,string>}
     */
    private function validateCampaignTemplate(Campaign $campaign, Template $template): array
    {
        return (new TemplateValidationService())->validateForCampaign($template, $campaign);
    }

    private function recordEvent(Message $message, string $eventType, array $metadata = []): void
    {
        $event = new EmailEvent()->fill([
            'message_id' => $message->id,
            'campaign_id' => $message->campaign_id,
            'organization_id' => $message->organization_id,
            'subscriber_id' => $message->subscriber_id,
            'recipient_id' => $message->recipient_id,
            'event_type' => $eventType,
            'provider' => $message->provider ?? 'smtp',
            'timestamp' => date('Y-m-d H:i:s'),
            'provider_message_id' => $message->provider_message_id,
        ]);
        $event->setMetadataArray($metadata);
        $event->save();
    }
}
