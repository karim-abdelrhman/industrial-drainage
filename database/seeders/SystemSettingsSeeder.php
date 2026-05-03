<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'collection_fee_inside_city',
                'label' => 'رسوم جمع العينة (داخل النطاق)',
                'value' => '250.00',
                'type' => 'decimal',
                'description' => 'رسوم جمع عينة المياه للمنشآت الواقعة داخل النطاق العمراني (بالجنيه)',
            ],
            [
                'key' => 'collection_fee_outside_city',
                'label' => 'رسوم جمع العينة (خارج النطاق)',
                'value' => '450.00',
                'type' => 'decimal',
                'description' => 'رسوم جمع عينة المياه للمنشآت الواقعة خارج النطاق العمراني (بالجنيه)',
            ],
            [
                'key' => 'collection_fee_composite',
                'label' => 'رسوم جمع العينة المركبة',
                'value' => '1400.00',
                'type' => 'decimal',
                'description' => 'رسوم جمع العينة المركبة (تحل محل رسوم الجمع العادية، لا تُضاف إليها)',
            ],
            [
                'key' => 'admin_fee_percentage',
                'label' => 'نسبة الرسوم الإدارية',
                'value' => '20.00',
                'type' => 'percentage',
                'description' => 'نسبة الرسوم الإدارية المحسوبة على رسوم جمع العينة (عادية أو مركبة)',
            ],
            [
                'key' => 'analysis_fee',
                'label' => 'رسوم التحليل',
                'value' => '355.00',
                'type' => 'decimal',
                'description' => 'رسوم تحليل العينة في المعمل (بالجنيه)',
            ],
            [
                'key' => 'issuance_fee',
                'label' => 'رسوم الإصدار',
                'value' => '0.50',
                'type' => 'decimal',
                'description' => 'رسوم إصدار المطالبة المالية (بالجنيه)',
            ],
            [
                'key' => 'vat_percentage',
                'label' => 'نسبة ضريبة القيمة المضافة',
                'value' => '14.00',
                'type' => 'percentage',
                'description' => 'نسبة ضريبة القيمة المضافة المطبقة على إجمالي الفاتورة',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
