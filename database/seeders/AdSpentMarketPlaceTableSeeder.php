<?php

namespace Database\Seeders;

use App\Domain\Sales\Models\AdSpentMarketPlace;
use App\Domain\Sales\Models\SalesChannel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AdSpentMarketPlaceTableSeeder extends Seeder
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

        $salesChannels = SalesChannel::all();

        foreach ($salesChannels as $salesChannel) {
            foreach ($period as $date) {
                for ($i = 1; $i <= 100; $i++) {

                    $amount = rand(1000000, 2000000);

                    AdSpentMarketPlace::updateOrcreate([
                        'date' => $date,
                        'sales_channel_id' => $salesChannel->id,
                    ], [
                        'amount' => $amount,
                    ]);

                    $i++;
                }
            }
        }
    }
}
