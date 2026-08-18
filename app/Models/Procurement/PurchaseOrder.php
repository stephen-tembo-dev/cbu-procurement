<?php

namespace App\Models\Procurement;

use App\Models\Finance\Payment;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PurchaseOrder extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'po_number', 'purchase_requisition_id', 'supplier_id',
        'total_value', 'status', 'issued_date',
        'expected_delivery_date', 'actual_delivery_date',
    ];

    protected function casts(): array
    {
        return [
            'total_value'            => 'decimal:2',
            'issued_date'            => 'date',
            'expected_delivery_date' => 'date',
            'actual_delivery_date'   => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $po) {
            $po->po_number = 'PO-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        });
    }

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
