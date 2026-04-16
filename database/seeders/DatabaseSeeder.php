<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Reference data (order matters — dependencies cascade down) ─────────
        $this->call([
            LanguageSeeder::class,           // must be first — all i18n labels depend on it
            PronunciationSystemSeeder::class,
            KangxiRadicalSeeder::class,      // 214 radicals — required before any word_objects
            PosLabelSeeder::class,
            PosGroupSeeder::class,           // simplified display groups (must follow PosLabelSeeder)
            SenseRelationTypeSeeder::class,
            NoteTypeSeeder::class,           // formula, usage-note, learner-traps
            TaxonomySeeder::class,           // categories → attributes → designations + labels
            DomainSeeder::class,             // 11 domain groups + 41 domain designations
            DefaultIconThemeSeeder::class,   // Nature emoji theme; depends on taxonomy
        ]);

        // ── Content: TOCFL Band 1 (100 words) ────────────────────────────────
        $this->call([
            TocflBand1Seeder::class,
        ]);

        // ── Content: Flow lexical cluster (23 words — ritual starter) ─────────
        $this->call([
            FlowClusterSeeder::class,
        ]);

        // ── Engagement: badges ────────────────────────────────────────────────
        $this->call([
            BadgeSeeder::class,
        ]);

        // ── Dev admin user ────────────────────────────────────────────────────
        // Change credentials before deploying to staging/production.
        User::firstOrCreate(
            ['email' => 'shifu@huggernaut.com'],
            [
                'name'     => 'Shifu',
                'password' => bcrypt('WeFlowAsOne^*!'),
                'role'     => 'admin',
            ]
        );
    }
}
