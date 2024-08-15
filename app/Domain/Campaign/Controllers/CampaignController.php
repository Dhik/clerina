<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\BLL\Campaign\CampaignBLLInterface;
use App\Domain\Campaign\Enums\CampaignContentEnum;
use App\Domain\Campaign\Enums\OfferEnum;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Requests\CampaignRequest;
use App\Domain\Campaign\Service\StatisticCardService;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use App\Domain\Campaign\Models\Budget;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignBLLInterface $campaignBLL,
        protected StatisticCardService $cardService
    ) {
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function get(): JsonResponse
    {
        $this->authorize('viewCampaign', Campaign::class);

        $query = $this->campaignBLL->getCampaignDataTable();

        return DataTables::of($query)
            ->addColumn('created_by_name', function ($row) {
                return $row->createdBy->name;
            })
            ->addColumn('actions', function ($row) {
                $actions = '<a href=' . route('campaign.show', $row->id) . ' class="btn btn-success btn-xs">
                        <i class="fas fa-eye"></i>
                    </a>';

                // Check if the user has the permission to edit campaigns
                if (Gate::allows('UpdateCampaign', $row)) {
                    $actions .= ' <a href=' . route('campaign.edit', $row->id) . ' class="btn btn-primary btn-xs">
                            <i class="fas fa-pencil-alt"></i>
                        </a>';

                    $actions .= ' <a href=' . route('campaign.refresh', $row->id) . ' class="btn btn-danger btn-xs">
                            <i class="fas fa-sync-alt"></i>
                        </a>';
                }

                return $actions;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Show index page campaign
     */
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewCampaign', Campaign::class);

        return view('admin.campaign.index');
    }

    /**
     * Create new campaign
     */
    public function create(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('createCampaign', Campaign::class);
        $budgets = Budget::all();

        return view('admin.campaign.create', compact('budgets'));
    }

    /**
     * Store campaign
     */
    public function store(CampaignRequest $request): RedirectResponse
    {
        $this->authorize('createCampaign', Campaign::class);

        $campaign = $this->campaignBLL->storeCampaign($request);

        return redirect()
            ->route('campaign.show', $campaign->id)
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_save', ['model' => trans('labels.campaign')]),
            ]);
    }

    /**
     * Show detail campaign
     */
    public function show(Campaign $campaign): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewCampaign', Campaign::class);

        $negotiates = OfferEnum::Negotiation;
        $statuses = OfferEnum::Status;
        $platforms = CampaignContentEnum::Platform;
        return view('admin.campaign.show', compact('campaign', 'negotiates', 'statuses', 'platforms'));
    }

    /**
     * Edit campaign
     */
    public function edit(Campaign $campaign): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('UpdateCampaign', $campaign);
        $budgets = Budget::all();

        return view('admin.campaign.edit', compact('campaign', 'budgets'));
    }

    /**
     * Update campaign
     */
    public function update(Campaign $campaign, CampaignRequest $request): RedirectResponse
    {
        $this->authorize('UpdateCampaign', $campaign);

        $campaign = $this->campaignBLL->updateCampaign($campaign, $request);

        return redirect()
            ->route('campaign.show', $campaign->id)
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_update', ['model' => trans('labels.campaign')]),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        $this->authorize('deleteCampaign', $campaign);

        $result = $this->campaignBLL->deleteCampaign($campaign);

        if (! $result) {
            return response()->json(['message' => trans('messages.campaign_failed_delete')], 422);
        }

        return response()->json(['message' => trans('messages.success_delete')]);
    }

    /**
     * Refresh statistic
     */
    public function refresh(Campaign $campaign): RedirectResponse
    {
        $this->authorize('UpdateCampaign', $campaign);

        $this->cardService->recapStatisticCampaign($campaign->id);

        return redirect()
            ->route('campaign.index')
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_update', ['model' => trans('labels.campaign')]),
            ]);
    }
}
