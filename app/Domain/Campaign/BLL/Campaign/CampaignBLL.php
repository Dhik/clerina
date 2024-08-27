<?php

namespace App\Domain\Campaign\BLL\Campaign;

use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Requests\CampaignRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\Campaign\DAL\Campaign\CampaignDALInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Utilities\Request;

class CampaignBLL extends BaseBLL implements CampaignBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(protected CampaignDALInterface $campaignDAL)
    {
    }

    /**
     * Get campaign list datatable
     */
    public function getCampaignDataTable(Request $request): Builder
    {
        $query = $this->campaignDAL->getCampaignDataTable();

        if ($request->input('search.value')) {
            $searchTerms = explode(' ', strtolower($request->input('search.value')));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%$term%"]);
                }
            });
        }

        return $query;
    }



    /**
     * Create new campaign
     */
    public function storeCampaign(CampaignRequest $request): Campaign
    {
        $period = $this->destructPeriod($request->period);

        $data = [
            'title' => $request->input('title'),
            'start_date' => $period->startDate,
            'end_date' => $period->endDate,
            'description' => $request->input('description'),
            'created_by' => Auth::user()->id,
            'id_budget' => $request->input('id_budget'),
        ];

        return $this->campaignDAL->storeCampaign($data);
    }

    /**
     * Update campaign
     */
    public function updateCampaign(Campaign $campaign, CampaignRequest $request): Campaign
    {
        $period = $this->destructPeriod($request->period);

        $data = [
            'title' => $request->input('title'),
            'start_date' => $period->startDate,
            'end_date' => $period->endDate,
            'description' => $request->input('description')
        ];

        return $this->campaignDAL->updateCampaign($campaign, $data);
    }

    /**
     * Convert period to start and end date
     */
    protected function destructPeriod(string $period): object
    {
        [$startDateString, $endDateString] = explode(' - ', $period);

        return (object) [
            'startDate' => Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d'),
            'endDate' => Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d')
        ];
    }

    /**
     * Delete campaign
     */
    public function deleteCampaign(Campaign $campaign): bool
    {
        $checkOffer = $this->campaignDAL->checkOffer($campaign);

        if (! empty($checkOffer)) {
            return false;
        }

        $checkCampaignContent = $this->campaignDAL->checkCampaignContent($campaign);

        if (! empty($checkCampaignContent)) {
            return false;
        }

        $this->campaignDAL->deleteCampaign($campaign);

        return true;
    }
    public function getCampaignSummary(Request $request, int $tenantId): array
{
    $startDateString = Carbon::now()->startOfMonth()->format('Y-m-d');
    $endDateString = Carbon::now()->endOfMonth()->format('Y-m-d');

    if ($request->input('filterMonth') && $request->input('filterMonth') !== '') {
        $startDateString = Carbon::createFromFormat('Y-m', $request->input('filterMonth'))->startOfMonth()->format('Y-m-d');
        $endDateString = Carbon::createFromFormat('Y-m', $request->input('filterMonth'))->endOfMonth()->format('Y-m-d');
    } else {
        // Remove date filter to show all campaigns
        $startDateString = null;
        $endDateString = null;
    }

    $campaigns = $this->campaignDAL->getCampaignsByDateRange($startDateString, $endDateString, $tenantId);

    if ($request->input('search')) {
        $searchTerms = explode(' ', strtolower($request->input('search')));
        
        $campaigns = $campaigns->filter(function ($campaign) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                if (stripos($campaign->title, $term) === false) {
                    return false;
                }
            }
            return true;
        });
    }

    $groupedCampaigns = $campaigns->groupBy(function ($campaign) {
        return strtolower($campaign->title);
    });

    $totalExpense = 0;
    $totalContent = 0;
    $totalViews = 0;

    foreach ($groupedCampaigns as $group) {
        $totalExpense += $group->sum('total_expense');
        $totalContent += $group->sum('total_content');
        $totalViews += $group->sum('view');
    }

    $cpm = $totalViews > 0 ? $totalExpense / ($totalViews / 1000) : 0;

    return [
        'total_expense' => $this->numberFormat($totalExpense),
        'total_content' => $this->numberFormat($totalContent),
        'cpm' => $this->numberFormat($cpm, 2),
        'views' => $this->numberFormat($totalViews),
    ];
}





    protected function numberFormat($number, $decimals = 0): string
    {
        return number_format($number, $decimals, '.', ',');
    }
}
