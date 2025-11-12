<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Clock\Contracts\Clock;
use Larafony\Framework\Database\ORM\Attributes\BelongsTo;
use Larafony\Framework\Database\ORM\Attributes\HasMany;
use Larafony\Framework\Database\ORM\Model;

class Template extends Model
{
    public string $table { get => 'templates'; }

    public array $fillable = [
        'organization_id', 'name', 'subject', 'html_content', 'variables', 'is_active'
    ];

    public ?int $organization_id {
        get => $this->organization_id ?? null;
        set {
            $this->organization_id = $value;
            $this->markPropertyAsChanged('organization_id');
        }
    }

    public ?string $name {
        get => $this->name ?? null;
        set {
            $this->name = $value;
            $this->markPropertyAsChanged('name');
        }
    }

    public ?string $subject {
        get => $this->subject ?? null;
        set {
            $this->subject = $value;
            $this->markPropertyAsChanged('subject');
        }
    }

    public ?string $html_content {
        get => $this->html_content ?? null;
        set {
            $this->html_content = $value;
            $this->markPropertyAsChanged('html_content');
        }
    }

    public ?string $variables {
        get => $this->variables ?? null;
        set {
            $this->variables = $value;
            $this->markPropertyAsChanged('variables');
        }
    }

    public ?bool $is_active {
        get => $this->is_active ?? true;
        set {
            $this->is_active = $value;
            $this->markPropertyAsChanged('is_active');
        }
    }

    #[BelongsTo(
        related: Organization::class,
        foreign_key: 'organization_id',
        local_key: 'id'
    )]
    public ?Organization $organization { get => $this->relations->getRelation('organization'); }

    #[HasMany(
        related: Campaign::class,
        foreign_key: 'template_id',
        local_key: 'id'
    )]
    public array $campaigns { get => $this->relations->getRelation('campaigns'); }

    public function parseVariables(): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $this->html_content, $matches);
        return array_unique($matches[1]);
    }

    public function render(array $data = []): string
    {
        $content = $this->html_content;
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }

    public function renderSubject(array $data = []): string
    {
        $subject = $this->subject;
        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }
        return $subject;
    }

    public Clock $created_at {
        get => $this->created_at;
        set {
            $this->created_at = $value;
            $this->markPropertyAsChanged('created_at');
        }
    }

    public Clock $updated_at {
        get => $this->updated_at;
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
