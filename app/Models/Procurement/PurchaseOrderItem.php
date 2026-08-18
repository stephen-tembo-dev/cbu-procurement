<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'description', 'quantity',
        'unit_of_measure', 'unit_price', 'total_price',
        'quantity_received', 'received_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity'          => 'decimal:2',
            'unit_price'        => 'decimal:2',
            'total_price'       => 'decimal:2',
            'quantity_received' => 'decimal:2',
            'received_date'     => 'date',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
