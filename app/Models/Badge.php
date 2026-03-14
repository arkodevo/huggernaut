<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'trigger_type',
        'threshold',
        'action_type',
        'bonus_credits',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'threshold'     => 'integer',
        'bonus_credits' => 'integer',
        'sort_order'    => 'integer',
        'is_active'     => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function earnedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
                    ->withPivot(['earned_at', 'notified_at'])
                    ->withTimestamps();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Whether this badge can be triggered automatically (not manual). */
    public function isAutomatic(): bool
    {
        return $this->trigger_type !== 'manual';
    }
}
