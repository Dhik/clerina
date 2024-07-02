<?php

namespace Database\Seeders;

use App\Domain\Campaign\Enums\KeyOpinionLeaderEnum;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class KeyOpinionLeaderSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // You can adjust the loop count according to how many records you want to seed
        for ($i = 0; $i < 100; $i++) {
            DB::table('key_opinion_leaders')->insert([
                'channel' => $faker->randomElement(KeyOpinionLeaderEnum::Channel),
                'username' => $faker->userName,
                'niche' => $faker->word,
                'average_view' => $faker->numberBetween(1000, 1000000),
                'skin_type' => $faker->randomElement(KeyOpinionLeaderEnum::SkinType),
                'skin_concern' => $faker->randomElement(KeyOpinionLeaderEnum::SkinConcern),
                'content_type' => $faker->randomElement(KeyOpinionLeaderEnum::ContentType),
                'rate' => $faker->randomNumber(5),
                'pic_contact' => 1,
                'created_by' => 1,
                'cpm' => $faker->randomFloat(2, 0.1, 10),
                'name' => $faker->name,
                'address' => $faker->address,
                'phone_number' => $faker->phoneNumber,
                'bank_name' => $faker->randomElement(['Bank A', 'Bank B', 'Bank C']),
                'bank_account' => $faker->bankAccountNumber,
                'bank_account_name' => $faker->name,
                'npwp' => $faker->boolean,
                'npwp_number' => $faker->boolean ? $faker->numerify('##############') : null,
                'nik' => $faker->boolean ? $faker->numerify('############') : null,
                'notes' => $faker->sentence,
                'product_delivery' => $faker->boolean,
                'product' => $faker->word,
                'tenant_id' => $faker->numberBetween(1, 2),
            ]);
        }
    }
}
