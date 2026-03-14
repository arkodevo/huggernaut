<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Designation;
use App\Models\DesignationGroup;
use App\Models\DesignationGroupLabel;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeds semantic domain groups and their 41 domain designations with
// English and Traditional Chinese labels.  Idempotent — safe to re-run.
//
// Groups (11 sections, 41 domains):
//   1. Human Inner Life          人的內在        5 domains
//   2. Body & Health             身體與健康       3 domains
//   3. Personal Identity & Rels  身分與人際       3 domains
//   4. Language & Communication  語言與溝通       2 domains
//   5. Daily Living              日常生活        4 domains
//   6. Society & Institutions    社會與制度       6 domains
//   7. Physical World            自然世界        5 domains
//   8. Space, Time & Motion      時空與動作       5 domains
//   9. Culture & Knowledge       文化與知識       6 domains
//  10. Conceptual Properties     屬性概念        1 domain
//  11. Number & Quantity         數量概念        1 domain
class DomainSeeder extends Seeder
{
    private int $en;
    private int $zhTW;
    private int $attributeId;

    public function run(): void
    {
        $this->en   = Language::where('code', 'en')->value('id');
        $this->zhTW = Language::where('code', 'zh-TW')->value('id');

        $domainAttr = Attribute::where('slug', 'domain')->firstOrFail();
        $this->attributeId = $domainAttr->id;

        $groups = [
            [
                'slug'       => 'human-inner-life',
                'sort_order' => 1,
                'en'         => 'Human Inner Life',
                'zh'         => '人的內在',
                'domains'    => [
                    ['slug' => 'emotion',     'en' => 'Emotion',     'zh' => '情緒'],
                    ['slug' => 'cognition',   'en' => 'Cognition',   'zh' => '認知'],
                    ['slug' => 'perception',  'en' => 'Perception',  'zh' => '感知'],
                    ['slug' => 'personality', 'en' => 'Personality', 'zh' => '性格'],
                    ['slug' => 'values',      'en' => 'Values',      'zh' => '價值觀'],
                ],
            ],
            [
                'slug'       => 'body-and-health',
                'sort_order' => 2,
                'en'         => 'Body & Health',
                'zh'         => '身體與健康',
                'domains'    => [
                    ['slug' => 'body',     'en' => 'Body',     'zh' => '身體'],
                    ['slug' => 'health',   'en' => 'Health',   'zh' => '健康'],
                    ['slug' => 'medicine', 'en' => 'Medicine', 'zh' => '醫療'],
                ],
            ],
            [
                'slug'       => 'personal-identity-and-relationships',
                'sort_order' => 3,
                'en'         => 'Personal Identity & Relationships',
                'zh'         => '身分與人際',
                'domains'    => [
                    ['slug' => 'identity',        'en' => 'Identity',           'zh' => '身分'],
                    ['slug' => 'family',          'en' => 'Family',             'zh' => '家庭'],
                    ['slug' => 'social-relations', 'en' => 'Social Relations',  'zh' => '人際關係'],
                ],
            ],
            [
                'slug'       => 'language-and-communication',
                'sort_order' => 4,
                'en'         => 'Language & Communication',
                'zh'         => '語言與溝通',
                'domains'    => [
                    ['slug' => 'language',      'en' => 'Language',      'zh' => '語言'],
                    ['slug' => 'communication', 'en' => 'Communication', 'zh' => '溝通'],
                ],
            ],
            [
                'slug'       => 'daily-living',
                'sort_order' => 5,
                'en'         => 'Daily Living',
                'zh'         => '日常生活',
                'domains'    => [
                    ['slug' => 'food',     'en' => 'Food',     'zh' => '飲食'],
                    ['slug' => 'clothing', 'en' => 'Clothing', 'zh' => '服裝'],
                    ['slug' => 'housing',  'en' => 'Housing',  'zh' => '居住'],
                    ['slug' => 'objects',  'en' => 'Objects',  'zh' => '物品'],
                ],
            ],
            [
                'slug'       => 'society-and-institutions',
                'sort_order' => 6,
                'en'         => 'Society & Institutions',
                'zh'         => '社會與制度',
                'domains'    => [
                    ['slug' => 'education', 'en' => 'Education', 'zh' => '教育'],
                    ['slug' => 'work',      'en' => 'Work',      'zh' => '工作'],
                    ['slug' => 'business',  'en' => 'Business',  'zh' => '商業'],
                    ['slug' => 'law',       'en' => 'Law',       'zh' => '法律'],
                    ['slug' => 'politics',  'en' => 'Politics',  'zh' => '政治'],
                    ['slug' => 'society',   'en' => 'Society',   'zh' => '社會'],
                ],
            ],
            [
                'slug'       => 'physical-world',
                'sort_order' => 7,
                'en'         => 'Physical World',
                'zh'         => '自然世界',
                'domains'    => [
                    ['slug' => 'nature',    'en' => 'Nature',    'zh' => '自然'],
                    ['slug' => 'animals',   'en' => 'Animals',   'zh' => '動物'],
                    ['slug' => 'plants',    'en' => 'Plants',    'zh' => '植物'],
                    ['slug' => 'weather',   'en' => 'Weather',   'zh' => '天氣'],
                    ['slug' => 'materials', 'en' => 'Materials', 'zh' => '材料'],
                ],
            ],
            [
                'slug'       => 'space-time-and-motion',
                'sort_order' => 8,
                'en'         => 'Space, Time & Motion',
                'zh'         => '時空與動作',
                'domains'    => [
                    ['slug' => 'place',          'en' => 'Place',          'zh' => '地點'],
                    ['slug' => 'space',          'en' => 'Space',          'zh' => '空間'],
                    ['slug' => 'time',           'en' => 'Time',           'zh' => '時間'],
                    ['slug' => 'movement',       'en' => 'Movement',       'zh' => '動作'],
                    ['slug' => 'transportation', 'en' => 'Transportation', 'zh' => '交通'],
                ],
            ],
            [
                'slug'       => 'culture-and-knowledge',
                'sort_order' => 9,
                'en'         => 'Culture & Knowledge',
                'zh'         => '文化與知識',
                'domains'    => [
                    ['slug' => 'art',        'en' => 'Art',        'zh' => '藝術'],
                    ['slug' => 'leisure',    'en' => 'Leisure',    'zh' => '休閒'],
                    ['slug' => 'sports',     'en' => 'Sports',     'zh' => '體育'],
                    ['slug' => 'technology', 'en' => 'Technology', 'zh' => '科技'],
                    ['slug' => 'religion',   'en' => 'Religion',   'zh' => '宗教'],
                    ['slug' => 'philosophy', 'en' => 'Philosophy', 'zh' => '哲學'],
                ],
            ],
            [
                'slug'       => 'conceptual-properties',
                'sort_order' => 10,
                'en'         => 'Conceptual Properties',
                'zh'         => '屬性概念',
                'domains'    => [
                    ['slug' => 'properties', 'en' => 'Properties', 'zh' => '屬性'],
                ],
            ],
            [
                'slug'       => 'number-and-quantity',
                'sort_order' => 11,
                'en'         => 'Number & Quantity',
                'zh'         => '數量概念',
                'domains'    => [
                    ['slug' => 'number-quantity', 'en' => 'Number & Quantity', 'zh' => '數量'],
                ],
            ],
        ];

        $domainSortOrder = 1;

        foreach ($groups as $groupData) {
            $group = DesignationGroup::firstOrCreate(
                ['slug' => $groupData['slug']],
                [
                    'attribute_id' => $this->attributeId,
                    'sort_order'   => $groupData['sort_order'],
                ]
            );

            $this->upsertGroupLabel($group->id, $this->en, $groupData['en']);
            $this->upsertGroupLabel($group->id, $this->zhTW, $groupData['zh']);

            foreach ($groupData['domains'] as $domainData) {
                $designation = Designation::firstOrCreate(
                    ['slug' => $domainData['slug']],
                    [
                        'attribute_id'         => $this->attributeId,
                        'designation_group_id' => $group->id,
                        'sort_order'           => $domainSortOrder,
                    ]
                );

                // Ensure group FK is set even if designation already existed.
                if (is_null($designation->designation_group_id)) {
                    $designation->update(['designation_group_id' => $group->id]);
                }

                $this->upsertDesignationLabel($designation->id, $this->en, $domainData['en']);
                $this->upsertDesignationLabel($designation->id, $this->zhTW, $domainData['zh']);

                $domainSortOrder++;
            }
        }
    }

    private function upsertGroupLabel(int $groupId, int $langId, string $label): void
    {
        DesignationGroupLabel::updateOrCreate(
            ['designation_group_id' => $groupId, 'language_id' => $langId],
            ['label' => $label]
        );
    }

    private function upsertDesignationLabel(int $designationId, int $langId, string $labelText): void
    {
        // DesignationLabel has no auto-increment PK (composite key), so Eloquent's
        // updateOrCreate can't update existing rows.  Use DB::table instead.
        DB::table('designation_labels')->updateOrInsert(
            ['designation_id' => $designationId, 'language_id' => $langId],
            ['label' => $labelText, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
