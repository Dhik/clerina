<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\KeywordMonitoring\Models\KeywordMonitoring;
use App\Domain\KeywordMonitoring\Models\Posting;
use Illuminate\Support\Facades\Http;

class UpdatePostings extends Command
{
    protected $description = 'Update postings data for each keyword from keyword_monitorings table';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postings:update {interval}';

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $interval = $this->argument('interval');

        if (!in_array($interval, ['daily', '3days', 'weekly'])) {
            $this->error('Invalid interval specified. Use daily, 3days, or weekly.');
            return;
        }

        $keywords = KeywordMonitoring::all();

        foreach ($keywords as $keywordMonitoring) {
            $keyword = $keywordMonitoring->keyword;

            $response = Http::withHeaders([
                'x-rapidapi-key' => '2bc060ac02msh3d873c6c4d26f04p103ac5jsn00306dda9986',
                'x-rapidapi-host' => 'tokapi-mobile-version.p.rapidapi.com'
            ])->get('https://tokapi-mobile-version.p.rapidapi.com/v1/search/post', [
                'keyword' => $keyword,
                'count' => 30,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                foreach ($data['search_item_list'] as $post) {
                    $awemeId = $post['aweme_info']['statistics']['aweme_id'];

                    Posting::updateOrCreate(
                        ['aweme_id' => $awemeId, 'keyword_id' => $keywordMonitoring->id],
                        [
                            'play_count' => $post['aweme_info']['statistics']['play_count'],
                            'comment_count' => $post['aweme_info']['statistics']['comment_count'],
                            'digg_count' => $post['aweme_info']['statistics']['digg_count'],
                            'share_count' => $post['aweme_info']['statistics']['share_count'],
                            'collect_count' => $post['aweme_info']['statistics']['collect_count'],
                            'download_count' => $post['aweme_info']['statistics']['download_count'],
                        ]
                    );
                }
            } else {
                $this->error('Failed to fetch data for keyword: ' . $keyword);
            }
        }

        $this->info('Postings updated successfully for interval: ' . $interval);
    }
}
