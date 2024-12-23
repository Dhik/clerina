<?php

namespace Database\Seeders;

use App\Domain\Sales\Models\SalesChannel;
use Illuminate\Database\Seeder;

class SalesChannelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salesChannels = [
            'Shopee',
            'Lazada',
            'Tokopedia',
            'Tiktok Shop',
            'Manual',
            'Others',
        ];

        foreach ($salesChannels as $salesChannel) {
            $check = SalesChannel::where('name', $salesChannel)->first();

            if (is_null($check)) {
                SalesChannel::create(['name' => $salesChannel]);
            }
        }
    }
}
