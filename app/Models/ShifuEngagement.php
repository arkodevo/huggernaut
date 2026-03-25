<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ShifuEngagement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'user_id',
        'word_sense_id',
        'word_object_id',
        'context',
        'word_label',
        'outcome',
        'interaction_count',
        'started_at',
        'completed_at',
        'audit_grade',
        'audit_feedback',
        'audit_reviewed_at',
    ];

    protected $casts = [
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'audit_reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /* ── Relationships ── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wordSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class);
    }

    public function wordObject(): BelongsTo
    {
        return $this->belongsTo(WordObject::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(ShifuInteraction::class, 'engagement_id')->orderBy('sequence');
    }

    /* ── Helpers ── */

    public function addInteraction(string $learnerInput, string $shifuResponse, ?bool $isCorrect = null, ?array $hintsUsed = null): ShifuInteraction
    {
        $this->increment('interaction_count');

        return $this->interactions()->create([
            'sequence'       => $this->interaction_count,
            'learner_input'  => $learnerInput,
            'shifu_response' => $shifuResponse,
            'is_correct'     => $isCorrect,
            'hints_used'     => $hintsUsed,
        ]);
    }

    public function complete(string $outcome): void
    {
        $this->update([
            'outcome'      => $outcome,
            'completed_at' => now(),
        ]);
    }
}
