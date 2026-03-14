<?php

namespace Database\Seeders;

use App\Models\PronunciationSystem;
use Illuminate\Database\Seeder;

class PronunciationSystemSeeder extends Seeder
{
    public function run(): void
    {
        $systems = [
            [
                'slug'        => 'pinyin',
                'name'        => 'Pinyin',
                'language'    => 'Mandarin',
                'description' => 'Standard Mandarin romanization system used in mainland China and internationally.',
            ],
            [
                'slug'        => 'zhuyin',
                'name'        => 'Zhuyin Fuhao (Bopomofo)',
                'language'    => 'Mandarin',
                'description' => 'Official Mandarin phonetic system used in Taiwan.',
            ],
            [
                'slug'        => 'jyutping',
                'name'        => 'Jyutping',
                'language'    => 'Cantonese',
                'description' => 'Cantonese romanization system developed by the Linguistic Society of Hong Kong.',
            ],
        ];

        foreach ($systems as $data) {
            PronunciationSystem::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
