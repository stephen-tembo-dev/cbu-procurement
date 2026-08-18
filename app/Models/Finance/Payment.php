<?php

namespace App\Models\Finance;

use App\Models\Procurement\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Payment extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'purchase_order_id', 'amount', 'status',
        'vc_approved_by', 'vc_approved_at',
        'bursar_confirmed_by', 'bursar_confirmed_at',
        'paid_at', 'delay_comment',
    ];

    protected function casts(): array
    {
        return [
            'amount'              => 'decimal:2',
            'vc_approved_at'      => 'datetime',
            'bursar_confirmed_at' => 'datetime',
            'paid_at'             => 'datetime',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function vcApprover()
    {
        return $this->belongsTo(User::class, 'vc_approved_by');
    }

    public function bursarConfirmer()
    {
        return $this->belongsTo(User::class, 'bursar_confirmed_by');
    }
}
