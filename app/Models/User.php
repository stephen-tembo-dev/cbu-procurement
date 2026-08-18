<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements Auditable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function purchaseRequisitions()
    {
        return $this->hasMany(Procurement\PurchaseRequisition::class, 'requester_id');
    }

    public function approvalRecords()
    {
        return $this->hasMany(Procurement\ApprovalRecord::class, 'actor_id');
    }
}
