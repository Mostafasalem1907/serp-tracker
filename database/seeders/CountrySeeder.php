<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * إدراج الدول الافتراضية الثلاث في قاعدة البيانات
     */
    public function run(): void
    {
        Country::insert([
            [
                'name_ar'       => 'مصر',
                'name_en'       => 'Egypt',
                'location_code' => '2818',
                'timezone'      => 'Africa/Cairo',
                'se_domain'     => 'google.com.eg',
                'is_active'     => true,
                'sort_order'    => 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name_ar'       => 'السعودية',
                'name_en'       => 'Saudi Arabia',
                'location_code' => '2682',
                'timezone'      => 'Asia/Riyadh',
                'se_domain'     => 'google.com.sa',
                'is_active'     => true,
                'sort_order'    => 2,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name_ar'       => 'المغرب',
                'name_en'       => 'Morocco',
                'location_code' => '21244',
                'timezone'      => 'Africa/Casablanca',
                'se_domain'     => 'google.co.ma',
                'is_active'     => true,
                'sort_order'    => 3,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }
}
