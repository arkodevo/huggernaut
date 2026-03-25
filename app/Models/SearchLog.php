<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SearchLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_id',
        'user_role',
        'search_type',
        'query',
        'results_count',
        'known_count',
        'unknown_count',
        'filters',
    ];

    protected $casts = [
        'filters'    => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notFoundWords(): HasMany
    {
        return $this->hasMany(SearchNotFound::class, 'search_log_id');
    }
}
