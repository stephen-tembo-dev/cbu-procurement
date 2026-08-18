<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Department extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $fillable = ['name', 'code', 'hod_user_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function hod()
    {
        return $this->belongsTo(User::class, 'hod_user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function costCentres()
    {
        return $this->hasMany(CostCentre::class);
    }

    public function purchaseRequisitions()
    {
        return $this->hasMany(Procurement\PurchaseRequisition::class);
    }
}
