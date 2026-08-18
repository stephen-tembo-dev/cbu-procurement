<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Supplier extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'name', 'contact_person', 'contact_email',
        'contact_phone', 'zppa_registration', 'zppa_approved', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'zppa_approved' => 'boolean',
            'is_active'     => 'boolean',
        ];
    }

    public function quotes()
    {
        return $this->hasMany(Procurement\SupplierQuote::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(Procurement\PurchaseOrder::class);
    }
}
