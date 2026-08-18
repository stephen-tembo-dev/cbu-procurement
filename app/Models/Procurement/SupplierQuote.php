<?php

namespace App\Models\Procurement;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id', 'supplier_id', 'amount',
        'received_date', 'supplier_contacted',
        'contact_evidence_path', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'             => 'decimal:2',
            'received_date'      => 'date',
            'supplier_contacted' => 'boolean',
        ];
    }

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function hasResponse(): bool
    {
        return ! is_null($this->amount);
    }
}
