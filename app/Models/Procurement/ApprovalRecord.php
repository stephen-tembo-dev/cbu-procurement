<?php

namespace App\Models\Procurement;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRecord extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'purchase_requisition_id', 'actor_id', 'delegated_by',
        'stage', 'decision', 'comment', 'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'acted_at'   => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    // Non-null when actor was acting as delegate on behalf of this HOD
    public function hodDelegatedBy()
    {
        return $this->belongsTo(User::class, 'delegated_by');
    }

    public function scopeForStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }
}
