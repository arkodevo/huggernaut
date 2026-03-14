<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name_en',
        'name_native',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function posLabelTranslations(): HasMany
    {
        return $this->hasMany(PosLabelTranslation::class);
    }

    public function categoryLabels(): HasMany
    {
        return $this->hasMany(CategoryLabel::class);
    }

    public function attributeLabels(): HasMany
    {
        return $this->hasMany(AttributeLabel::class);
    }

    public function designationLabels(): HasMany
    {
        return $this->hasMany(DesignationLabel::class);
    }

    public function senseRelationTypeLabels(): HasMany
    {
        return $this->hasMany(SenseRelationTypeLabel::class);
    }

    public function iconThemeLabels(): HasMany
    {
        return $this->hasMany(IconThemeLabel::class);
    }

    public function wordSenseDefinitions(): HasMany
    {
        return $this->hasMany(WordSenseDefinition::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'ui_language_id');
    }
}
