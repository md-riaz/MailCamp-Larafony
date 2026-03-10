<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Attributes\HasMany;
use Larafony\Framework\Database\ORM\Model;

class Campaign extends Model
{
    public string $table { get => 'campaigns'; }

    public array $fillable = [
        'organization_id', 'template_id', 'name', 'status', 
        'scheduled_at', 'started_at', 'completed_at',
        'total_recipients', 'sent_count', 'failed_count', 'created_by'
    ];

    public ?int $organization_id {
        get => $this->organization_id ?? null;
        set {
            $this->organization_id = $value;
            $this->markPropertyAsChanged('organization_id');
        }
    }

    public ?int $template_id {
        get => $this->template_id ?? null;
        set {
            $this->template_id = $value;
            $this->markPropertyAsChanged('template_id');
        }
    }

    public ?string $name {
        get => $this->name ?? null;
        set {
            $this->name = $value;
            $this->markPropertyAsChanged('name');
        }
    }

    public ?string $status {
        get => $this->status ?? 'draft';
        set {
            $this->status = $value;
            $this->markPropertyAsChanged('status');
        }
    }

    public ?string $scheduled_at {
        get => $this->scheduled_at ?? null;
        set {
            $this->scheduled_at = $value;
            $this->markPropertyAsChanged('scheduled_at');
        }
    }

    public ?string $started_at {
        get => $this->started_at ?? null;
        set {
            $this->started_at = $value;
            $this->markPropertyAsChanged('started_at');
        }
    }

    public ?string $completed_at {
        get => $this->completed_at ?? null;
        set {
            $this->completed_at = $value;
            $this->markPropertyAsChanged('completed_at');
        }
    }

    public ?int $total_recipients {
        get => $this->total_recipients ?? 0;
        set {
            $this->total_recipients = $value;
            $this->markPropertyAsChanged('total_recipients');
        }
    }

    public ?int $sent_count {
        get => $this->sent_count ?? 0;
        set {
            $this->sent_count = $value;
            $this->markPropertyAsChanged('sent_count');
        }
    }

    public ?int $failed_count {
        get => $this->failed_count ?? 0;
        set {
            $this->failed_count = $value;
            $this->markPropertyAsChanged('failed_count');
        }
    }

    public ?int $created_by {
        get => $this->created_by ?? null;
        set {
            $this->created_by = $value;
            $this->markPropertyAsChanged('created_by');
        }
    }

    // BelongsTo relationship: Campaign belongs to Organization
    #[BelongsTo(
        related: Organization::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public ?Organization $organization { get => $this->relations->getRelation('organization'); }

    // BelongsTo relationship: Campaign belongs to Template
    #[BelongsTo(
        related: Template::class,
        foreign_key: 'template_id',
        local_key: 'id'
    )]
    public ?Template $template { get => $this->relations->getRelation('template'); }

    // BelongsTo relationship: Campaign belongs to User (creator)
    #[BelongsTo(
        related: User::class,
        foreign_key: 'created_by',
        local_key: 'id'
    )]
    public ?User $creator { get => $this->relations->getRelation('creator'); }

    // HasMany relationship: Campaign has many Recipients
    #[HasMany(
        related: Recipient::class,
        foreign_key: 'campaign_id',
        local_key: 'id'
    )]
    public array $recipients { get => $this->relations->getRelation('recipients'); }

    // HasMany relationship: Campaign has many QueueJobs
    #[HasMany(
        related: QueueJob::class,
        foreign_key: 'campaign_id',
        local_key: 'id'
    )]
    public array $queueJobs { get => $this->relations->getRelation('queueJobs'); }

    // HasMany relationship: Campaign has many Logs
    #[HasMany(
        related: Log::class,
        foreign_key: 'campaign_id',
        local_key: 'id'
    )]
    public array $logs { get => $this->relations->getRelation('logs'); }

    /**
     * Check if campaign can be started
     */
    public function canStart(): bool
    {
        return in_array($this->status, ['draft', 'scheduled', 'paused']);
    }

    /**
     * Get campaign statistics
     */
    public function getStats(): array
    {
        return [
            'total_recipients' => $this->total_recipients,
            'sent_count' => $this->sent_count,
            'failed_count' => $this->failed_count,
            'open_rate' => $this->calculateOpenRate(),
            'click_rate' => $this->calculateClickRate(),
        ];
    }

    private function calculateOpenRate(): float
    {
        if ($this->sent_count == 0) return 0.0;
        // Count unique opens from logs - placeholder
        return 0.0;
    }

    private function calculateClickRate(): float
    {
        if ($this->sent_count == 0) return 0.0;
        // Count unique clicks from logs - placeholder
        return 0.0;
    }

    public string $created_at {
        get => $this->created_at ?? date('Y-m-d H:i:s');
        set {
            $this->created_at = $value;
            $this->markPropertyAsChanged('created_at');
        }
    }

    public string $updated_at {
        get => $this->updated_at ?? date('Y-m-d H:i:s');
        set {
            $this->updated_at = $value;
            $this->markPropertyAsChanged('updated_at');
        }
    }

    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
