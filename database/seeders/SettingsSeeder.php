<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * إدراج الإعدادات الافتراضية للتطبيق
     */
    public function run(): void
    {
        $settings = [
            // إعدادات عامة
            ['key' => 'app_name',                 'value' => 'SERP Rank Tracker', 'is_encrypted' => false, 'group' => 'general'],
            ['key' => 'default_timezone',         'value' => 'Africa/Cairo',      'is_encrypted' => false, 'group' => 'general'],
            ['key' => 'default_country_id',       'value' => '1',                 'is_encrypted' => false, 'group' => 'general'],
            ['key' => 'max_keywords_per_project', 'value' => '20',                'is_encrypted' => false, 'group' => 'general'],
            ['key' => 'check_interval_hours',     'value' => '12',                'is_encrypted' => false, 'group' => 'general'],

            // إعدادات API — تُملأ من لوحة الإعدادات
            ['key' => 'dataforseo_login',    'value' => null, 'is_encrypted' => true, 'group' => 'api'],
            ['key' => 'dataforseo_password', 'value' => null, 'is_encrypted' => true, 'group' => 'api'],
            ['key' => 'anthropic_api_key',   'value' => null, 'is_encrypted' => true, 'group' => 'api'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                array_merge($setting, ['updated_at' => now()])
            );
        }
    }
}
