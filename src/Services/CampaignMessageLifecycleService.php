<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campaign;
use App\Models\EmailEvent;
use App\Models\Message;
use App\Models\QueueJob;
use App\Models\Recipient;
use App\Models\SmtpSetting;
use App\Models\Template;
use Larafony\Framework\Mail\Address;
use Larafony\Framework\Mail\Message\Email;
use Larafony\Framework\Mail\Transport\SmtpConfig;
use Larafony\Framework\Mail\Transport\SmtpTransport;

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

        foreach ($recipients as $recipient) {
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
        $query = Message::query()->where('status', '=', 'queued');
        if ($campaignId !== null) {
            $query->where('campaign_id', '=', $campaignId);
        }

        $messages = $query->orderBy('id')->get();
        $sent = 0;

        foreach ($messages as $message) {
            $this->sendQueuedMessage($message);
            $sent++;

            if ($limit > 0 && $sent >= $limit) {
                break;
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
            ->where('is_active', '=', 1)
            ->first();

        if (!$template || !$smtp || !$smtp->validate()) {
            throw new \RuntimeException('Active SMTP settings or template are missing for campaign send.');
        }

        $payload = $recipient->getCustomData();
        $subject = $template->renderSubject($payload);
        $html = $template->render($payload);
        $clickTracking = new ClickTrackingService();
        $html = $clickTracking->injectTrackedLinks($html, $message);
        $html = (new OpenTrackingService())->injectTrackingPixel($html, $message);

        $dsn = $this->buildDsn($smtp);
        $transport = new SmtpTransport(SmtpConfig::fromDsn($dsn));
        $email = (new Email())
            ->from(new Address($smtp->from_email, $smtp->from_name))
            ->to(new Address((string) $recipient->email, $recipient->name))
            ->subject($subject)
            ->html($html);

        $result = $transport->sendWithResult($email);
        $sentAt = date('Y-m-d H:i:s');

        $message->status = 'sent';
        $message->subject = $subject;
        $message->sent_at = $sentAt;
        $message->provider_message_id = $result->providerMessageId;
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
            $queueJob->attempts = (int) $queueJob->attempts + 1;
            $queueJob->save();
        }

        $campaign->sent_count = (int) $campaign->sent_count + 1;
        if ($campaign->sent_count >= (int) $campaign->total_recipients && (int) $campaign->total_recipients > 0) {
            $campaign->status = 'sent';
            $campaign->completed_at = $sentAt;
        } else {
            $campaign->status = 'sending';
        }
        $campaign->save();

        $this->recordEvent($message, 'sent', [
            'transport' => 'smtp',
            'smtp_host' => $smtp->host,
            'smtp_port' => (int) $smtp->port,
            'smtp_encryption' => $smtp->encryption,
            'smtp_response' => $result->responseMessage,
            'smtp_code' => (string) $result->responseCode,
            'accepted_at' => $sentAt,
            'provider_message_id' => $result->providerMessageId,
            'recipient_email' => $recipient->email,
        ]);
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
