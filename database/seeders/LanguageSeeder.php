<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            [
                'code'        => 'en',
                'name_en'     => 'English',
                'name_native' => 'English',
                'is_active'   => true,
            ],
            [
                'code'        => 'zh-TW',
                'name_en'     => 'Traditional Chinese',
                'name_native' => '繁體中文',
                'is_active'   => true,
            ],
            [
                'code'        => 'zh-CN',
                'name_en'     => 'Simplified Chinese',
                'name_native' => '简体中文',
                'is_active'   => true,
            ],
        ];

        foreach ($languages as $data) {
            Language::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
