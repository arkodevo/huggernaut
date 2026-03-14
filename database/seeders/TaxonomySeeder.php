<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeLabel;
use App\Models\Category;
use App\Models\CategoryLabel;
use App\Models\Designation;
use App\Models\DesignationLabel;
use App\Models\Language;
use Illuminate\Database\Seeder;

// Seeds the full attribute taxonomy:
//   Category → Attribute → Designation
// with English and Traditional Chinese labels on every node.
//
// TOCFL level attribute → 6 designations (tocfl-prep … tocfl-fluency)
// HSK level attribute   → 6 designations (hsk-1 … hsk-6)
// Both frameworks always stored; users.level_framework routes which filter shows.
//
// intensity attribute has NO designations — its value is a scalar (1–5 tinyint)
// stored directly on word_senses, driving the 🌸→🌺 flower display.
class TaxonomySeeder extends Seeder
{
    private int $en;
    private int $zhTW;

    public function run(): void
    {
        $this->en   = Language::where('code', 'en')->value('id');
        $this->zhTW = Language::where('code', 'zh-TW')->value('id');

        // ── Categories ──────────────────────────────────────────────────────────

        $categories = [
            [
                'slug'       => 'linguistic-identity',
                'sort_order' => 1,
                'en'         => 'Linguistic Identity',
                'zh'         => '語言特性',
            ],
            [
                'slug'       => 'usage-context',
                'sort_order' => 2,
                'en'         => 'Usage Context',
                'zh'         => '使用情境',
            ],
            [
                'slug'       => 'expressive-quality',
                'sort_order' => 3,
                'en'         => 'Expressive Quality',
                'zh'         => '表達特質',
            ],
            [
                'slug'       => 'pedagogical',
                'sort_order' => 4,
                'en'         => 'Pedagogical',
                'zh'         => '學習指標',
            ],
        ];

        foreach ($categories as $catData) {
            $cat = Category::firstOrCreate(
                ['slug' => $catData['slug']],
                ['sort_order' => $catData['sort_order']]
            );
            $this->categoryLabel($cat->id, $catData['en'], $catData['zh']);
        }

        // ── Attributes ──────────────────────────────────────────────────────────
        // Each entry maps to an attribute row and then its designations below.

        $catId = fn (string $slug) => Category::where('slug', $slug)->value('id');

        $attributes = [

            // ── linguistic-identity ────────────────────────────────────────────

            [
                'category'       => 'linguistic-identity',
                'slug'           => 'channel',
                'sort_order'     => 1,
                'is_spectrum'    => true,
                'is_multi_select'=> false,
                'default_visible'=> true,
                'en'             => 'Channel',
                'zh'             => '媒介',
                'designations'   => [
                    // spoken ←→ written spectrum; creature icons 🦎→🐉
                    ['slug' => 'spoken-only',      'sort_order' => 1, 'en' => 'Spoken Only',      'zh' => '純口語'],
                    ['slug' => 'spoken-dominant',  'sort_order' => 2, 'en' => 'Spoken-Dominant',  'zh' => '偏口語'],
                    ['slug' => 'fluid',            'sort_order' => 3, 'en' => 'Fluid',            'zh' => '口筆皆宜'],
                    ['slug' => 'written-dominant', 'sort_order' => 4, 'en' => 'Written-Dominant', 'zh' => '偏書面'],
                    ['slug' => 'written-only',     'sort_order' => 5, 'en' => 'Written Only',     'zh' => '純書面'],
                ],
            ],
            [
                'category'       => 'linguistic-identity',
                'slug'           => 'register',
                'sort_order'     => 2,
                'is_spectrum'    => false,
                'is_multi_select'=> true,
                'default_visible'=> true,
                'en'             => 'Register',
                'zh'             => '語域',
                'designations'   => [
                    // multi-select; insect icons 🦋→🕷️
                    ['slug' => 'literary',   'sort_order' => 1, 'en' => 'Literary',   'zh' => '文學'],
                    ['slug' => 'formal',     'sort_order' => 2, 'en' => 'Formal',     'zh' => '正式'],
                    ['slug' => 'standard',   'sort_order' => 3, 'en' => 'Standard',   'zh' => '標準'],
                    ['slug' => 'informal',   'sort_order' => 4, 'en' => 'Informal',   'zh' => '非正式'],
                    ['slug' => 'colloquial', 'sort_order' => 5, 'en' => 'Colloquial', 'zh' => '口語'],
                    ['slug' => 'slang',      'sort_order' => 6, 'en' => 'Slang',      'zh' => '俚語'],
                ],
            ],

            // ── usage-context ──────────────────────────────────────────────────

            [
                'category'       => 'usage-context',
                'slug'           => 'domain',
                'sort_order'     => 1,
                'is_spectrum'    => false,
                'is_multi_select'=> false,
                'default_visible'=> true,
                'en'             => 'Domain',
                'zh'             => '領域',
                'designations'   => [
                    // TOCFL task domains
                    ['slug' => 'personal',      'sort_order' => 1,  'en' => 'Personal',         'zh' => '個人資料'],
                    ['slug' => 'education',     'sort_order' => 2,  'en' => 'Education',        'zh' => '教育'],
                    ['slug' => 'travel',        'sort_order' => 3,  'en' => 'Travel',           'zh' => '旅行'],
                    ['slug' => 'food',          'sort_order' => 4,  'en' => 'Food & Drink',     'zh' => '飲食'],
                    ['slug' => 'shopping',      'sort_order' => 5,  'en' => 'Shopping',         'zh' => '購物'],
                    ['slug' => 'health',        'sort_order' => 6,  'en' => 'Health',           'zh' => '健康'],
                    ['slug' => 'family',        'sort_order' => 7,  'en' => 'Family',           'zh' => '家庭'],
                    ['slug' => 'entertainment', 'sort_order' => 8,  'en' => 'Entertainment',    'zh' => '娛樂'],
                    ['slug' => 'work',          'sort_order' => 9,  'en' => 'Work',             'zh' => '工作'],
                    ['slug' => 'environment',   'sort_order' => 10, 'en' => 'Environment',      'zh' => '環境'],
                ],
            ],
            [
                'category'       => 'usage-context',
                'slug'           => 'sensitivity',
                'sort_order'     => 2,
                'is_spectrum'    => false,
                'is_multi_select'=> false,
                'default_visible'=> false,  // hidden by default in filter bar
                'en'             => 'Sensitivity',
                'zh'             => '敏感度',
                'designations'   => [
                    ['slug' => 'general',   'sort_order' => 1, 'en' => 'General',   'zh' => '一般'],
                    ['slug' => 'mature',    'sort_order' => 2, 'en' => 'Mature',    'zh' => '成人'],
                    ['slug' => 'profanity', 'sort_order' => 3, 'en' => 'Profanity', 'zh' => '粗口'],
                    ['slug' => 'sexual',    'sort_order' => 4, 'en' => 'Sexual',    'zh' => '性相關'],
                    ['slug' => 'taboo',     'sort_order' => 5, 'en' => 'Taboo',     'zh' => '禁忌'],
                ],
            ],

            // ── expressive-quality ─────────────────────────────────────────────

            [
                'category'       => 'expressive-quality',
                'slug'           => 'connotation',
                'sort_order'     => 1,
                'is_spectrum'    => true,
                'is_multi_select'=> false,
                'default_visible'=> true,
                'en'             => 'Connotation',
                'zh'             => '感情色彩',
                'designations'   => [
                    // positive ←→ negative spectrum; weather icons ☀️→⛈️
                    ['slug' => 'positive',           'sort_order' => 1, 'en' => 'Positive',           'zh' => '正面'],
                    ['slug' => 'positive-dominant',  'sort_order' => 2, 'en' => 'Mostly Positive',    'zh' => '偏正面'],
                    ['slug' => 'context-dependent',  'sort_order' => 3, 'en' => 'Context-Dependent',  'zh' => '依語境'],
                    ['slug' => 'negative-dominant',  'sort_order' => 4, 'en' => 'Mostly Negative',    'zh' => '偏負面'],
                    ['slug' => 'negative',           'sort_order' => 5, 'en' => 'Negative',           'zh' => '負面'],
                ],
            ],
            [
                'category'       => 'expressive-quality',
                'slug'           => 'semantic-mode',
                'sort_order'     => 2,
                'is_spectrum'    => true,
                'is_multi_select'=> false,
                'default_visible'=> true,
                'en'             => 'Semantic Mode',
                'zh'             => '語義模式',
                'designations'   => [
                    // literal ←→ metaphorical spectrum
                    // 'balanced' avoids slug collision with channel's 'fluid'
                    ['slug' => 'literal-only',          'sort_order' => 1, 'en' => 'Literal Only',      'zh' => '純字面'],
                    ['slug' => 'literal-dominant',      'sort_order' => 2, 'en' => 'Mostly Literal',    'zh' => '偏字面'],
                    ['slug' => 'balanced',              'sort_order' => 3, 'en' => 'Balanced',          'zh' => '字面比喻皆宜'],
                    ['slug' => 'metaphorical-dominant', 'sort_order' => 4, 'en' => 'Mostly Metaphorical','zh' => '偏比喻'],
                    ['slug' => 'metaphorical-only',     'sort_order' => 5, 'en' => 'Metaphorical Only', 'zh' => '純比喻'],
                ],
            ],
            [
                'category'       => 'expressive-quality',
                'slug'           => 'intensity',
                'sort_order'     => 3,
                'is_spectrum'    => true,
                'is_multi_select'=> false,
                'default_visible'=> true,
                'en'             => 'Intensity',
                'zh'             => '強度',
                'designations'   => [],
                // Intensity is a scalar (1–5) stored as a tinyint on word_senses.
                // NO designation rows — the flower icons 🌸→🌺 are driven by
                // the numeric value directly in the UI. The attribute row here
                // exists only to power the attribute label and header icon.
            ],

            // ── pedagogical ────────────────────────────────────────────────────

            [
                'category'       => 'pedagogical',
                'slug'           => 'dimension',
                'sort_order'     => 1,
                'is_spectrum'    => false,
                'is_multi_select'=> true,
                'default_visible'=> true,
                'en'             => 'Dimension',
                'zh'             => '維度',
                'designations'   => [
                    // multi-select usage dimensions; sea creature icons 🐢🐙🦀🐟🦂
                    ['slug' => 'temporal',    'sort_order' => 1, 'en' => 'Temporal',    'zh' => '時間性'],
                    ['slug' => 'aspectual',   'sort_order' => 2, 'en' => 'Aspectual',   'zh' => '體態性'],
                    ['slug' => 'resultative', 'sort_order' => 3, 'en' => 'Resultative', 'zh' => '結果性'],
                    ['slug' => 'pragmatic',   'sort_order' => 4, 'en' => 'Pragmatic',   'zh' => '語用性'],
                    ['slug' => 'figurative',  'sort_order' => 5, 'en' => 'Figurative',  'zh' => '修辭性'],
                ],
            ],
            [
                'category'       => 'pedagogical',
                'slug'           => 'tocfl-level',
                'sort_order'     => 2,
                'is_spectrum'    => true,
                'is_multi_select'=> false,
                'default_visible'=> true,
                'en'             => 'TOCFL Level',
                'zh'             => '華語文能力測驗',
                'designations'   => [
                    // 6 levels; moon-phase icons 🌑→🌝
                    // Designation IDs are stored in word_senses.tocfl_level_id
                    ['slug' => 'tocfl-prep',     'sort_order' => 1, 'en' => 'Prep',     'zh' => '預備級'],
                    ['slug' => 'tocfl-entry',    'sort_order' => 2, 'en' => 'Entry',    'zh' => '入門級'],
                    ['slug' => 'tocfl-basic',    'sort_order' => 3, 'en' => 'Basic',    'zh' => '基礎級'],
                    ['slug' => 'tocfl-advanced', 'sort_order' => 4, 'en' => 'Advanced', 'zh' => '進階級'],
                    ['slug' => 'tocfl-high',     'sort_order' => 5, 'en' => 'High',     'zh' => '高階級'],
                    ['slug' => 'tocfl-fluency',  'sort_order' => 6, 'en' => 'Fluency',  'zh' => '流利級'],
                ],
            ],
            [
                'category'       => 'pedagogical',
                'slug'           => 'hsk-level',
                'sort_order'     => 3,
                'is_spectrum'    => true,
                'is_multi_select'=> false,
                'default_visible'=> true,
                'en'             => 'HSK Level',
                'zh'             => '漢語水平考試',
                'designations'   => [
                    // 6 levels; growth icons 🌰→🎋
                    // Designation IDs are stored in word_senses.hsk_level_id
                    ['slug' => 'hsk-1', 'sort_order' => 1, 'en' => 'HSK 1', 'zh' => '一級'],
                    ['slug' => 'hsk-2', 'sort_order' => 2, 'en' => 'HSK 2', 'zh' => '二級'],
                    ['slug' => 'hsk-3', 'sort_order' => 3, 'en' => 'HSK 3', 'zh' => '三級'],
                    ['slug' => 'hsk-4', 'sort_order' => 4, 'en' => 'HSK 4', 'zh' => '四級'],
                    ['slug' => 'hsk-5', 'sort_order' => 5, 'en' => 'HSK 5', 'zh' => '五級'],
                    ['slug' => 'hsk-6', 'sort_order' => 6, 'en' => 'HSK 6', 'zh' => '六級'],
                ],
            ],
        ];

        foreach ($attributes as $attrData) {
            $categoryId = $catId($attrData['category']);

            $attr = Attribute::firstOrCreate(
                ['slug' => $attrData['slug']],
                [
                    'category_id'    => $categoryId,
                    'sort_order'     => $attrData['sort_order'],
                    'is_spectrum'    => $attrData['is_spectrum'],
                    'is_multi_select'=> $attrData['is_multi_select'],
                    'default_visible'=> $attrData['default_visible'],
                ]
            );

            $this->attributeLabel($attr->id, $attrData['en'], $attrData['zh']);

            foreach ($attrData['designations'] as $desData) {
                $des = Designation::firstOrCreate(
                    ['slug' => $desData['slug']],
                    ['attribute_id' => $attr->id, 'sort_order' => $desData['sort_order']]
                );

                $this->designationLabel($des->id, $desData['en'], $desData['zh']);
            }
        }
    }

    // ── Label helpers ─────────────────────────────────────────────────────────

    private function categoryLabel(int $id, string $en, string $zh): void
    {
        CategoryLabel::updateOrCreate(
            ['category_id' => $id, 'language_id' => $this->en],
            ['label' => $en]
        );
        CategoryLabel::updateOrCreate(
            ['category_id' => $id, 'language_id' => $this->zhTW],
            ['label' => $zh]
        );
    }

    private function attributeLabel(int $id, string $en, string $zh): void
    {
        AttributeLabel::updateOrCreate(
            ['attribute_id' => $id, 'language_id' => $this->en],
            ['label' => $en]
        );
        AttributeLabel::updateOrCreate(
            ['attribute_id' => $id, 'language_id' => $this->zhTW],
            ['label' => $zh]
        );
    }

    private function designationLabel(int $id, string $en, string $zh): void
    {
        DesignationLabel::updateOrCreate(
            ['designation_id' => $id, 'language_id' => $this->en],
            ['label' => $en]
        );
        DesignationLabel::updateOrCreate(
            ['designation_id' => $id, 'language_id' => $this->zhTW],
            ['label' => $zh]
        );
    }
}
