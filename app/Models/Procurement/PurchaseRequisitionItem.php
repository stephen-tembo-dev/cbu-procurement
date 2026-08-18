<?php

namespace App\Models\Procurement;

use App\Models\StockItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id', 'stock_item_id', 'description',
        'category', 'quantity', 'quantity_issued', 'unit_of_measure',
        'unit_price_estimated', 'total_price_estimated',
    ];

    protected function casts(): array
    {
        return [
            'quantity'              => 'decimal:2',
            'quantity_issued'       => 'decimal:2',
            'unit_price_estimated'  => 'decimal:2',
            'total_price_estimated' => 'decimal:2',
        ];
    }

    public function isFullyIssued(): bool
    {
        return (float) ($this->quantity_issued ?? 0) >= (float) $this->quantity;
    }

    public function unfulfilledQuantity(): float
    {
        return max(0.0, (float) $this->quantity - (float) ($this->quantity_issued ?? 0));
    }

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }
}
