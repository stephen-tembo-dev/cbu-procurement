<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOfMeasure extends Model
{
    use HasFactory;

    protected $table = 'units_of_measure';

    protected $fillable = ['name', 'abbreviation', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('name');
    }

    /** Label shown in dropdowns, e.g. "Pieces (pcs)" */
    public function label(): string
    {
        return "{$this->name} ({$this->abbreviation})";
    }
}
