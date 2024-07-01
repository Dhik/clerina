<?php

namespace Database\Seeders;

use App\Domain\Marketing\Models\SocialMedia;
use App\Domain\Sales\Models\AdSpentSocialMedia;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AdSpentSocialMediaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Create period date
        $start_date = Carbon::now()->startOfMonth();
        $end_date = Carbon::now()->endOfMonth();

        $period = CarbonPeriod::create($start_date, '1 day', $end_date);

        $socialMedia = SocialMedia::all();

        foreach ($socialMedia as $media) {
            foreach ($period as $date) {
                for ($i = 1; $i <= 100; $i++) {

                    $amount = rand(1000000, 2000000);

                    AdSpentSocialMedia::updateOrcreate([
                        'date' => $date,
                        'social_media_id' => $media->id,
                    ], [
                        'amount' => $amount,
                    ]);

                    $i++;
                }
            }
        }
    }
}
