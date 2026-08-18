<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class CostCentre extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $fillable = ['code', 'name', 'department_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function purchaseRequisitions()
    {
        return $this->hasMany(Procurement\PurchaseRequisition::class);
    }

    public function budgetAllocations()
    {
        return $this->hasMany(Finance\BudgetAllocation::class);
    }

    public function currentBudget(): ?Finance\BudgetAllocation
    {
        return $this->budgetAllocations()
            ->where('fiscal_year', now()->year)
            ->first();
    }
}
