<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disputation extends Model
{
    protected $fillable = [
        'user_id',
        'word_sense_id',
        'fields_disputed',
        'rationale',
        'is_anonymous',
        'status',
        'verdict',
        'adjudicator_id',
        'adjudicator_notes',
        'resolved_at',
    ];

    protected $casts = [
        'fields_disputed' => 'array',
        'is_anonymous'    => 'boolean',
        'resolved_at'     => 'datetime',
    ];

    // Status machine
    public const STATUS_PENDING      = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_RESOLVED     = 'resolved';

    public const VERDICT_FULLY_AGREE     = 'fully_agree';
    public const VERDICT_PARTIALLY_AGREE = 'partially_agree';
    public const VERDICT_DISAGREE        = 'disagree';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wordSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class);
    }

    public function adjudicator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjudicator_id');
    }

    /**
     * Display name for this disputation's author, respecting the per-row
     * anonymity snapshot. When anonymous, yields the literal "Anonymous"
     * instead of leaking the author through a join.
     */
    public function displayAuthor(): string
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }
        $u = $this->user;
        if (! $u) {
            return 'Anonymous';
        }
        return $u->chinese_name ?: ($u->name ?: 'Anonymous');
    }
}
