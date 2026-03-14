<?php

/*
|--------------------------------------------------------------------------
| Fluency Level Ladder
|--------------------------------------------------------------------------
|
| Levels are determined by points_total_earned (lifetime, never decreases).
| Spending points does NOT de-level a user — the ladder only climbs.
|
| Each entry:
|   'slug'          — stable identifier
|   'name'          — display name (English + Chinese)
|   'icon'          — emoji icon (Nature theme)
|   'min_points'    — minimum lifetime points to reach this level
|   'bonus_credits' — one-time AI credits awarded on first reaching this level
|
*/

return [

    [
        'slug'          => 'seedling',
        'name'          => 'Seedling 幼苗',
        'icon'          => '🌱',
        'min_points'    => 0,
        'bonus_credits' => 0,
    ],
    [
        'slug'          => 'sprout',
        'name'          => 'Sprout 嫩芽',
        'icon'          => '🌿',
        'min_points'    => 50,
        'bonus_credits' => 5,
    ],
    [
        'slug'          => 'sapling',
        'name'          => 'Sapling 樹苗',
        'icon'          => '🍃',
        'min_points'    => 200,
        'bonus_credits' => 10,
    ],
    [
        'slug'          => 'scholar',
        'name'          => 'Scholar 學者',
        'icon'          => '🌳',
        'min_points'    => 600,
        'bonus_credits' => 20,
    ],
    [
        'slug'          => 'adept',
        'name'          => 'Adept 能手',
        'icon'          => '🎋',
        'min_points'    => 1500,
        'bonus_credits' => 35,
    ],
    [
        'slug'          => 'master',
        'name'          => 'Master 大師',
        'icon'          => '🀄',
        'min_points'    => 4000,
        'bonus_credits' => 50,
    ],

];
