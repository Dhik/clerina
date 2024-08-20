<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\BLL\CampaignContent\CampaignContentBLLInterface;
use App\Domain\Campaign\Enums\CampaignContentEnum;
use App\Domain\Campaign\Exports\CampaignContentExport;
use App\Domain\Campaign\Exports\CampaignContentTemplateExport;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Requests\CampaignContentRequest;
use App\Domain\Campaign\Requests\CampaignUpdateContentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;

class CampaignContentController extends Controller
{
    public function __construct(protected CampaignContentBLLInterface $campaignContentBLL)
    {
    }

    public function statistics(Campaign $campaign): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|Factory|View|Application
    {
        $this->authorize('viewCampaignContent', CampaignContent::class);

        $platforms = CampaignContentEnum::Platform;

        return view('admin.campaign.content.statistics', compact(
            'campaign', 'platforms'
        ));
    }

    public function selectApprovedInfluencer(int $campaignId, Request $request): JsonResponse
    {
        $this->authorize('viewCampaignContent', CampaignContent::class);

        return response()->json($this->campaignContentBLL->getApprovedKOL($campaignId, $request->input('search')));
    }

    /**
     * Return campaign content datatable
     */
    public function getCampaignContentDataTable(int $campaignId, Request $request): JsonResponse
    {
        $this->authorize('viewCampaignContent', CampaignContent::class);

        $query = $this->campaignContentBLL->getCampaignContentDataTable($campaignId, $request);

        return DataTables::of($query)
            ->addColumn('created_by_name', function ($row) {
                return $row->createdBy->name;
            })
            ->addColumn('key_opinion_leader_username', function ($row) {
                return $row->keyOpinionLeader->username;
            })
            ->addColumn('like', function($row) {
                if (!empty($row->latestStatistic->like)) {
                    $result = $row->latestStatistic->like < 0 ? abs($row->latestStatistic->like) : $row->latestStatistic->like;
                    return $this->numberFormatShort($result);
                }

                return 0;
            })
            ->addColumn('comment', function($row) {
                $result = $row->latestStatistic->comment ?? 0;
                return $this->numberFormatShort($result);
            })
            ->addColumn('view', function($row) {
                $result = $row->latestStatistic->view ?? 0;
                return $this->numberFormatShort($result);
            })
            ->addColumn('cpm', function ($row) {
                $cpm = $row->latestStatistic->cpm ?? 0;
                return number_format($cpm, '2', ',', '.');
            })
            ->addColumn('rate_card_formatted', function ($row) {
                return number_format($row->rate_card, '0', ',', '.');
            })
            ->addColumn('additional_info', function ($row) {
                return $this->additionalInfo($row);
            })
            ->addColumn('actions', function ($row) {
                return $this->actionsHtml($row);
            })
            ->rawColumns(['actions', 'additional_info'])
            ->toJson();
    }

    protected function actionsHtml($row): string
    {
        $actionsHtml = '
            <div class="btn-group">
                <button class="btn btn-info btn-sm btnDetail">'. trans("labels.detail") .'</button>
                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu" role="menu" style="">';

        if (in_array($row->channel, [CampaignContentEnum::InstagramFeed, CampaignContentEnum::TiktokVideo, CampaignContentEnum::TwitterPost])) {
            $actionsHtml .= '
            <button class="dropdown-item btnRefresh">
                '. trans("labels.refresh").'
            </button>';
        }

        if (Gate::allows('updateCampaign', $row->campaign)) {
            $actionsHtml .= '
                <button class="dropdown-item btnUpdateContent">'.trans("labels.update").'</button>';
        }

        if (Gate::allows('updateCampaign', $row->campaign) && !in_array($row->channel, [CampaignContentEnum::InstagramFeed, CampaignContentEnum::TiktokVideo, CampaignContentEnum::TwitterPost])) {
            $actionsHtml .= '
                <button class="dropdown-item btnStatistic">'. trans("labels.manual") .' '. trans("labels.data") .'</button>';
        }

        if (Gate::allows('deleteCampaign', $row->campaign)) {
            $actionsHtml .= '
                <div class="dropdown-divider"></div>
                <a class="dropdown-item btnDeleteContent" href="#">' . trans('labels.delete') . '</a>
            ';
        }

        return $actionsHtml;
    }

    protected function additionalInfo($row): string
    {
        $infoHtml = '<a href="#" class="btn btn-link btn-sm btnFyp" data-toggle="tooltip" data-placement="top" title="FYP">
                        <i class="far fa-star' . ($row->is_fyp ? ' text-warning' : ' text-black-50') . '"></i>
                    </a>';

        $infoHtml .= '<a href="#" class="btn btn-link btn-sm btnDeliver" data-toggle="tooltip" data-placement="top" title="Barang dikirim">
                        <i class="fab fa-product-hunt' . ($row->is_product_deliver ? ' text-warning' : ' text-black-50') . '"></i>
                    </a>';

        $infoHtml .= '<a href="#" class="btn btn-link btn-sm btnPay" data-toggle="tooltip" data-placement="top" title="Pembayaran">
                        <i class="far fa-credit-card' . ($row->is_paid ? ' text-warning' : ' text-black-50') . '"></i>
                    </a>';

        if (!empty($row->link)) {
            $infoHtml .= '<a href="#" class="btn btn-link btn-sm btnCopy" data-toggle="tooltip" data-placement="top" title="Copy content link">
                        <i class="far fa-copy text-primary"></i>
                    </a>';
        } else {
            $infoHtml .= '<a href="#" class="btn btn-link btn-sm">
                        <i class="far fa-copy text-black-50"></i>
                    </a>';
        }

        return $infoHtml;
    }

    public function getDetail(CampaignContent $campaignContent)
    {

    }

    /**
     * Store new campaign content
     */
    public function store(int $campaignId, CampaignContentRequest $request): JsonResponse
    {
        $this->authorize('CreateCampaignContent', CampaignContent::class);

        return response()->json(
            $this->campaignContentBLL->storeCampaignContent($campaignId, $request)
        );
    }

    /**
     * Update campaign content
     */
    public function update(CampaignContent $campaignContent, CampaignUpdateContentRequest $request): JsonResponse
    {
        $this->authorize('updateCampaignContent', CampaignContent::class);

        return response()->json(
            $this->campaignContentBLL->updateCampaignContent($campaignContent, $request)
        );
    }

    /**
     * Update FYP campaign content
     */
    public function updateFyp(CampaignContent $campaignContent): JsonResponse
    {
        $this->authorize('updateCampaignContent', CampaignContent::class);

        return response()->json(
            $this->campaignContentBLL->updateFyp($campaignContent)
        );
    }

    /**
     * Update Deliver campaign content
     */
    public function updateDeliver(CampaignContent $campaignContent): JsonResponse
    {
        $this->authorize('updateCampaignContent', CampaignContent::class);

        return response()->json(
            $this->campaignContentBLL->updateDeliver($campaignContent)
        );
    }

    /**
     * Update Payment campaign content
     */
    public function updatePayment(CampaignContent $campaignContent): JsonResponse
    {
        $this->authorize('updateCampaignContent', CampaignContent::class);

        return response()->json(
            $this->campaignContentBLL->updatePay($campaignContent)
        );
    }

    public function import(Campaign $campaign, Request $request)
    {
        $this->authorize('updateCampaign', $campaign);

        $data = $this->campaignContentBLL->importContent(
            $request,
            Auth::user()->current_tenant_id,
            $campaign
        );

        return response()->json($data);
    }

    /**
     * Template import order
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $this->authorize('ViewCampaignContent', CampaignContent::class);

        return Excel::download(new CampaignContentTemplateExport(), 'Campaign Template.xlsx');
    }

    /**
     * Export Content
     */
    public function export(Campaign $campaign): Response|BinaryFileResponse
    {
        $this->authorize('ViewCampaignContent', CampaignContent::class);

        return (new CampaignContentExport())
            ->forCampaign($campaign->id)
            ->download($campaign->title .' offer.xlsx');
    }

    protected  function numberFormatShort($n, $precision = 1): string
    {
        if ($n < 900) {
            // 0 - 900
            $n_format = number_format($n, $precision);
            $suffix = '';
        } elseif ($n < 900000) {
            // 0.9k-850k
            $n_format = number_format($n * 0.001, $precision);
            $suffix = 'K';
        } elseif ($n < 900000000) {
            // 0.9m-850m
            $n_format = number_format($n * 0.000001, $precision);
            $suffix = 'M';
        } elseif ($n < 900000000000) {
            // 0.9b-850b
            $n_format = number_format($n * 0.000000001, $precision);
            $suffix = 'B';
        } else {
            // 0.9t+
            $n_format = number_format($n * 0.000000000001, $precision);
            $suffix = 'T';
        }

        // Remove unnecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
        // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if ($precision > 0) {
            $dotZero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotZero, '', $n_format);
        }

        return $n_format . $suffix;
    }

    /**
     * Delete campaign content
     */
    public function destroy(CampaignContent $campaignContent): JsonResponse
    {
        $campaignContent = $campaignContent->load('campaign');

        $this->authorize('deleteCampaign', $campaignContent->campaign);

        $this->campaignContentBLL->deleteCampaignContent($campaignContent);

        return response()->json(['message' => trans('messages.success_delete')]);
    }

}
