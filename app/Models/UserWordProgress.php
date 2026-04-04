<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWordProgress extends Model
{
    protected $table = 'user_word_progress';

    public $incrementing = false;

    // Composite primary key — use DB::table() for updateOrCreate instead of Eloquent
    protected $primaryKey = null;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'word_object_id',
        'pinyin_passed',
        'definition_passed',
        'usage_passed',
        'pinyin_passed_at',
        'definition_passed_at',
        'usage_passed_at',
    ];

    /**
     * Safe updateOrCreate for composite key — Eloquent's built-in version
     * generates UPDATE without WHERE when primaryKey is null.
     */
    public static function safeUpsert(int $userId, int $wordObjectId, array $values): void
    {
        $existing = static::where('user_id', $userId)
            ->where('word_object_id', $wordObjectId)
            ->first();

        if ($existing) {
            static::where('user_id', $userId)
                ->where('word_object_id', $wordObjectId)
                ->update($values);
        } else {
            static::create(array_merge(
                ['user_id' => $userId, 'word_object_id' => $wordObjectId],
                $values
            ));
        }
    }

    protected $casts = [
        'pinyin_passed'       => 'boolean',
        'definition_passed'   => 'boolean',
        'usage_passed'        => 'boolean',
        'pinyin_passed_at'    => 'datetime',
        'definition_passed_at' => 'datetime',
        'usage_passed_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wordObject(): BelongsTo
    {
        return $this->belongsTo(WordObject::class);
    }

    /**
     * Count how many of the 3 tests have been passed.
     */
    public function passedCount(): int
    {
        return ($this->pinyin_passed ? 1 : 0)
             + ($this->definition_passed ? 1 : 0)
             + ($this->usage_passed ? 1 : 0);
    }
}
