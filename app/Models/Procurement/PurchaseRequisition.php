<?php

namespace App\Models\Procurement;

use App\Models\CostCentre;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PurchaseRequisition extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'reference_no', 'type', 'requester_id', 'department_id',
        'cost_centre_id', 'status', 'service_consumed_flag', 'notes',
    ];

    protected function casts(): array
    {
        return ['service_consumed_flag' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (PurchaseRequisition $pr) {
            $pr->reference_no = 'PR-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        });
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function costCentre()
    {
        return $this->belongsTo(CostCentre::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function approvalRecords()
    {
        return $this->hasMany(ApprovalRecord::class)->orderBy('created_at');
    }

    public function supplierQuotes()
    {
        return $this->hasMany(SupplierQuote::class);
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function totalEstimated(): float
    {
        return (float) $this->items->sum('total_price_estimated');
    }

    /**
     * Estimated value of items not yet fully issued from stores.
     * Used by Bursar to commit only what needs external procurement.
     */
    public function unfulfilledValue(): float
    {
        return (float) $this->items->sum(function ($item) {
            $remaining = max(0.0, (float) $item->quantity - (float) ($item->quantity_issued ?? 0));
            return $remaining * (float) $item->unit_price_estimated;
        });
    }

    public function unfulfilledItems()
    {
        return $this->items->filter(fn ($item) => ! $item->isFullyIssued())->values();
    }

    public function requiresThreeQuotes(): bool
    {
        return $this->totalEstimated() >= config('procurement.quote_threshold', 10000);
    }

    public function latestApproval(string $stage): ?ApprovalRecord
    {
        return $this->approvalRecords->where('stage', $stage)->last();
    }
}
