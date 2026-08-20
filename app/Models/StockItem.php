<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class StockItem extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'stock_code', 'description', 'category',
        'is_stocked', 'quantity_on_hand', 'reorder_level',
        'unit_of_measure', 'is_active',
        'is_recurring', 'reorder_frequency_days', 'last_reorder_date', 'next_reorder_date',
    ];

    protected function casts(): array
    {
        return [
            'is_stocked'            => 'boolean',
            'is_active'             => 'boolean',
            'is_recurring'          => 'boolean',
            'quantity_on_hand'      => 'decimal:2',
            'reorder_level'         => 'decimal:2',
            'last_reorder_date'     => 'date',
            'next_reorder_date'     => 'date',
        ];
    }

    public function purchaseRequisitionItems()
    {
        return $this->hasMany(Procurement\PurchaseRequisitionItem::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class)->latest('occurred_at');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeStocked($query)
    {
        return $query->where('is_stocked', true)->where('is_active', true);
    }

    public function scopeOrderable($query)
    {
        return $query->where('is_stocked', false)->where('is_active', true);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true)->where('is_active', true);
    }

    public function scopeDueForReorder($query)
    {
        return $query->where('is_recurring', true)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('next_reorder_date')
                  ->orWhere('next_reorder_date', '<=', now()->toDateString());
            });
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isDueForReorder(): bool
    {
        if (! $this->is_recurring) {
            return false;
        }

        return is_null($this->next_reorder_date)
            || $this->next_reorder_date->lte(now());
    }

    public function computeNextReorderDate(): ?\Carbon\Carbon
    {
        if (! $this->reorder_frequency_days) {
            return null;
        }

        $base = $this->last_reorder_date ?? now();

        return $base->addDays($this->reorder_frequency_days);
    }
}
