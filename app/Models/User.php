<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// TODO Phase 3: composer require laravel/cashier, then uncomment:
// use Laravel\Cashier\Billable;

// role: learner · user (staff) · editor · admin  (editor/admin gates admin panel access)
// subscription_tier: free · entry · mid · pro  (cached/derived alongside Cashier)
// level_framework: tocfl · hsk  (controls which Level filter chips appear)
// script_preference: traditional · simplified · both
// pos_mode: simplified · standard · full
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    // use Billable; // TODO Phase 3

    protected $fillable = [
        'name',
        'pll_name',
        'chinese_name',
        'chinese_name_pinyin',
        'chinese_name_meaning',
        'email',
        'password',
        'role',
        'chinese_font',
        'ui_language_id',
        'script_preference',
        'pos_mode',
        'level_framework',
        'fluency_level',
        'shifu_persona',
        'filter_attribute_overrides',
        'ui_preferences',
        'subscription_tier',
        'ai_credits_remaining',
        'ai_credits_reset_at',
        'points_balance',
        'points_total_earned',
        'icon_theme_id',
        // Cashier columns (Phase 3):
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'ai_credits_reset_at'  => 'datetime',
            'trial_ends_at'        => 'datetime',
            'points_balance'              => 'integer',
            'points_total_earned'         => 'integer',
            'filter_attribute_overrides'  => 'array',
            'ui_preferences'              => 'array',
        ];
    }

    // ── Preferences ───────────────────────────────────────────────────────────

    public function uiLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'ui_language_id');
    }

    public function iconTheme(): BelongsTo
    {
        return $this->belongsTo(IconTheme::class, 'icon_theme_id');
    }

    // ── Icon themes forked by this user ───────────────────────────────────────

    public function customIconThemes(): HasMany
    {
        return $this->hasMany(IconTheme::class);
    }

    // ── Saved content ─────────────────────────────────────────────────────────

    public function savedWords(): HasMany
    {
        return $this->hasMany(UserSavedWord::class);
    }

    public function savedWordObjects(): BelongsToMany
    {
        return $this->belongsToMany(WordObject::class, 'user_saved_words')
            ->withPivot('personal_note', 'saved_at')
            ->withTimestamps();
    }

    public function savedExamples(): HasMany
    {
        return $this->hasMany(UserSavedExample::class);
    }

    // ── Learning progress ────────────────────────────────────────────────────

    public function wordProgress(): HasMany
    {
        return $this->hasMany(UserWordProgress::class);
    }

    // ── Collections ───────────────────────────────────────────────────────────

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    // ── Contributed examples ──────────────────────────────────────────────────

    public function contributedExamples(): HasMany
    {
        return $this->hasMany(WordSenseExample::class);
    }

    // ── AI ────────────────────────────────────────────────────────────────────

    public function aiUsageLogs(): HasMany
    {
        return $this->hasMany(AiUsageLog::class);
    }

    // ── Cashier (Phase 3) ─────────────────────────────────────────────────────
    // Subscription and SubscriptionItem models are provided by laravel/cashier.
    // The Billable trait (commented above) adds subscriptions(), subscription(),
    // newSubscription(), etc. automatically.

    // ── Points & Badges ───────────────────────────────────────────────────────

    public function pointEvents(): HasMany
    {
        return $this->hasMany(PointEvent::class);
    }

    public function earnedBadges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
                    ->using(UserBadge::class)
                    ->withPivot(['earned_at', 'notified_at']);
    }

    /**
     * Award points for a named action and record a PointEvent.
     * Also updates points_total_earned (lifetime counter for levels/badges).
     * After awarding, checks for newly unlocked badges.
     */
    public function awardPoints(string $eventType, ?Model $subject = null, array $meta = []): PointEvent
    {
        $amount = (int) config("points.earn.{$eventType}", 0);

        $this->increment('points_balance', $amount);
        $this->increment('points_total_earned', $amount);
        $this->refresh();

        $event = $this->pointEvents()->create([
            'event_type'   => $eventType,
            'points'       => $amount,
            'balance_after' => $this->points_balance,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'meta'         => $meta ?: null,
        ]);

        $this->checkBadgeUnlocks($eventType);

        return $event;
    }

    /**
     * Spend points for a named action. Returns false if the balance is insufficient.
     */
    public function spendPoints(string $eventType, int $amount, ?Model $subject = null): PointEvent|false
    {
        if ($this->points_balance < $amount) {
            return false;
        }

        $this->decrement('points_balance', $amount);
        $this->refresh();

        return $this->pointEvents()->create([
            'event_type'    => $eventType,
            'points'        => -$amount,
            'balance_after' => $this->points_balance,
            'subject_type'  => $subject ? get_class($subject) : null,
            'subject_id'    => $subject?->getKey(),
        ]);
    }

    /**
     * Convert N points into AI credits (1:1 by default, rate in config).
     */
    public function convertPointsToAiCredits(int $points): bool
    {
        $result = $this->spendPoints('points_to_ai_credit', $points);

        if ($result === false) {
            return false;
        }

        $this->increment('ai_credits_remaining', $points);

        return true;
    }

    /**
     * Return the current level array from config/levels.php based on lifetime points.
     */
    public function currentLevel(): array
    {
        $levels   = config('levels');
        $earned   = $this->points_total_earned ?? 0;
        $current  = $levels[0];

        foreach ($levels as $level) {
            if ($earned >= $level['min_points']) {
                $current = $level;
            }
        }

        return $current;
    }

    /**
     * Check if any automatic badges have just been unlocked and award them.
     * Called after every awardPoints() call.
     */
    public function checkBadgeUnlocks(string $eventType = ''): void
    {
        $alreadyEarned = $this->earnedBadges()->pluck('badges.id');

        Badge::active()
            ->whereNotIn('id', $alreadyEarned)
            ->get()
            ->each(function (Badge $badge) use ($eventType) {
                $unlock = match ($badge->trigger_type) {
                    'points_total' => $this->points_total_earned >= $badge->threshold,
                    'action_count' => $badge->action_type && $this->pointEvents()
                        ->where('event_type', $badge->action_type)
                        ->earned()
                        ->count() >= $badge->threshold,
                    default => false,
                };

                if ($unlock) {
                    $this->earnedBadges()->attach($badge->id, ['earned_at' => now()]);

                    if ($badge->bonus_credits > 0) {
                        $this->increment('ai_credits_remaining', $badge->bonus_credits);
                    }
                }
            });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEditor(): bool
    {
        return in_array($this->role, ['admin', 'editor']);
    }
}
