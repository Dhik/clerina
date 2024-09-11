<?php

namespace App\Domain\Campaign\DAL\CampaignContent;

use App\Domain\Campaign\Enums\OfferEnum;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Models\Offer;
use Carbon\Carbon;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CampaignContentDAL implements CampaignContentDALInterface
{
    public function __construct(
        protected CampaignContent $campaignContent,
        protected Offer $offer
    ) {
    }

    /**
     * Return campaign content datatable
     */
    public function getCampaignContentDatatable(int $campaignId): Builder
    {
        return $this->campaignContent->query()
            ->where('campaign_contents.campaign_id', $campaignId)
            ->with(['keyOpinionLeader', 'createdBy', 'campaign']);
    }

    /**
     * Get list approved KOL on campaign
     */
    public function getCampaignKOL(int $campaignId, ?string $search): ?Collection
    {
        $kol = $this->offer
            ->select('key_opinion_leader_id', 'campaign_id') // Include all non-aggregated columns in the GROUP BY clause
            ->selectRaw('SUM(acc_slot) as total_acc_slot')
            ->with(['keyOpinionLeader' => function ($query) {
                $query->select('id', 'channel', 'username');
            }])
            ->whereHas('keyOpinionLeader', function ($query) use ($search) {
                $query->where('username', 'like', '%' . $search . '%');
            })
            ->where('campaign_id', $campaignId)
            ->where('status', OfferEnum::Approved)
            ->groupBy('key_opinion_leader_id', 'campaign_id') // Group by all non-aggregated columns
            ->limit(50)
            ->get();

        return $kol;
    }

    /**
     * Count user slot KOL on Campaign
     */
    public function countUsedSlot(int $campaignId, int $kolId): int
    {
        return $this->campaignContent
            ->where('campaign_id', $campaignId)
            ->where('key_opinion_leader_id', $kolId)
            ->count();
    }

    /**
     * Create campaign content
     */
    public function storeCampaignContent(array $data): CampaignContent
    {
        return $this->campaignContent->create($data);
    }

    /**
     * Update campaign content
     */
    public function updateCampaignContent(CampaignContent $campaignContent, array $data): CampaignContent
    {
        $campaignContent->update($data);
        return $campaignContent;
    }

    /**
     * Update upload date
     */
    public function updateUploadDate(int $campaignContentId, string $date): void
    {
        $content = CampaignContent::where('id', $campaignContentId)->withoutGlobalScopes()->first();
        if ($content->channel == 'youtube_video') {
            // Convert the ISO 8601 date string to a Carbon instance
            $content->upload_date = $date ? Carbon::parse($date) : null;
            $content->save(); // Use save() to update the model
        }
        else{
            $content->upload_date = Carbon::createFromTimestamp($date);
            $content->update();
        }
    }

    /**
     * Delete campaign content and statistic
     */
    public function deleteCampaignContent(CampaignContent $campaignContent): void
    {
        $campaignContent->delete();
    }

    public function getCampaignContentDataTableForRefresh(int $campaignId): Collection
    {
        return $this->campaignContent->select('id', 'username', 'task_name', 'channel', 'product', 'link')
            ->where('campaign_id', $campaignId)
            ->get();
    }

}
