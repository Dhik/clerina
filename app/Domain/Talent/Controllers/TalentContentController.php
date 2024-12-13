<?php

namespace App\Domain\Talent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Talent\BLL\Talent\TalentBLLInterface;
use App\Domain\Talent\Models\TalentContent;
use App\Domain\Talent\Exports\TalentContentExport;
use App\Domain\Talent\Models\Approval;
use App\Domain\Talent\Models\Talent;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Talent\Requests\TalentContentRequest;
use Yajra\DataTables\Utilities\Request;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\DB;
use Auth;

/**
 */
class TalentContentController extends Controller
{
    protected $talentContentBLL;
    public function __construct(TalentBLLInterface $talentContentBLL)
    {
        $this->talentContentBLL = $talentContentBLL;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.talent_content.index');
    }
    protected function statusAndLinkHtml($talentContent): string
    {
        $uploadLinkButton = !empty($talentContent->upload_link)
            ? '<a href="' . $talentContent->upload_link . '" target="_blank" class="btn btn-light" data-toggle="tooltip" data-placement="top" title="View Upload">
                <i class="fas fa-link"></i>
            </a>'
            : '<span class="text-black-50">No Link</span>';

        $viewButton = '<button class="btn btn-light viewButton" 
            data-id="' . $talentContent->id . '" 
            data-toggle="modal" 
            data-target="#viewTalentContentModal" 
            data-placement="top" title="View">
            <i class="fas fa-eye"></i>
        </button>';

        return $uploadLinkButton . ' ' . $viewButton;
    }

    protected function doneHtml($talentContent): string
    {
        $doneButton = $talentContent->done
            ? '<button class="btn btn-light" data-toggle="tooltip" data-placement="top" title="Done">
                <i class="fas fa-check-circle text-success"></i>
            </button>'
            : '<button class="btn btn-light" data-toggle="tooltip" data-placement="top" title="Not Done">
                <i class="fas fa-times-circle text-secondary"></i>
            </button>';
        return $doneButton;
    }
    private function adjustSpentForTax($spent, $accountName)
    {
        $isPTorCV = \Illuminate\Support\Str::startsWith($accountName, ['PT', 'CV']);
        $pph = $isPTorCV ? $spent * 0.02 : $spent * 0.025;
        return $spent - $pph;
    }

    public function data(Request $request)
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        $talentContents = TalentContent::select([
            'talent_content.id',
            'talent_content.talent_id',
            'talent_content.dealing_upload_date',
            'talent_content.posting_date',
            'talent_content.done',
            'talent_content.upload_link',
            'talents.username',
            'talent_content.final_rate_card',
            'talent_content.is_refund',
            'talents.rate_final',
            'talents.slot_final',
            'talents.nama_rekening',
            'campaigns.title as campaign_title'
        ])
            ->join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->join('campaigns', 'talent_content.campaign_id', '=', 'campaigns.id')
            ->where('talents.tenant_id', $currentTenantId);

        if ($request->input('username')) {
            $talentContents->where('talents.username', $request->input('username'));
        }

        if ($request->filled('dateRange')) {
            $dates = explode(' - ', $request->input('dateRange'));
            $startDate = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();

            $talentContents->whereBetween('talent_content.posting_date', [$startDate, $endDate]);
        }

        if (!is_null($request->input('filterProduct'))) {
            $talentContents->where('talent_content.product', $request->input('filterProduct'));
        }

        if (! is_null($request->input('filterDealingDate'))) {
            $dates = explode(' - ', $request->input('filterDealingDate'));
            $startDate = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
            $talentContents->whereBetween('dealing_upload_date', [$startDate, $endDate]);
        }

        // Filter by posting_date
        if (! is_null($request->input('filterPostingDate'))) {
            $dates = explode(' - ', $request->input('filterPostingDate'));
            $startDate = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
            $talentContents->whereBetween('posting_date', [$startDate, $endDate]);
        }

        if (!is_null($request->input('filterDone'))) {
            $talentContents->where('talent_content.done', $request->input('filterDone'));
        }

        return DataTables::of($talentContents)
            ->addColumn('talent_should_get', function ($talentContent) {
                if (!is_null($talentContent->upload_link)) {
                    $rateFinal = $talentContent->rate_final ?? 0;
                    $slotFinal = $talentContent->slot_final ?? 1;
                    $accountName = $talentContent->nama_rekening ?? '';
                    $totalPerSlot = $slotFinal > 0 ? $rateFinal / $slotFinal : 0;
                    $totalPerSlot = $this->adjustSpentForTax($totalPerSlot, $accountName);
                    return $totalPerSlot;
                }
                return 0;
            })
            ->addColumn('status_and_link', function ($talentContent) {
                return $this->statusAndLinkHtml($talentContent);
            })
            ->addColumn('done', function ($talentContent) {
                return $this->doneHtml($talentContent);
            })
            ->addColumn('action', function ($talentContent) {
                return '
                    <button class="btn btn-sm btn-primary addLinkButton" 
                        data-id="' . $talentContent->id . '">
                        <i class="fas fa-link"> Add link</i>
                    </button>
                    <button class="btn btn-sm btn-success editButton" 
                        data-id="' . $talentContent->id . '">
                        <i class="fas fa-pencil-alt"> Edit</i>
                    </button>
                    <button class="btn btn-sm btn-danger deleteButton" 
                        data-id="' . $talentContent->id . '">
                        <i class="fas fa-trash-alt"> Delete</i>
                    </button>
                ';
            })
            ->addColumn('refund', function ($talentContent) {
                if ($talentContent->is_refund == 0) {
                    return '
                        <button class="btn btn-sm bg-maroon refundButton" 
                            data-id="' . $talentContent->id . '">
                            <i class="fas fa-redo"> Refund</i>
                        </button>
                    ';
                } else {
                    return '
                        <button class="btn btn-sm bg-info unRefundButton" 
                            data-id="' . $talentContent->id . '">
                            <i class="fas fa-undo"> Unrefund</i>
                        </button>
                    ';
                }
            })
            ->addColumn('deadline', function ($talentContent) {
                if ($talentContent->posting_date > $talentContent->dealing_upload_date) {
                    return '<span style="color:red;">Overdue</span>';
                } else {
                    return '<span style="color:green;">On time</span>';
                }
            })
            ->rawColumns(['action', 'status_and_link', 'done', 'refund', 'talent_should_get', 'deadline'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param TalentContentRequest $request
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'talent_id' => 'required|integer',
            'dealing_upload_date' => 'nullable|date',
            'pic_code' => 'nullable|string|max:255',
            'product' => 'nullable|string|max:255',
            'kerkun' => 'required|boolean',
            'campaign_id' => 'nullable|integer',
        ]);
        $validatedData['done'] = 0;
        $validatedData['upload_link'] = null;
        $validatedData['boost_code'] = null;
        $validatedData['tenant_id'] = Auth::user()->current_tenant_id;

        // Create a new TalentContent record
        TalentContent::create($validatedData);

        // Redirect back to the talent contents index page with a success message
        return redirect()->route('talent_content.index')->with('success', 'Talent content created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param TalentContent $talentContent
     */
    public function show(TalentContent $talentContent)
    {
        if (!$talentContent) {
            return response()->json(['message' => 'Talent content not found'], 404);
        }
        $campaignContent = CampaignContent::where('link', $talentContent->upload_link)->first();
        return response()->json([
            'talentContent' => [
                'id' => $talentContent->id,
                'talent_name' => $talentContent->talent_name,
                'dealing_upload_date' => $talentContent->dealing_upload_date,
                'posting_date' => $talentContent->posting_date,
                'final_rate_card' => $talentContent->final_rate_card,
                'done' => $talentContent->done,
                'upload_link' => $talentContent->upload_link,
                'pic_code' => $talentContent->pic_code,
                'boost_code' => $talentContent->boost_code,
                'kerkun' => $talentContent->kerkun,
                'product' => $talentContent->product,
                'campaign_title' => $talentContent->campaign ? $talentContent->campaign->title : null,
                'channel' => $campaignContent ? $campaignContent->channel : null,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param TalentContent $talentContent
     */
    public function edit($id)
    {
        $talentContent = TalentContent::find($id);

        if (!$talentContent) {
            return response()->json(['error' => 'Talent content not found'], 404);
        }
        return response()->json(['talentContent' => $talentContent]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param TalentContentRequest $request
     * @param TalentContent $talentContent
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'dealing_upload_date' => 'nullable|date',
            'posting_date' => 'nullable|date',
            'pic_code' => 'nullable|string|max:255',
            'boost_code' => 'nullable|string|max:255',
            'kerkun' => 'required|boolean',
            'upload_link' => 'nullable|string|max:255',
            'product' => 'nullable|string|max:255',
        ]);

        $validated['transfer_date'] = $request->dealing_upload_date;
        $validated['posting_date'] = $request->posting_date;
        $validated['upload_link'] = $request->upload_link;
        $validated['product'] = $request->product;
        if ($request->upload_link == "") {
            $validated['done'] = 0;
        }

        $talentContent = TalentContent::find($id);
        if (!$talentContent) {
            return redirect()->route('talent_content.index')->with('error', 'Talent content not found.');
        }

        try {
            $talentContent->update($validated);
            return redirect()->route('talent_content.index')->with('success', 'Talent content updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('talent_content.index')->with('error', 'Failed to update talent content. ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param TalentContent $talentContent
     */
    public function destroy($id)
    {
        $talentContent = TalentContent::findOrFail($id);
        $talentContent->delete();
        return response()->json(['success' => true]);
    }
    public function getTalents()
    {
        $talents = Talent::select('id', 'username')->get();
        return response()->json($talents);
    }
    public function getCampaigns()
    {
        $campaigns = Campaign::select('id', 'title')->get();
        return response()->json($campaigns);
    }
    public function getTodayTalentNames()
    {
        $today = Carbon::today();
        $currentTenantId = Auth::user()->current_tenant_id;
        $talentData = TalentContent::whereDate('talent_content.dealing_upload_date', $today)
            ->join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->leftJoin('campaigns', 'talent_content.campaign_id', '=', 'campaigns.id')
            ->where('talents.tenant_id', $currentTenantId)
            ->select('talents.username', 'campaigns.title as campaign_title')
            ->get();

        return response()->json($talentData);
    }

    public function calendar(): JsonResponse
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        $talentContents = TalentContent::join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->select('talent_content.id', 'talent_content.dealing_upload_date', 'talents.username')
            ->where('talents.tenant_id', $currentTenantId)
            ->get();
        $data = $talentContents->map(function ($content) {
            return [
                'id' => $content->id,
                'talent_name' => $content->username,
                'posting_date' => $content->dealing_upload_date ? (new \DateTime($content->dealing_upload_date))->format(DATE_ISO8601) : null, // Ensure ISO format
            ];
        });

        return response()->json(['data' => $data]);
    }
    public function countContent(): JsonResponse
    {
        $currentTenantId = Auth::user()->current_tenant_id;

        $todayCount = TalentContent::whereDate('talent_content.posting_date', today())
            ->join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->where('talents.tenant_id', $currentTenantId)
            ->count();

        $doneFalseCount = TalentContent::where('talent_content.done', false)
            ->join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->where('talents.tenant_id', $currentTenantId)
            ->count();

        $doneTrueCount = TalentContent::where('talent_content.done', true)
            ->join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->where('talents.tenant_id', $currentTenantId)
            ->count();

        $totalCount = TalentContent::join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->where('talents.tenant_id', $currentTenantId)
            ->count();

        return response()->json([
            'today_count' => $todayCount,
            'done_false_count' => $doneFalseCount,
            'done_true_count' => $doneTrueCount,
            'total_count' => $totalCount,
        ]);
    }

    public function extractVideoIdFromShortLink($shortUrl, $username)
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

    public function addLink(Request $request, $id): JsonResponse
    {
        $request->validate([
            'upload_link' => 'required|url',
            'channel' => 'required|string',
            'task_name' => 'required|string',
            'posting_date' => 'required|date',
            'kode_ads' => 'nullable|string',
        ]);

        $talentContent = TalentContent::findOrFail($id);
        $talent = Talent::findOrFail($talentContent->talent_id);

        if ($request->channel === "shopee_video") {
            $link_shopee = $this->extractVideoIdFromShortLink($request->upload_link, $talent->username);
            $talentContent->upload_link = $link_shopee;
        } else {
            $talentContent->upload_link = $request->upload_link;
        }

        $talentContent->done = 1;
        $talentContent->posting_date = $request->posting_date;
        $talentContent->save();

        $existingKOL = KeyOpinionLeader::where('username', $talent->username)->first();

        if ($existingKOL) {
            $existingKOL->update([
                'channel' => $request->channel,
                'rate' => $talent->slot_final != 0 ? ($talent->rate_final - $talent->tax_deduction) / $talent->slot_final : 0,
                'created_by' => Auth::user()->id,
                'pic_contact' => Auth::user()->id,
            ]);
            $kol = $existingKOL;
        } else {
            $kol = KeyOpinionLeader::create([
                'username' => $talent->username,
                'channel' => $request->channel,
                'niche' => '-',
                'average_view' => 0,
                'skin_type' => '-',
                'skin_concern' => '-',
                'content_type' => '-',
                'rate' => ($talent->rate_final - $talent->tax_deduction) / $talent->slot_final,
                'cpm' => 0,
                'created_by' => Auth::user()->id,
                'pic_contact' => Auth::user()->id,
            ]);
        }
        if ($request->channel === 'instagram_story') {
            CampaignContent::create([
                'campaign_id' => $talentContent->campaign_id,
                'key_opinion_leader_id' => $kol->id,
                'username' => $talent->username,
                'channel' => $request->channel,
                'task_name' => $request->task_name,
                'link' => $request->upload_link,
                'rate_card' => ($talent->rate_final - $talent->tax_deduction) / $talent->slot_final,
                'product' => $talentContent->product,
                'kode_ads' => $request->kode_ads,
                'upload_date' => null,
                'boost_code' => $talentContent->boost_code,
                'is_fyp' => 0,
                'is_product_deliver' => 0,
                'is_paid' => 0,
                'caption' => null,
                'created_by' => Auth::user()->id,
                'tenant_id' => Auth::user()->current_tenant_id,
            ]);
        } else {
            $campaignContent = CampaignContent::where('campaign_id', $talentContent->campaign_id)
                ->where('link', $request->upload_link)
                ->first();

            if ($campaignContent) {
                $campaignContent->update([
                    'username' => $talent->username,
                    'channel' => $request->channel,
                    'task_name' => $request->task_name,
                    'rate_card' => $talent->slot_final != 0 ? ($talent->rate_final - $talent->tax_deduction) / $talent->slot_final : 0,
                    'kode_ads' => $request->kode_ads,
                ]);
            } else {
                if ($request->channel === 'shopee_video') {
                    CampaignContent::create([
                        'campaign_id' => $talentContent->campaign_id,
                        'key_opinion_leader_id' => $kol->id,
                        'username' => $talent->username,
                        'channel' => $request->channel,
                        'task_name' => $request->task_name,
                        'link' => $link_shopee,
                        'rate_card' => $talent->slot_final != 0 ? ($talent->rate_final - $talent->tax_deduction) / $talent->slot_final : 0,
                        'product' => $talentContent->product,
                        'kode_ads' => $request->kode_ads,
                        'upload_date' => null,
                        'boost_code' => $talentContent->boost_code,
                        'is_fyp' => 0,
                        'is_product_deliver' => 0,
                        'is_paid' => 0,
                        'caption' => null,
                        'created_by' => Auth::user()->id,
                        'tenant_id' => Auth::user()->current_tenant_id,
                    ]);
                } else {
                    CampaignContent::create([
                        'campaign_id' => $talentContent->campaign_id,
                        'key_opinion_leader_id' => $kol->id,
                        'username' => $talent->username,
                        'channel' => $request->channel,
                        'task_name' => $request->task_name,
                        'link' => $request->upload_link,
                        'rate_card' => $talent->slot_final != 0 ? ($talent->rate_final - $talent->tax_deduction) / $talent->slot_final : 0,
                        'product' => $talentContent->product,
                        'kode_ads' => $request->kode_ads,
                        'upload_date' => null,
                        'boost_code' => $talentContent->boost_code,
                        'is_fyp' => 0,
                        'is_product_deliver' => 0,
                        'is_paid' => 0,
                        'caption' => null,
                        'created_by' => Auth::user()->id,
                        'tenant_id' => Auth::user()->current_tenant_id,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Link added successfully and campaign content created',
            'upload_link' => $talentContent->upload_link,
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(new TalentContentExport($request), 'talent_content.xlsx');
    }
    public function refund($id)
    {
        try {
            $talentContent = TalentContent::findOrFail($id);
            $talentContent->is_refund = 1; // Set is_refund to 1
            $talentContent->save();

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to refund talent content: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to refund talent content.'], 500);
        }
    }

    public function unrefund($id)
    {
        try {
            $talentContent = TalentContent::findOrFail($id);
            $talentContent->is_refund = 0; // Set is_refund to 0
            $talentContent->save();

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to unrefund talent content: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to unrefund talent content.'], 500);
        }
    }
    public function getProducts()
    {
        $tenantId = Auth::user()->current_tenant_id;

        $products = Talent::distinct()
            ->where('talents.tenant_id', '=', $tenantId)
            ->pluck('talents.produk')
            ->map(function ($product) {
                return ['short_name' => $product];
            });

        return response()->json($products);
    }
    public function getLineChartData()
    {
        $data = TalentContent::selectRaw('DATE(posting_date) as posting_date, product, COUNT(id) as count_id')
            ->whereNotNull('posting_date')
            ->groupByRaw('DATE(posting_date), product')
            ->orderBy('posting_date')
            ->get();

        $labels = $data->groupBy('posting_date')->keys();
        $products = $data->pluck('product')->unique();
        $datasets = [];

        foreach ($products as $product) {
            $productData = $data->where('product', $product)->keyBy('posting_date');
            $dataset = [
                'label' => $product,
                'data' => $labels->map(function ($date) use ($productData) {
                    return isset($productData[$date]) ? $productData[$date]->count_id : 0;
                })->toArray(),
                'borderColor' => $this->getRandomColor(),
                'fill' => false
            ];
            $datasets[] = $dataset;
        }

        return response()->json([
            'labels' => $labels->values()->toArray(),
            'datasets' => $datasets
        ]);
    }
    private function getRandomColor()
    {
        $colors = [
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)'
        ];
        return $colors[array_rand($colors)];
    }
}
