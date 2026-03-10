<?php

declare(strict_types=1);

namespace App\ViewDto;

use App\Models\Campaign;

final class CampaignViewDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $status,
        public readonly string $statusLabel,
        public readonly string $statusBadgeClass,
        public readonly int $totalRecipients,
        public readonly int $sentCount,
        public readonly int $failedCount,
        public readonly string $createdAtLabel,
        public readonly bool $canLaunch,
    ) {
    }

    public static function fromModel(Campaign $campaign): self
    {
        $status = (string) ($campaign->status ?? 'draft');

        $statusBadgeClass = match ($status) {
            'sent' => 'badge-success',
            'sending' => 'badge-warning',
            'draft' => 'badge-info',
            'failed' => 'badge-danger',
            default => 'badge-muted',
        };

        $createdAtLabel = '—';
        $createdAt = $campaign->created_at ?? null;
        if (is_object($createdAt) && method_exists($createdAt, 'format')) {
            $createdAtLabel = (string) $createdAt->format('M d, Y');
        } elseif (is_string($createdAt) && $createdAt !== '') {
            $timestamp = strtotime($createdAt);
            $createdAtLabel = $timestamp !== false ? date('M d, Y', $timestamp) : $createdAt;
        }

        return new self(
            id: (int) $campaign->id,
            name: (string) ($campaign->name ?? ''),
            status: $status,
            statusLabel: ucfirst($status),
            statusBadgeClass: $statusBadgeClass,
            totalRecipients: (int) ($campaign->total_recipients ?? 0),
            sentCount: (int) ($campaign->sent_count ?? 0),
            failedCount: (int) ($campaign->failed_count ?? 0),
            createdAtLabel: $createdAtLabel,
            canLaunch: $status === 'draft',
        );
    }
}
