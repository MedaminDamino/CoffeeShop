<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Promotion;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Promotion::updateOrCreate(
            ['code_promo' => 'WELCOME10'],
            [
                'promo_description' => '10% discount for new customers',
                'promo_discount_type' => 'percent',
                'promo_discount_value' => 10,
                'promo_start_date' => now()->toDateString(),
                'promo_end_date' => now()->addMonths(6)->toDateString(),
                'promo_usage_limit' => 100,
                'promo_is_active' => true,
            ]
        );
    }
}