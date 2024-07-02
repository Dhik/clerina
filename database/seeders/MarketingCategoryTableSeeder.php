<?php

namespace Database\Seeders;

use App\Domain\Marketing\Enums\MarketingCategoryTypeEnum;
use App\Domain\Marketing\Models\MarketingCategory;
use App\Domain\Marketing\Models\MarketingSubCategory;
use Illuminate\Database\Seeder;

class MarketingCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brandings = [
            'Artis',
            'Mega KOL',
            'Mega Random',
            'Creative Campaign',
            'Brand Ambassador',
            'Creative Production',
        ];

        foreach ($brandings as $branding) {
            $check = MarketingCategory::where('name', $branding)->first();

            if (is_null($check)) {
                MarketingCategory::create(['name' => $branding, 'type' => MarketingCategoryTypeEnum::Branding]);
            }
        }

        $marketings = [
            'Media Online',
            'KOL',
        ];

        $mediaOnlines = [
            'Portal Media',
            'Buzzer',
            'Paid Promote',
        ];

        $kols = [
            'KOL Beauty',
            'Skin Expert',
            'KOL Random',
        ];

        foreach ($marketings as $marketing) {
            $check = MarketingCategory::where('name', $marketing)->first();

            if (is_null($check)) {
                $marketingData = MarketingCategory::create([
                    'name' => $marketing, 'type' => MarketingCategoryTypeEnum::Marketing,
                ]);

                if ($marketingData->name === 'Media Online') {
                    foreach ($mediaOnlines as $mediaOnline) {
                        $check = MarketingSubCategory::where('name', $mediaOnline)->first();

                        if (is_null($check)) {
                            MarketingSubCategory::create([
                                'marketing_category_id' => $marketingData->id,
                                'name' => $mediaOnline,
                            ]);
                        }
                    }
                }

                if ($marketingData->name === 'KOL') {
                    foreach ($kols as $kol) {
                        $check = MarketingSubCategory::where('name', $kol)->first();

                        if (is_null($check)) {
                            MarketingSubCategory::create([
                                'marketing_category_id' => $marketingData->id,
                                'name' => $kol,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
