<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoPersonTag extends Model
{
    public const UPDATED_AT = null;

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_APPROVED => 'Freigegeben',
        self::STATUS_PENDING => 'Ausstehend',
        self::STATUS_REJECTED => 'Abgelehnt',
    ];

    protected $fillable = [
        'photo_id', 'person_id', 'x_percent', 'y_percent', 'note',
        'status', 'suggested_by', 'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'x_percent' => 'decimal:2',
            'y_percent' => 'decimal:2',
        ];
    }

    protected $attributes = [
        'status' => self::STATUS_APPROVED,
    ];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function suggestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suggested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
