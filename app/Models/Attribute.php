<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    protected $fillable = [
        'category_id',
        'slug',
        'sort_order',
        'is_spectrum',
        'is_multi_select',
        'default_visible',
        'tier_required',
        'learner_min_band',
    ];

    protected function casts(): array
    {
        return [
            'is_spectrum'      => 'boolean',
            'is_multi_select'  => 'boolean',
            'default_visible'  => 'boolean',
            'learner_min_band' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function labels(): HasMany
    {
        return $this->hasMany(AttributeLabel::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }

    public function attributeIcons(): HasMany
    {
        return $this->hasMany(AttributeIcon::class);
    }
}
