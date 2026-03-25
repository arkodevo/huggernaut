<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchNotFound extends Model
{
    public $timestamps = false;

    protected $table = 'search_not_found';

    protected $fillable = [
        'search_log_id',
        'character',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function searchLog(): BelongsTo
    {
        return $this->belongsTo(SearchLog::class);
    }
}
