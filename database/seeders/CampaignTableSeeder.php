<?php

namespace Database\Seeders;

use App\Domain\Campaign\Enums\CampaignContentEnum;
use App\Domain\Campaign\Enums\OfferEnum;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Campaign\Models\Offer;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CampaignTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Create period date
        $start_date = Carbon::now()->startOfMonth();
        $end_date = Carbon::now()->endOfMonth();

        $period = CarbonPeriod::create($start_date, '1 day', $end_date);
        $period2 = CarbonPeriod::create($start_date, '1 day', $end_date);

        $campaignId = DB::table('campaigns')->insertGetId([
            'title' => $faker->title,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'description' => $faker->word,
            'created_by' => 1,
            'tenant_id' => 1,
            'created_at' => Carbon::now(),
        ]);

        $kols = KeyOpinionLeader::withoutGlobalScopes()->limit(10)->get();

        foreach ($kols as $kol) {
            $offerId = DB::table('offers')->insertGetId([
                'key_opinion_leader_id' => $kol->id,
                'rate_per_slot' => 10000,
                'benefit' => $faker->word,
                'negotiate' => OfferEnum::FreeIgs,
                'campaign_id' => $campaignId,
                'status' => OfferEnum::Approved,
                'created_by' => 1,
                'bank_name' => $faker->word,
                'bank_account' => $faker->word,
                'bank_account_name' => $faker->word,
                'nik' => $faker->word,
                'acc_slot' => 1000,
                'rate_total_slot' => 10000000,
                'rate_final_slot' => 10000000,
                'final_amount' => 10000000,
            ]);

            foreach ($period as $date) {
                for ($i=1; $i<=12; $i++) {
                    $rateCard = rand(1000, 10000);
                    $contentId = DB::table('campaign_contents')->insertGetId([
                        'campaign_id' => $campaignId,
                        'key_opinion_leader_id' => $kol->id,
                        'channel' => CampaignContentEnum::InstagramFeed,
                        'task_name' => $faker->word,
                        'link' => 'https://www.instagram.com/reel/C3ACFtNLkDP/',
                        'rate_card' => $rateCard,
                        'product' => $faker->word,
                        'created_by' => 1,
                        'tenant_id' => 1,
                        'created_at' => $date
                    ]);

                    foreach ($period2 as $date2) {
                        $view = rand(10000, 1000000);
                        $like = rand(10000, 1000000);
                        $comment = rand(10000, 1000000);

                        DB::table('statistics')->insert([
                            'date' => $date2,
                            'campaign_id' => $campaignId,
                            'campaign_content_id' => $contentId,
                            'view' => $view,
                            'like' => $like,
                            'comment' => $comment,
                            'tenant_id' => 1,
                            'cpm' => $this->calculateCPM($view, $rateCard),
                            'engagement' => $view + $like + $comment
                        ]);
                    }

                }
            }

            foreach ($period as $date) {
                for ($i=1; $i<=12; $i++) {
                    $rateCard = rand(1000, 10000);
                    $contentId = DB::table('campaign_contents')->insertGetId([
                        'campaign_id' => $campaignId,
                        'key_opinion_leader_id' => $kol->id,
                        'channel' => CampaignContentEnum::TiktokVideo,
                        'task_name' => $faker->word,
                        'link' => 'https://www.tiktok.com/@cleorabeauty.id/video/7362162243921841413?is_from_webapp=1&sender_device=pc&web_id=7123425463527867905',
                        'rate_card' => $rateCard,
                        'product' => $faker->word,
                        'created_by' => 1,
                        'tenant_id' => 1,
                        'created_at' => $date
                    ]);

                    foreach ($period2 as $date2) {
                        $view = rand(10000, 1000000);
                        $like = rand(10000, 1000000);
                        $comment = rand(10000, 1000000);

                        DB::table('statistics')->insert([
                            'date' => $date2,
                            'campaign_id' => $campaignId,
                            'campaign_content_id' => $contentId,
                            'view' => $view,
                            'like' => $like,
                            'comment' => $comment,
                            'tenant_id' => 1,
                            'cpm' => $this->calculateCPM($view, $rateCard),
                            'engagement' => $view + $like + $comment
                        ]);
                    }
                }
            }
        }
    }

    protected function calculateCPM($view, $rate) {
        if ($view === 0) {
            return 0;
        }

        return ($rate / $view) * 1000;
    }
}
