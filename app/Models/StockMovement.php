<?php

namespace App\Models;

use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequisition;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'stock_item_id', 'performed_by', 'purchase_requisition_id', 'purchase_order_id',
        'movement_type', 'quantity_in', 'quantity_out', 'balance_after', 'occurred_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_in' => 'decimal:2',
            'quantity_out' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
