<?php

namespace Database\Seeders;

use App\Domain\Marketing\Enums\MarketingCategoryTypeEnum;
use App\Domain\Marketing\Models\Marketing;
use App\Domain\Marketing\Models\MarketingCategory;
use App\Domain\Marketing\Models\MarketingSubCategory;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class MarketingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create period date
        $start_date = Carbon::now()->startOfMonth();
        $end_date = Carbon::now()->endOfMonth();

        $period = CarbonPeriod::create($start_date, '1 day', $end_date);

        // Create seeder brandings
        $brandings = MarketingCategory::where('type', MarketingCategoryTypeEnum::Branding)->get();

        foreach ($brandings as $branding) {
            foreach ($period as $date) {
                Marketing::create([
                    'date' => $date,
                    'type' => MarketingCategoryTypeEnum::Branding,
                    'marketing_category_id' => $branding->id,
                    'amount' => rand(100000000, 200000000),
                ]);
            }
        }

        $marketings = MarketingSubCategory::all();

        foreach ($marketings as $marketing) {
            foreach ($period as $date) {
                Marketing::create([
                    'date' => $date,
                    'type' => MarketingCategoryTypeEnum::Marketing,
                    'marketing_category_id' => $marketing->marketing_category_id,
                    'marketing_sub_category_id' => $marketing->id,
                    'amount' => rand(100000000, 200000000),
                ]);
            }
        }
    }
}
