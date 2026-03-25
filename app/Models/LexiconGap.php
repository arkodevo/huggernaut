<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LexiconGap extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'character',
        'status',
        'status_updated_at',
    ];

    protected $casts = [
        'created_at'        => 'datetime',
        'status_updated_at' => 'datetime',
    ];
}
