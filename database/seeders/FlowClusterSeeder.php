<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\Language;
use App\Models\PosLabel;
use App\Models\SenseRelationType;
use App\Models\WordObject;
use App\Models\WordPronunciation;
use App\Models\WordSense;
use App\Models\WordSenseDefinition;
use App\Models\WordSenseExample;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeds the 'flow' lexical cluster from flow_seed_final_20260309.json.
// Idempotent — safe to re-run; skips word_objects whose smart_id already exists.
//
// Pass 1 — per entry:
//   word_object · word_pronunciation
//   Per sense: word_sense · word_sense_designations (register + dimensions)
//              word_sense_definition (pos · def · formula · usage_note)
//              word_sense_pos · word_sense_example
//
// Pass 2 — word_sense_relations for immediate / close / distant relatives.
//   immediate → synonym_close  |  close → synonym_related  |  distant → contrast
//   External smart_ids (not in the seed) are silently skipped.
class FlowClusterSeeder extends Seeder
{
    private const JSON_PATH = '../../Documents/華語/planning/flow_seed_final_20260309.json';

    private const PINYIN_SYSTEM_ID = 1;

    private const TOCFL_MAP = [
        1 => 'tocfl-prep',
        2 => 'tocfl-entry',
        3 => 'tocfl-basic',
        4 => 'tocfl-advanced',
        5 => 'tocfl-high',
        6 => 'tocfl-fluency',
    ];

    // Proximity ring → sense_relation_type slug
    private const PROXIMITY_TYPE = [
        'immediate' => 'synonym_close',
        'close'     => 'synonym_related',
        'distant'   => 'contrast',
    ];

    // Runtime lookups
    private int $langEn;
    private array $designations;   // slug → id
    private array $posLabels;      // slug → id
    private array $relationTypes;  // slug → id
    private array $seededSenses;   // smart_id → [sense_id, ...]

    // ─────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->langEn      = Language::where('code', 'en')->value('id');
        $this->designations = Designation::all()->keyBy('slug')->map->id->all();
        $this->posLabels    = PosLabel::all()->keyBy('slug')->map->id->all();
        $this->relationTypes = SenseRelationType::all()->keyBy('slug')->map->id->all();
        $this->seededSenses  = [];

        $json    = file_get_contents(base_path(self::JSON_PATH));
        $data    = json_decode($json, true);
        $entries = $data['entries'];

        // ── Pass 1: word_objects, senses, definitions, examples ───────────────

        $created = $skipped = 0;

        foreach ($entries as $entry) {
            $this->seedEntry($entry) ? $created++ : $skipped++;
        }

        // ── Pass 2: lexical cluster relations ─────────────────────────────────

        $relations = 0;

        foreach ($entries as $entry) {
            $relations += $this->seedRelatives($entry);
        }

        $this->command->info(
            "FlowClusterSeeder: {$created} created, {$skipped} skipped, {$relations} relations."
        );
    }

    // ── Pass 1 helpers ────────────────────────────────────────────────────────

    private function seedEntry(array $e): bool
    {
        $smartId = $e['smart_id'];

        // ── word_object ───────────────────────────────────────────────────────

        $word = WordObject::firstOrCreate(
            ['smart_id' => $smartId],
            [
                'traditional'  => $e['traditional'],
                'simplified'   => $e['simplified'],
                'radical_id'   => $e['radical_id'],
                'strokes_trad' => $e['strokes_trad'],
                'status'       => 'draft',
            ]
        );

        if (! $word->wasRecentlyCreated) {
            $this->command->line("  skip: {$e['traditional']}");
            // Still register existing senses so relations can be wired
            $this->seededSenses[$smartId] = WordSense::where('word_object_id', $word->id)
                ->orderBy('id')
                ->pluck('id')
                ->all();
            return false;
        }

        $this->command->line("  create: {$e['traditional']}");

        // ── word_pronunciation ────────────────────────────────────────────────

        $pronunciation = WordPronunciation::firstOrCreate(
            [
                'word_object_id'          => $word->id,
                'pronunciation_system_id' => self::PINYIN_SYSTEM_ID,
                'pronunciation_text'      => $e['pinyin'],
            ],
            ['is_primary' => true]
        );

        // ── Entry-level derived IDs ───────────────────────────────────────────

        $tocflId = $this->designationId(self::TOCFL_MAP[$e['level']] ?? null);
        $hskId   = $e['hsk'] ? $this->designationId($e['hsk']) : null;

        $dimensionIds = array_values(array_filter(
            array_map(fn ($s) => $this->designationId($s), $e['dimension'])
        ));

        // ── Seed each sense ───────────────────────────────────────────────────

        $senseIds = [];

        foreach ($e['senses'] as $i => $senseData) {
            $sense = $this->seedSense(
                word:         $word,
                pronunciation: $pronunciation,
                senseData:    $senseData,
                sortOrder:    $i + 1,
                tocflId:      $tocflId,
                hskId:        $hskId,
                dimensionIds: $dimensionIds,
            );

            $senseIds[] = $sense->id;
        }

        $this->seededSenses[$smartId] = $senseIds;

        return true;
    }

    private function seedSense(
        WordObject $word,
        WordPronunciation $pronunciation,
        array $senseData,
        int $sortOrder,
        ?int $tocflId,
        ?int $hskId,
        array $dimensionIds,
    ): WordSense {
        // ── word_sense ────────────────────────────────────────────────────────

        $sense = WordSense::create([
            'word_object_id'      => $word->id,
            'pronunciation_id'    => $pronunciation->id,
            'channel_id'          => $this->designationId($senseData['channel']),
            'connotation_id'      => $this->designationId($senseData['connotation']),
            'intensity'           => $senseData['intensity'],
            'tocfl_level_id'      => $tocflId,
            'hsk_level_id'        => $hskId,
            'status'              => 'draft',
        ]);

        // ── Domains (primary + secondary) via pivot ─────────────────────────
        $primaryDomainId   = $this->designationId($senseData['semanticDomainPrimary']);
        $secondaryDomainId = $this->designationId($senseData['semanticDomainSecondary']);
        $domainSync = [];
        if ($primaryDomainId)   $domainSync[$primaryDomainId]   = ['is_primary' => true,  'sort_order' => 0];
        if ($secondaryDomainId) $domainSync[$secondaryDomainId] = ['is_primary' => false, 'sort_order' => 1];
        if ($domainSync) $sense->domains()->sync($domainSync);

        // ── word_sense_designations: register (per-sense) + dimensions (entry) ─

        $designationIds = array_values(array_unique(array_filter([
            $this->designationId($senseData['register']),
            ...$dimensionIds,
        ])));

        if ($designationIds) {
            $sense->designations()->attach($designationIds);
        }

        // ── word_sense_definition ─────────────────────────────────────────────
        // formula + usage_note live here per v1.5 §3C (POS + def are inseparable)

        $posId = $this->posLabels[$senseData['pos']] ?? null;

        $definition = WordSenseDefinition::create([
            'word_sense_id'   => $sense->id,
            'language_id'     => $this->langEn,
            'pos_id'          => $posId,
            'definition_text' => $senseData['def'],
            'formula'         => $senseData['formula'] ?? null,
            'usage_note'      => $senseData['usageNote'] ?? null,
            'sort_order'      => $sortOrder,
        ]);

        // ── word_sense_pos index ──────────────────────────────────────────────

        if ($posId) {
            $sense->posLabels()->attach($posId, ['is_primary' => true]);
        }

        // ── word_sense_example ────────────────────────────────────────────────

        $example = WordSenseExample::create([
            'word_sense_id' => $sense->id,
            'definition_id' => $definition->id,
            'chinese_text'  => $senseData['example']['cn'],
            'source'        => 'default',
            'is_public'     => true,
            'is_suppressed' => false,
        ]);

        if (! empty($senseData['example']['en'])) {
            $enLangId = \DB::table('languages')->where('code', 'en')->value('id');
            if ($enLangId) {
                \DB::table('word_sense_example_translations')->insert([
                    'word_sense_example_id' => $example->id,
                    'language_id'           => $enLangId,
                    'translation_text'      => $senseData['example']['en'],
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }
        }

        return $sense;
    }

    // ── Pass 2: relations ─────────────────────────────────────────────────────

    private function seedRelatives(array $entry): int
    {
        $sourceSenseIds = $this->seededSenses[$entry['smart_id']] ?? [];

        if (empty($sourceSenseIds)) {
            return 0;
        }

        $count = 0;
        $now   = now();

        foreach (self::PROXIMITY_TYPE as $proximity => $typeSlug) {
            $targetSmartIds = $entry['relatives'][$proximity] ?? [];
            $relationTypeId = $this->relationTypes[$typeSlug] ?? null;

            if (! $relationTypeId) {
                continue;
            }

            foreach ($targetSmartIds as $targetSmartId) {
                // Link to the first sense of the target word (closest proxy)
                $targetSenseId = ($this->seededSenses[$targetSmartId] ?? [])[0] ?? null;

                if (! $targetSenseId) {
                    continue; // not in this seed — skip silently
                }

                foreach ($sourceSenseIds as $sourceSenseId) {
                    $count += DB::table('word_sense_relations')->insertOrIgnore([
                        'word_sense_id'    => $sourceSenseId,
                        'related_sense_id' => $targetSenseId,
                        'relation_type_id' => $relationTypeId,
                        'editorial_note'   => null,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);
                }
            }
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function designationId(?string $slug): ?int
    {
        if (! $slug) {
            return null;
        }

        return $this->designations[$slug] ?? null;
    }
}
