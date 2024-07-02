<?php

namespace Database\Seeders;

use App\Domain\Sales\Models\SalesChannel;
use App\Domain\Sales\Models\Visit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class VisitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get the current year
        $currentYear = Carbon::now()->year;

        // Create start date (January 1st of the current year)
        $start_date = Carbon::createFromDate($currentYear, 1, 1);

        // Create end date (December 31st of the current year)
        $end_date = Carbon::createFromDate($currentYear, 12, 31);

        $period = CarbonPeriod::create($start_date, '1 day', $end_date);

        $salesChannels = SalesChannel::all();

        foreach ($salesChannels as $salesChannel) {
            foreach ($period as $date) {
                for ($i = 1; $i <= 100; $i++) {

                    $amount = rand(1000000, 2000000);

                    Visit::updateOrcreate([
                        'date' => $date,
                        'sales_channel_id' => $salesChannel->id,
                    ], [
                        'visit_amount' => $amount,
                    ]);

                    $i++;
                }
            }
        }
    }
}
