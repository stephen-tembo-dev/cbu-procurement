<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HodDelegation extends Model
{
    protected $fillable = [
        'department_id',
        'hod_user_id',
        'delegate_user_id',
        'reason',
        'starts_at',
        'ends_at',
        'is_active',
        'revoked_at',
        'revoked_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'  => 'date',
            'ends_at'    => 'date',
            'is_active'  => 'boolean',
            'revoked_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function hod()
    {
        return $this->belongsTo(User::class, 'hod_user_id');
    }

    public function delegate()
    {
        return $this->belongsTo(User::class, 'delegate_user_id');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Revoked';
        }
        if ($this->ends_at->isPast()) {
            return 'Expired';
        }
        if ($this->starts_at->isFuture()) {
            return 'Scheduled';
        }

        return 'Active';
    }

    public function revoke(int $revokedByUserId): void
    {
        $this->update([
            'is_active'  => false,
            'revoked_at' => now(),
            'revoked_by' => $revokedByUserId,
        ]);
    }
}
