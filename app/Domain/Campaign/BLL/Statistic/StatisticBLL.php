<?php

namespace App\Domain\Campaign\BLL\Statistic;

use App\Domain\Campaign\DAL\CampaignContent\CampaignContentDALInterface;
use App\Domain\Campaign\DAL\Statistic\StatisticDALInterface;
use App\Domain\Campaign\Enums\CampaignContentEnum;
use App\Domain\Campaign\Models\Statistic;
use App\Domain\Campaign\Service\InstagramScrapperService;
use App\Domain\Campaign\Service\TiktokScrapperService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticBLL implements StatisticBLLInterface
{
    public function __construct(
        protected CampaignContentDALInterface $campaignContentDAL,
        protected StatisticDALInterface $statisticDAL,
        protected InstagramScrapperService $instagramScrapperService,
        protected TiktokScrapperService $tiktokScrapperService
    ) {
    }

    /**
     * Update or crate statistic
     */
    public function store(
        int $campaignId,
        int $campaignContentId,
        string $date,
        ?int $like,
        ?int $view,
        ?int $comment,
        int $tenantId,
        ?string $uploadDate = null,
        ?int $rateCard = 0
    ): Statistic {
        $data = [
            'campaign_id' => $campaignId,
            'campaign_content_id' => $campaignContentId,
            'date' => Carbon::parse($date)->format('Y-m-d'),
            'tenant_id' => $tenantId
        ];

        // Assign non-null values to the data array
        if (!is_null($like)) {
            $data['like'] = $like;
        }

        if (!is_null($view)) {
            $data['view'] = $view;
        }

        if (!is_null($comment)) {
            $data['comment'] = $comment;
        }

        $data['cpm'] = $data['view'] === 0 ? 0 : ($rateCard / $data['view']) * 1000;

        $statistic = $this->statisticDAL->store($data);

        if (!is_null($uploadDate)) {
            $this->campaignContentDAL->updateUploadDate($campaignContentId, $uploadDate);
        }

        return $statistic;
    }

    public function scrapData(
        int $campaignId,
        int $campaignContentId,
        string $channel,
        string $link,
        int $tenantId,
        int $rateCard
    ): Statistic|bool {
        if (!empty($link)) {
            $data = [];

            if ($channel === CampaignContentEnum::InstagramFeed) {
                $data = $this->instagramScrapperService->getPostInfo($link);
            } elseif ($channel === CampaignContentEnum::TiktokVideo) {
                $data = $this->tiktokScrapperService->getData($link);
            }

            if (empty($data)) {
                return false;
            }

            return $this->store(
                $campaignId,
                $campaignContentId,
                Carbon::now(),
                $data['like'],
                $data['view'],
                $data['comment'],
                $tenantId,
                $data['upload_date'],
                $rateCard
            );
        }

        return false;
    }

    /**
     * Get data for chart
     */
    public function getChartDataCampaign(int $campaignId, Request $request)
    {
        $query = $this->statisticDAL->getChartDataCampaign($campaignId);

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $query->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Get data for chart detail content
     */
    public function getChartDataCampaignContent(int $campaignContentId)
    {
        return $this->statisticDAL->getChartDataCampaignContent($campaignContentId);
    }
}
