<?php

namespace Database\Seeders;

use App\Domain\Marketing\Models\SocialMedia;
use Illuminate\Database\Seeder;

class SocialMediaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salesChannels = [
            'Facebook',
            'Snack Video',
            'Instagram',
            'Tiktok',
            'Google',
        ];

        foreach ($salesChannels as $salesChannel) {
            $check = SocialMedia::where('name', $salesChannel)->first();

            if (is_null($check)) {
                SocialMedia::create(['name' => $salesChannel]);
            }
        }
    }
}
