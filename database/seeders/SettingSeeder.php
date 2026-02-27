<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'CBT App'],
            ['key' => 'site_description', 'value' => 'Sistem Ujian Berbasis Komputer'],
            ['key' => 'academic_year', 'value' => '2024/2025'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], ['value' => $s['value']]);
        }
    }
}
