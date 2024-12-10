<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\BLL\Campaign\CampaignBLLInterface;
use App\Domain\Campaign\Enums\CampaignContentEnum;
use App\Domain\Campaign\Enums\OfferEnum;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\Statistic;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Requests\CampaignRequest;
use App\Domain\Campaign\Service\StatisticCardService;
use App\Http\Controllers\Controller;
use Exception;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use App\Domain\Campaign\Models\Budget;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


class CampaignController extends Controller
{
    public function __construct(
        protected CampaignBLLInterface $campaignBLL,
        protected StatisticCardService $cardService
    ) {}

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewCampaign', Campaign::class);

        $query = $this->campaignBLL->getCampaignDataTable($request);

        if ($request->has('filterMonth')) {
            $month = $request->input('filterMonth');
            $query->whereMonth('start_date', '=', date('m', strtotime($month)))
                ->whereYear('start_date', '=', date('Y', strtotime($month)));
        }

        if ($request->has('filterDates')) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->startOfDay();
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->endOfDay();

            $query->with(['statistics' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            }]);
        } else {
            $query->with(['statistics' => function ($q) {
                $latestDate = Statistic::max('date');
                $q->whereDate('date', $latestDate);
            }]);
        }

        return DataTables::of($query)
            ->addColumn('created_by_name', function ($row) {
                return $row->createdBy->name;
            })
            ->addColumn('engagement_rate', function ($row) {
                $likes = $row->statistics->sum('like') ?? $row->like;
                $comments = $row->statistics->sum('comment') ?? $row->comment;
                $views = $row->statistics->sum('view') ?? $row->view;

                if ($views > 0) {
                    return round(($likes + $comments) / $views * 100, 2);
                }
                return 0;
            })
            ->addColumn('view', function ($row) use ($request) {
                if ($request->has('filterDates')) {
                    [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
                    $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->endOfDay();

                    $sumStartDate = $row->statistics->where('date', $startDate->toDateString())->sum('view');
                    $sumEndDate = $row->statistics->where('date', $endDate->toDateString())->sum('view');

                    return $sumEndDate - $sumStartDate;
                }

                return $row->statistics->sum('view'); // Default to total view if no filterDates
            })
            ->addColumn('like', function ($row) use ($request) {
                if ($request->has('filterDates')) {
                    [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
                    $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->endOfDay();

                    $sumStartDateLikes = $row->statistics->where('date', $startDate->toDateString())->sum('like');
                    $sumEndDateLikes = $row->statistics->where('date', $endDate->toDateString())->sum('like');

                    return $sumEndDateLikes - $sumStartDateLikes;
                }

                return $row->statistics->sum('like'); // Default to total likes if no filterDates
            })
            ->addColumn('comment', function ($row) use ($request) {
                if ($request->has('filterDates')) {
                    [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
                    $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->endOfDay();

                    $sumStartDateComments = $row->statistics->where('date', $startDate->toDateString())->sum('comment');
                    $sumEndDateComments = $row->statistics->where('date', $endDate->toDateString())->sum('comment');

                    return $sumEndDateComments - $sumStartDateComments;
                }

                return $row->statistics->sum('comment'); // Default to total comments if no filterDates
            })
            ->addColumn('actions', function ($row) {
                $actions = '<a href="' . route('campaign.show', $row->id) . '" class="btn btn-success btn-xs">
                            <i class="fas fa-eye"></i>
                        </a>';

                if (Gate::allows('UpdateCampaign', $row)) {
                    $actions .= ' <a href="' . route('campaign.edit', $row->id) . '" class="btn btn-primary btn-xs">
                                <i class="fas fa-pencil-alt"></i>
                            </a>';
                    $actions .= ' <a href="' . route('campaign.refresh', $row->id) . '" class="btn btn-warning btn-xs">
                                <i class="fas fa-sync-alt"></i>
                            </a>';
                }

                if (Gate::allows('deleteCampaign', $row)) {
                    $actions .= ' <button class="btn btn-danger btn-xs deleteButton" data-id="' . $row->id . '">
                                <i class="fas fa-trash-alt"></i>
                            </button>';
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
        $negotiates = OfferEnum::Negotiation;
        $statuses = OfferEnum::Status;
        $platforms = CampaignContentEnum::Platform;

        $usernames = CampaignContent::where('tenant_id', Auth::user()->current_tenant_id)
            ->distinct()
            ->pluck('username');

        return view('admin.campaign.show', compact('campaign', 'negotiates', 'statuses', 'platforms', 'usernames'));
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

        try {
            // Delete related campaign contents first
            CampaignContent::where('campaign_id', $campaign->id)->delete();

            // Then delete the campaign itself
            $this->campaignBLL->deleteCampaign($campaign);

            return response()->json(['message' => trans('messages.success_delete')]);
        } catch (Exception $e) {
            return response()->json(['message' => trans('messages.campaign_failed_delete')], 500);
        }
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
    public function bulkRefresh(): RedirectResponse
    {
        $currentMonth = now()->format('Y-m'); // Get the current month in 'YYYY-MM' format

        $campaigns = Campaign::where('created_at', 'like', "$currentMonth%")->get();
        // $campaigns = Campaign::all();

        foreach ($campaigns as $campaign) {
            $this->cardService->recapStatisticCampaign($campaign->id);
        }

        return redirect()
            ->route('campaign.index')
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_bulk_update', ['model' => trans('labels.campaign')]),
            ]);
    }

    public function refreshAllCampaigns(): RedirectResponse
    {
        $campaigns = Campaign::all(); // Fetch all campaigns

        foreach ($campaigns as $campaign) {
            $this->cardService->recapStatisticCampaign($campaign->id);
        }

        return redirect()
            ->route('campaign.index')
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_refresh_all', ['model' => trans('labels.campaign')]),
            ]);
    }



    public function getCampaignSummary(Request $request): JsonResponse
    {
        $summary = $this->campaignBLL->getCampaignSummary($request, Auth::user()->current_tenant_id);
        return response()->json($summary);
    }

    public function getCampaignTotal(Request $request): JsonResponse
    {
        $summaryDatatable = $this->get($request);
        $datatableCollection = collect($summaryDatatable->getData()->data);
        $totalExpense = $datatableCollection->sum('total_expense');
        $totalContent = $datatableCollection->sum(function ($item) {
            return count($item->statistics);
        });
        $totalViews = $datatableCollection->sum('view');
        $cpm = $totalViews > 0 ? $totalExpense / ($totalViews / 1000) : 0;
        $averageEngagementRate = $datatableCollection->avg('engagement_rate');


        return response()->json([
            'total_expense' => $this->numberFormat($totalExpense),
            'total_content' => $this->numberFormat($totalContent),
            'cpm' => $this->numberFormat($cpm, 2),
            'views' => $this->numberFormat($totalViews),
            'engagement_rate' => $this->numberFormat($averageEngagementRate, 2) . '%'
        ]);
    }
    protected function numberFormat($number, $decimals = 0): string
    {
        return number_format($number, $decimals, '.', ',');
    }

    public function getCampaignsTitles()
    {
        $campaigns = Campaign::select('id', 'title')->get();
        return response()->json($campaigns);
    }

    public function listFiles()
    {
        try {
            $files = Storage::disk('nas')->files('/'); // Adjust the directory path if needed
            return response()->json($files);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function downloadVideo()
    {
        try {

            $tiktokUrl = 'https://vt.tiktok.com/ZSjBBReDk/';

            // First, get the webpage content
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->get($tiktokUrl);

            // Get redirect URL if it's a short URL
            if ($response->status() === 301 || $response->status() === 302) {
                $tiktokUrl = $response->header('Location');
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ])->get($tiktokUrl);
            }

            $html = $response->body();

            // Extract video URL using regex
            preg_match('/"downloadAddr":"([^"]+)"/', $html, $matches);

            if (empty($matches[1])) {
                preg_match('/"playAddr":"([^"]+)"/', $html, $matches);
            }

            if (empty($matches[1])) {
                return response()->json([
                    'error' => 'Could not find video URL'
                ], 404);
            }

            // Properly decode the URL
            $videoUrl = json_decode('"' . $matches[1] . '"'); // This will handle \u0026 and other escaped characters

            if (!$videoUrl) {
                return response()->json([
                    'error' => 'Invalid video URL encoding'
                ], 400);
            }

            // Clean and validate the URL
            $videoUrl = $this->sanitizeUrl($videoUrl);

            if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'error' => 'Invalid video URL format'
                ], 400);
            }

            // Download the video content
            $videoResponse = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Referer' => 'https://www.tiktok.com/',
                'Range' => 'bytes=0-'
            ])->get($videoUrl);

            if ($videoResponse->failed()) {
                return response()->json([
                    'error' => 'Failed to download video: ' . $videoResponse->status()
                ], 500);
            }

            $videoContent = $videoResponse->body();

            // Verify we actually got video content
            if (strlen($videoContent) < 1024) { // Less than 1KB is probably an error
                return response()->json([
                    'error' => 'Retrieved content is too small to be a video'
                ], 500);
            }

            // Generate a filename based on timestamp
            $filename = 'tiktok_' . time() . '.mp4';

            // Return video as download
            return response($videoContent)
                ->header('Content-Type', 'video/mp4')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while downloading the video: ' . $e->getMessage()
            ], 500);
        }
    }

    private function sanitizeUrl($url)
    {
        // Remove any escaped unicode sequences
        $url = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }, $url);

        // Convert backslashes to forward slashes
        $url = str_replace('\\/', '/', $url);

        // Ensure proper URL encoding
        $parts = parse_url($url);
        if ($parts) {
            // Rebuild the URL with properly encoded parts
            $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : 'https://';
            $host = $parts['host'] ?? '';
            $path = isset($parts['path']) ? rawurlencode(ltrim($parts['path'], '/')) : '';
            $path = str_replace('%2F', '/', $path); // Keep slashes readable
            $query = isset($parts['query']) ? '?' . $parts['query'] : '';

            $url = $scheme . $host . '/' . $path . $query;
        }

        return $url;
    }

    private function debugUrl($url)
    {
        \Log::info('Processing URL: ' . $url);
        $parts = parse_url($url);
        \Log::info('URL Parts:', $parts ?: ['parse_url_failed' => true]);
        return $url;
    }
}
