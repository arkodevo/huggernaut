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
        'source',
        'user_id',
        'collection_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function searchLog(): BelongsTo
    {
        return $this->belongsTo(SearchLog::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
