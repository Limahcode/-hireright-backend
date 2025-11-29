<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'organization',
        'issue_date',
        'expiration_date',
        'has_expiry',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiration_date' => 'date',
        'has_expiry' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->has_expiry && $this->expiration_date?->isPast();
    }

    public function isActive(): bool
    {
        return !$this->isExpired();
    }
}
