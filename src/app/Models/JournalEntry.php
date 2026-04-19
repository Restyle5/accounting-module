<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'reference',
        'description',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    // auto-add current user as created_by user.
    protected static function boot()
    {
        parent::boot();
        
        // append on create request
        static::creating(function ($model) {
            if (empty($model->user_id) && Auth::check()) {
                $model->created_by = Auth::id();
            }
        });
    }

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
