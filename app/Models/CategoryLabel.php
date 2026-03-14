<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Composite PK: (category_id, language_id). Use updateOrCreate() for upserts.
class CategoryLabel extends Model
{
    protected $table = 'category_labels';

    public $incrementing = false;

    protected $fillable = [
        'category_id',
        'language_id',
        'label',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
