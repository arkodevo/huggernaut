<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [

            // ── Points total ──────────────────────────────────────────────────

            [
                'slug'         => 'first-steps',
                'name'         => 'First Steps',
                'description'  => 'Earned your first 10 points — welcome to the journey!',
                'icon'         => '🌱',
                'trigger_type' => 'points_total',
                'threshold'    => 10,
                'bonus_credits' => 2,
                'sort_order'   => 10,
            ],
            [
                'slug'         => 'growing-roots',
                'name'         => 'Growing Roots',
                'description'  => 'Reached 100 lifetime points. The roots are taking hold.',
                'icon'         => '🌿',
                'trigger_type' => 'points_total',
                'threshold'    => 100,
                'bonus_credits' => 5,
                'sort_order'   => 20,
            ],
            [
                'slug'         => 'deep-roots',
                'name'         => 'Deep Roots',
                'description'  => 'Reached 500 lifetime points. Your vocabulary is flourishing.',
                'icon'         => '🍃',
                'trigger_type' => 'points_total',
                'threshold'    => 500,
                'bonus_credits' => 10,
                'sort_order'   => 30,
            ],
            [
                'slug'         => 'ancient-tree',
                'name'         => 'Ancient Tree',
                'description'  => 'Reached 2000 lifetime points. A towering achievement.',
                'icon'         => '🌳',
                'trigger_type' => 'points_total',
                'threshold'    => 2000,
                'bonus_credits' => 20,
                'sort_order'   => 40,
            ],

            // ── Action: word saving ───────────────────────────────────────────

            [
                'slug'         => 'word-collector',
                'name'         => 'Word Collector',
                'description'  => 'Saved your first word. Every great lexicon starts somewhere.',
                'icon'         => '📖',
                'trigger_type' => 'action_count',
                'threshold'    => 1,
                'action_type'  => 'word_saved',
                'bonus_credits' => 2,
                'sort_order'   => 50,
            ],
            [
                'slug'         => 'word-hoarder',
                'name'         => 'Word Hoarder',
                'description'  => 'Saved 50 words. Your collection is growing beautifully.',
                'icon'         => '📚',
                'trigger_type' => 'action_count',
                'threshold'    => 50,
                'action_type'  => 'word_saved',
                'bonus_credits' => 5,
                'sort_order'   => 60,
            ],

            // ── Action: flashcard sessions ─────────────────────────────────────

            [
                'slug'         => 'first-flash',
                'name'         => 'First Flash',
                'description'  => 'Completed your first flashcard session. Practice makes permanent.',
                'icon'         => '⚡',
                'trigger_type' => 'action_count',
                'threshold'    => 1,
                'action_type'  => 'flashcard_session',
                'bonus_credits' => 3,
                'sort_order'   => 70,
            ],
            [
                'slug'         => 'flashcard-adept',
                'name'         => 'Flashcard Adept',
                'description'  => 'Completed 20 flashcard sessions. Consistency is the key.',
                'icon'         => '🃏',
                'trigger_type' => 'action_count',
                'threshold'    => 20,
                'action_type'  => 'flashcard_session',
                'bonus_credits' => 10,
                'sort_order'   => 80,
            ],

            // ── Action: streak ────────────────────────────────────────────────

            [
                'slug'         => 'seven-suns',
                'name'         => 'Seven Suns',
                'description'  => 'Maintained a 7-day learning streak. The habit is forming.',
                'icon'         => '🌅',
                'trigger_type' => 'action_count',
                'threshold'    => 1,
                'action_type'  => 'streak_7day',
                'bonus_credits' => 5,
                'sort_order'   => 90,
            ],
            [
                'slug'         => 'thirty-moons',
                'name'         => 'Thirty Moons',
                'description'  => 'Kept a 30-day streak. Dedication like water — patient, persistent.',
                'icon'         => '🌕',
                'trigger_type' => 'action_count',
                'threshold'    => 1,
                'action_type'  => 'streak_30day',
                'bonus_credits' => 20,
                'sort_order'   => 100,
            ],

            // ── Manual / special ──────────────────────────────────────────────

            [
                'slug'         => 'founding-student',
                'name'         => 'Founding Student',
                'description'  => 'An early adopter who believed in 流動 from the beginning.',
                'icon'         => '🀄',
                'trigger_type' => 'manual',
                'threshold'    => 0,
                'bonus_credits' => 15,
                'sort_order'   => 200,
            ],

        ];

        foreach ($badges as $data) {
            Badge::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
