<?php

namespace App\Models\Finance;

use App\Models\CostCentre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class BudgetAllocation extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'cost_centre_id', 'fiscal_year',
        'allocated_amount', 'committed_amount', 'spent_amount',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
            'committed_amount' => 'decimal:2',
            'spent_amount'     => 'decimal:2',
        ];
    }

    public function costCentre()
    {
        return $this->belongsTo(CostCentre::class);
    }

    public function availableBalance(): float
    {
        return (float) ($this->allocated_amount - $this->committed_amount - $this->spent_amount);
    }
}
