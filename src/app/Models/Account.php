<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // public function journalLines()
    // {
    //     return $this->hasMany(JournalLine::class);
    // }

    // Scope for active accounts only
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
