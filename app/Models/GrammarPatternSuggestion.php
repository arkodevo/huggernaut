<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrammarPatternSuggestion extends Model
{
    protected $fillable = [
        'pattern_text',
        'chinese_example',
        'shifu_notes',
        'user_id',
        'grammar_pattern_id',
        'status',
        'status_updated_at',
        'reviewed_by',
    ];

    protected $casts = [
        'status_updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(GrammarPattern::class, 'grammar_pattern_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
