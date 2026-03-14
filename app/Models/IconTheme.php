<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// null user_id = system theme; non-null = user-forked custom theme.
// source_theme_id records which system theme was forked.
class IconTheme extends Model
{
    protected $fillable = [
        'user_id',
        'source_theme_id',
        'slug',
        'name',
        'description',
        'icon_type',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceTheme(): BelongsTo
    {
        return $this->belongsTo(IconTheme::class, 'source_theme_id');
    }

    public function forks(): HasMany
    {
        return $this->hasMany(IconTheme::class, 'source_theme_id');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(IconThemeLabel::class);
    }

    public function designationIcons(): HasMany
    {
        return $this->hasMany(DesignationIcon::class);
    }

    public function attributeIcons(): HasMany
    {
        return $this->hasMany(AttributeIcon::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'icon_theme_id');
    }

    public function scopeSystem($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
