<?php

declare(strict_types=1);

namespace App\Models;

use Larafony\Framework\Database\ORM\Model;

class Audience extends Model
{
    public string $table {
        get => 'audiences';
    }
}
