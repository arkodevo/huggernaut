<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShifuInteraction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'engagement_id',
        'sequence',
        'learner_input',
        'shifu_response',
        'is_correct',
        'hints_used',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'hints_used' => 'array',
        'created_at' => 'datetime',
    ];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(ShifuEngagement::class, 'engagement_id');
    }
}
