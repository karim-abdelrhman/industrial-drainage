<?php

namespace Database\Factories;

use App\Enums\ActivityType;
use App\Enums\LocationType;
use App\Models\Establishment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Establishment>
 */
class EstablishmentFactory extends Factory
{
    private static array $arabicCompanyNames = [
        'مصنع النيل للورق والكرتون',
        'شركة الدلتا للصناعات الكيماوية',
        'مصنع الإسكندرية للزجاج',
        'شركة القاهرة للمنسوجات',
        'مصنع الجيزة للبلاستيك',
        'شركة سيناء للصناعات المعدنية',
        'مصنع الصعيد للغزل والنسيج',
        'شركة بورسعيد للبتروكيماويات',
        'مصنع الإسماعيلية للطلاء والدهانات',
        'شركة المنيا للصناعات الغذائية',
        'مصنع أسيوط للأسمدة والكيماويات',
        'شركة طنطا للصناعات الجلدية',
        'مصنع المنصورة للصناعات الخشبية',
        'شركة الفيوم للصناعات الدوائية',
        'مصنع سوهاج للزيوت والشحوم',
    ];

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(static::$arabicCompanyNames),
            'activity_type' => $this->faker->randomElement(ActivityType::cases()),
            'location_type' => $this->faker->randomElement(LocationType::cases()),
            'address' => $this->faker->address(),
            'contact_person' => $this->faker->name(),
            'phone' => '01'.$this->faker->numerify('#########'),
            'email' => $this->faker->unique()->companyEmail(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function industrial(): static
    {
        return $this->state(['activity_type' => ActivityType::Industrial]);
    }

    public function insideCity(): static
    {
        return $this->state(['location_type' => LocationType::InsideCity]);
    }
}
