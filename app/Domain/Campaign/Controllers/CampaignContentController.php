<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\BLL\CampaignContent\CampaignContentBLLInterface;
use App\Domain\Campaign\Enums\CampaignContentEnum;
use App\Domain\Campaign\Exports\CampaignContentExport;
use App\Domain\Campaign\Exports\CampaignContentTemplateExport;
use App\Domain\Campaign\Exports\CampaignContentTemplateKOLExport;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Talent\Models\TalentContent;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Models\Statistic;
use App\Domain\Campaign\Requests\CampaignContentRequest;
use App\Domain\Campaign\Requests\CampaignUpdateContentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Http;
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
    public function __construct(protected CampaignContentBLLInterface $campaignContentBLL) {}

    public function statistics(Campaign $campaign): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|Factory|View|Application
    {
        $this->authorize('viewCampaignContent', CampaignContent::class);

        $platforms = CampaignContentEnum::Platform;

        return view('admin.campaign.content.statistics', compact(
            'campaign',
            'platforms'
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
            ->addColumn('like', function ($row) {
                if (!empty($row->latestStatistic->like)) {
                    $result = $row->latestStatistic->like < 0 ? abs($row->latestStatistic->like) : $row->latestStatistic->like;
                    return $this->numberFormatShort($result);
                }

                return 0;
            })
            ->addColumn('comment', function ($row) {
                $result = $row->latestStatistic->comment ?? 0;
                return $this->numberFormatShort($result);
            })
            ->addColumn('view', function ($row) {
                $result = $row->latestStatistic->view ?? 0;
                return $this->numberFormatShort($result);
            })
            ->addColumn('engagement_rate', function ($row) {
                $likes = $row->latestStatistic->like ?? 0;
                $comments = $row->latestStatistic->comment ?? 0;
                $views = $row->latestStatistic->view ?? 0;
                $engagementRate = $views > 0 ? (($likes + $comments) / $views) * 100 : 0;

                return number_format($engagementRate, 2) . '%'; // Format as percentage
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

    public function getCampaignContentJson(int $campaignId, Request $request): JsonResponse
    {
        $this->authorize('viewCampaignContent', CampaignContent::class);
        $query = $this->campaignContentBLL->getCampaignContentDataTable($campaignId, $request)->orderBy('upload_date', 'desc');
        $campaignContents = $query->get()->map(function ($row) {

            $baseUsername = preg_replace('/\s*\(.*?\)\s*/', '', $row->username);
            $keyOpinionLeader = KeyOpinionLeader::where('username', $baseUsername)->first();
            $followers = $keyOpinionLeader->followers ?? 0;

            $likes = $row->latestStatistic->like ?? 0;
            $comments = $row->latestStatistic->comment ?? 0;
            $views = $row->latestStatistic->view ?? 0;
            $engagementRate = $views > 0 ? (($likes + $comments) / $views) * 100 : 0;

            if ($followers >= 1000 && $followers < 10000) {
                $tiering = "<button class='btn btn-sm bg-info'>
                                Nano
                            </button>";
                $er_top = 0.1;
                $er_bottom = 0.04;
                $cpm_target = 35000;
            } elseif ($followers >= 10000 && $followers < 50000) {
                $tiering = "<button class='btn btn-sm bg-purple'>
                                Micro
                            </button>";
                $er_top = 0.05;
                $er_bottom = 0.02;
                $cpm_target = 35000;
            } elseif ($followers >= 50000 && $followers < 250000) {
                $tiering = "<button class='btn btn-sm bg-maroon'>
                                Mid-Tier
                            </button>";
                $er_top = 0.03;
                $er_bottom = 0.015;
                $cpm_target = 25000;
            } elseif ($followers >= 250000 && $followers < 1000000) {
                $tiering = "<button class='btn btn-sm bg-success'>
                                Macro TOFU
                            </button>";
                $er_top = 0.025;
                $er_bottom = 0.01;
                $cpm_target = 10000;
            } elseif ($followers >= 1000000 && $followers < 2000000) {
                $tiering = "<button class='btn btn-sm bg-teal'>
                                Mega-TOFU
                            </button>";
                $er_top = 0.02;
                $er_bottom = 0.01;
                $cpm_target = 10000;
            } elseif ($followers >= 2000000) {
                $tiering = "<button class='btn btn-sm bg-pink'>
                                Mega-MOFU
                            </button>";
                $er_top = 0.02;
                $er_bottom = 0.01;
                $cpm_target = 35000;
            } else {
                $tiering = "Unknown";
                $er_top = null;
                $er_bottom = null;
                $cpm_target = null;
            }

            // Return the transformed row data
            return [
                'id' => $row->id,
                'channel' => $row->channel,
                'product' => $row->product,
                'task' => $row->task_name,
                'is_fyp' => $row->is_fyp,
                'username' => $row->username,
                'upload_date' => $row->upload_date,
                'kode_ads' => $row->kode_ads,
                'rate_card' => $row->rate_card,
                'is_product_deliver' => $row->is_product_deliver,
                'is_paid' => $row->is_paid,
                'created_by_name' => $row->createdBy->name ?? 'N/A',
                'key_opinion_leader_username' => $row->keyOpinionLeader->username ?? 'N/A',
                'kol_followers' => $followers,
                'like' => !empty($row->latestStatistic->like) ? abs($row->latestStatistic->like) : 0,
                'comment' => $row->latestStatistic->comment ?? 0,
                'view' => $row->latestStatistic->view ?? 0,
                'cpm' => number_format($row->latestStatistic->cpm ?? 0, 2, ',', '.'),
                'rate_card_formatted' => number_format($row->rate_card, 0, ',', '.'),
                'link' => $row->link ?? 'N/A',
                'additional_info' => $this->additionalInfo($row),
                'actions' => $this->actionsHtml($row),
                'engagement_rate' => number_format($engagementRate, 2) . '%',
                'tiering' => $tiering,
                'er_top' => $er_top,
                'er_bottom' => $er_bottom,
                'cpm_target' => $cpm_target,
            ];
        });

        // Return the campaign content data in JSON format
        return response()->json([
            'data' => $campaignContents
        ]);
    }



    protected function actionsHtml($row): string
    {
        $actionsHtml = '
            <div class="btn-group">
                <button class="btn btn-info btn-sm btnDetail">' . trans("labels.detail") . '</button>
                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu" role="menu" style="">';

        if (in_array($row->channel, [CampaignContentEnum::InstagramFeed, CampaignContentEnum::TiktokVideo, CampaignContentEnum::TwitterPost, CampaignContentEnum::YoutubeVideo, CampaignContentEnum::ShopeeVideo])) {
            $actionsHtml .= '
            <button class="dropdown-item btnRefresh">
                ' . trans("labels.refresh") . '
            </button>';
        }

        if (Gate::allows('updateCampaign', $row->campaign)) {
            $actionsHtml .= '
                <button class="dropdown-item btnUpdateContent">' . trans("labels.update") . '</button>';
        }

        if (Gate::allows('updateCampaign', $row->campaign) && !in_array($row->channel, [CampaignContentEnum::InstagramFeed, CampaignContentEnum::TiktokVideo, CampaignContentEnum::TwitterPost, CampaignContentEnum::YoutubeVideo, CampaignContentEnum::ShopeeVideo])) {
            $actionsHtml .= '
                <button class="dropdown-item btnStatistic">' . trans("labels.manual") . ' ' . trans("labels.data") . '</button>';
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

        if (!empty($row->kode_ads)) {
            $infoHtml .= '<a href="#" class="btn btn-link btn-sm btnKode" data-toggle="tooltip" data-placement="top" title="Copy Ads Code">
                        <i class="far fa-bell text-primary"></i>
                    </a>';
        } else {
            $infoHtml .= '<a href="#" class="btn btn-link btn-sm">
                        <i class="far fa-bell text-black-50"></i>
                    </a>';
        }

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

    public function import_from_KOL(Campaign $campaign, Request $request)
    {
        $this->authorize('updateCampaign', $campaign);

        $data = $this->campaignContentBLL->importContentKOL(
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
    public function downloadTemplateKOL(): BinaryFileResponse
    {
        $this->authorize('ViewCampaignContent', CampaignContent::class);
        return Excel::download(new CampaignContentTemplateKOLExport(), 'Campaign Template KOL.xlsx');
    }

    /**
     * Export Content
     */
    public function export(Campaign $campaign): Response|BinaryFileResponse
    {
        $this->authorize('ViewCampaignContent', CampaignContent::class);

        return (new CampaignContentExport())
            ->forCampaign($campaign->id)
            ->download($campaign->title . ' offer.xlsx');
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

        TalentContent::where('campaign_id', $campaignContent->campaign_id)
            ->where('upload_link', $campaignContent->link)
            ->first()
            ->delete();

        return response()->json(['message' => trans('messages.success_delete')]);
    }
    public function getCampaignContentDataTableForRefresh(int $campaignId): JsonResponse
    {
        $this->authorize('viewCampaignContent', CampaignContent::class);

        $query = $this->campaignContentBLL->getCampaignContentDataTableForRefresh($campaignId);

        return response()->json($query);
    }
    public function getProductDataTable(): JsonResponse
    {
        $products = CampaignContent::select('product')->distinct()->get();

        return DataTables::of($products)
            ->addColumn('actions', function ($product) {
                if ($product->product) {
                    return '
                        <a href="' . route('campaignContent.showProductDetails', ['productName' => $product->product]) . '" class="btn btn-sm btn-primary">
                            View Details
                        </a>';
                } else {
                    return ''; // Return an empty string if the product is null
                }
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function showProductDetails(string $productName): View
    {
        $product = CampaignContent::where('product', $productName)->firstOrFail();
        return view('admin.campaign.product_details', ['product' => $product]);
    }


    public function getProductStatistics(string $productName)
    {
        $product = CampaignContent::where('product', $productName)->firstOrFail();
        $topViews = Statistic::join('campaign_contents', 'statistics.campaign_content_id', '=', 'campaign_contents.id')
            ->where('campaign_contents.product', $productName)
            ->selectRaw('campaign_contents.username, campaign_contents.product, SUM(statistics.view) AS total_views')
            ->groupBy('campaign_contents.username', 'campaign_contents.product')
            ->orderByDesc('total_views')
            ->take(5)
            ->get();

        $topLikes = Statistic::join('campaign_contents', 'statistics.campaign_content_id', '=', 'campaign_contents.id')
            ->where('campaign_contents.product', $productName)
            ->selectRaw('campaign_contents.username, campaign_contents.product, SUM(statistics.like) AS total_likes')
            ->groupBy('campaign_contents.username', 'campaign_contents.product')
            ->orderByDesc('total_likes')
            ->take(5)
            ->get();

        $topComments = Statistic::join('campaign_contents', 'statistics.campaign_content_id', '=', 'campaign_contents.id')
            ->where('campaign_contents.product', $productName)
            ->selectRaw('campaign_contents.username, campaign_contents.product, SUM(statistics.comment) AS total_comments')
            ->groupBy('campaign_contents.username', 'campaign_contents.product')
            ->orderByDesc('total_comments')
            ->take(5)
            ->get();

        $topEngagements = Statistic::join('campaign_contents', 'statistics.campaign_content_id', '=', 'campaign_contents.id')
            ->where('campaign_contents.product', $productName)
            ->selectRaw('campaign_contents.username, campaign_contents.product, SUM(statistics.engagement) AS total_engagement')
            ->groupBy('campaign_contents.username', 'campaign_contents.product')
            ->orderByDesc('total_engagement')
            ->take(5)
            ->get();

        // Calculate the overall totals for the product (not just from top 5)
        $totalViews = Statistic::join('campaign_contents', 'statistics.campaign_content_id', '=', 'campaign_contents.id')
            ->where('campaign_contents.product', $productName)
            ->sum('statistics.view');

        $totalLikes = Statistic::join('campaign_contents', 'statistics.campaign_content_id', '=', 'campaign_contents.id')
            ->where('campaign_contents.product', $productName)
            ->sum('statistics.like');

        $totalComments = Statistic::join('campaign_contents', 'statistics.campaign_content_id', '=', 'campaign_contents.id')
            ->where('campaign_contents.product', $productName)
            ->sum('statistics.comment');

        $totalInfluencers = CampaignContent::where('product', $productName)
            ->distinct('username')
            ->count('username');

        // Return the results as JSON
        return response()->json([
            'totalViews' => $totalViews,
            'totalLikes' => $totalLikes,
            'totalComments' => $totalComments,
            'totalInfluencers' => $totalInfluencers,
            'topEngagements' => $topEngagements,
            'topViews' => $topViews,
            'topLikes' => $topLikes,
            'topComments' => $topComments,
        ]);
    }
    public function showDistinctProducts(): View
    {
        return view('admin.campaign.products');
    }

    public function updateAllShopeeVideoLinks()
    {
        $campaignContents = CampaignContent::where('channel', 'shopee_video')
            ->whereNotNull('link')
            ->get();

        if ($campaignContents->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No campaigns with channel "shopee_video" found.'
            ]);
        }

        foreach ($campaignContents as $campaignContent) {
            $finalUrl = $this->extractVideoIdFromShortLink($campaignContent->link, $campaignContent->username);

            if ($finalUrl) {
                $campaignContent->link = $finalUrl;
                $campaignContent->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'All Shopee video links updated successfully!'
        ]);
    }

    private function extractVideoIdFromShortLink($shortUrl, $username)
    {
        try {
            $response = Http::get($shortUrl);
            $finalUrl = $response->effectiveUri();

            $parsedUrl = parse_url($finalUrl);
            parse_str($parsedUrl['query'] ?? '', $queryParams);

            $redirValue = $queryParams['redir'] ?? null;
            if ($redirValue) {
                $urlParts = explode('/', $redirValue);
                $videoId = explode('?', $urlParts[4])[0];

                $finalVideoUrl = "https://sv.shopee.co.id/web/@{$username}/video/{$videoId}";
                return $finalVideoUrl;
            }
        } catch (\Exception $e) {
            // Handle any exceptions (optional logging could be added here)
        }

        return null;
    }
}
