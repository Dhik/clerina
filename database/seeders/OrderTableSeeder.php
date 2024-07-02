<?php

namespace Database\Seeders;

use App\Domain\Order\Models\Order;
use App\Domain\Sales\Models\SalesChannel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class OrderTableSeeder extends Seeder
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

                    $qty = rand(1000, 2000);
                    $price = rand(100000000, 200000000);

                    Order::create([
                        'date' => $date,
                        'id_order' => rand(100000, 200000),
                        'sales_channel_id' => $salesChannel->id,
                        'customer_name' => $faker->name,
                        'customer_phone_number' => $faker->phoneNumber,
                        'product' => $faker->word,
                        'qty' => $qty,
                        'receipt_number' => $faker->numerify('############'),
                        'shipment' => $faker->word,
                        'payment_method' => $faker->word,
                        'sku' => $faker->numerify('############'),
                        'variant' => $faker->word,
                        'price' => $price,
                        'username' => $faker->userName,
                        'shipping_address' => $faker->address,
                        'city' => $faker->city,
                        'province' => $faker->word,
                        'amount' => $qty * $price,
                    ]);

                    $i++;
                }
            }
        }
    }
}
