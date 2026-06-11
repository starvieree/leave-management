<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveQuota extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'quota',
        'used'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}