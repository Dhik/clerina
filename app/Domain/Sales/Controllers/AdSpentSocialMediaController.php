<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Marketing\BLL\SocialMedia\SocialMediaBLLInterface;
use App\Domain\Sales\BLL\AdSpentSocialMedia\AdSpentSocialMediaBLLInterface;
use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Models\AdsMeta;
use App\Domain\Sales\Requests\AdSpentSocialMediaRequest;
use App\Http\Controllers\Controller;
use Auth;
use Exception;
use Carbon\Carbon; 
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AdSpentSocialMediaController extends Controller
{
    public function __construct(
        protected AdSpentSocialMediaBLLInterface $adSpentSocialMediaBLL,
        protected SocialMediaBLLInterface $socialMediaBLL
    ) {}

    /**
     * @throws Exception
     */
    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewAdSpentSocialMedia', AdSpentSocialMedia::class);

        $visitQuery = $this->adSpentSocialMediaBLL->getAdSpentSocialMediaDataTable($request, Auth::user()->current_tenant_id);

        return DataTables::of($visitQuery)
            ->addColumn('socialMedia', function ($row) {
                return $row->socialMedia->name ?? '-';
            })
            ->addColumn('amountFormatted', function ($row) {
                return number_format($row->amount, 0, ',', '.');
            })
            ->toJson();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAdSpentSocialMedia', AdSpentSocialMedia::class);

        $socialMedia = $this->socialMediaBLL->getSocialMedia();

        return view('admin.adSpentSocialMedia.index', compact('socialMedia'));
    }

    /**
     * Create or update data Ad SpentSocial Media
     */
    public function store(AdSpentSocialMediaRequest $request): JsonResponse
    {
        $this->authorize('createAdSpentSocialMedia', AdSpentSocialMedia::class);

        $this->authorize('createSales', Sales::class);

        return response()->json($this->adSpentSocialMediaBLL->createAdSpentSocialMedia($request, Auth::user()->current_tenant_id));
    }

    /**
     * Retrieves AdSpent social media recap information based on the provided request.
     */
    public function getAdSpentRecap(Request $request): JsonResponse
    {
        $this->authorize('viewAdSpentSocialMedia', AdSpentSocialMedia::class);

        return response()->json($this->adSpentSocialMediaBLL->getAdSpentSocialMediaRecap($request, Auth::user()->current_tenant_id));
    }
    public function import_ads(Request $request)
    {
        try {
            $request->validate([
                'meta_ads_csv_file' => 'required|file|mimes:csv,txt|max:2048'
            ]);

            $file = $request->file('meta_ads_csv_file');
            $csvData = array_map('str_getcsv', file($file->getPathname()));
            
            $headers = array_shift($csvData);
            $groupedData = [];
            $adSpentData = [];
            
            foreach ($csvData as $row) {
                $date = Carbon::parse($row[0])->format('Y-m-d');
                $amount = (int)$row[3];
                
                if (!isset($groupedData[$date])) {
                    $groupedData[$date] = [
                        'amount_spent' => 0,
                        'impressions' => 0,
                        'content_views_shared_items' => 0,
                        'adds_to_cart_shared_items' => 0,
                        'purchases_shared_items' => 0,
                        'purchases_conversion_value_shared_items' => 0,
                    ];
                }
                
                if (!isset($adSpentData[$date])) {
                    $adSpentData[$date] = 0;
                }
                $groupedData[$date]['amount_spent'] += (int)$row[3];
                $groupedData[$date]['impressions'] += (int)$row[4];
                $groupedData[$date]['content_views_shared_items'] += (float)($row[5] ?? 0);
                $groupedData[$date]['adds_to_cart_shared_items'] += (float)($row[6] ?? 0);
                $groupedData[$date]['purchases_shared_items'] += (float)($row[7] ?? 0);
                $groupedData[$date]['purchases_conversion_value_shared_items'] += (float)($row[8] ?? 0);
                
                $adSpentData[$date] += $amount;
            }

            DB::beginTransaction();
            try {
                foreach ($groupedData as $date => $data) {
                    AdsMeta::updateOrCreate(
                        [
                            'date' => $date,
                            'tenant_id' => auth()->user()->current_tenant_id
                        ],
                        [
                            'amount_spent' => $data['amount_spent'],
                            'impressions' => $data['impressions'],
                            'content_views_shared_items' => $data['content_views_shared_items'],
                            'adds_to_cart_shared_items' => $data['adds_to_cart_shared_items'],
                            'purchases_shared_items' => $data['purchases_shared_items'],
                            'purchases_conversion_value_shared_items' => $data['purchases_conversion_value_shared_items']
                        ]
                    );
                }
                foreach ($adSpentData as $date => $totalAmount) {
                    AdSpentSocialMedia::updateOrCreate(
                        [
                            'date' => $date,
                            'social_media_id' => 1, // Meta's ID
                            'tenant_id' => auth()->user()->current_tenant_id
                        ],
                        [
                            'amount' => $totalAmount
                        ]
                    );
                }
                
                DB::commit();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Meta ads data imported successfully'
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error importing data: ' . $e->getMessage()
            ], 422);
        }
    }
    
    public function getFunnelData(Request $request)
    {
        try {
            $dates = explode(' - ', $request->filterDates);
            $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]));
            $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]));

            $data = AdsMeta::select(
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases')
            )
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->first();

            return response()->json([
                'status' => 'success',
                'data' => [
                    [
                        'name' => 'Impressions',
                        'value' => (int)$data->total_impressions
                    ],
                    [
                        'name' => 'Content Views',
                        'value' => (int)$data->total_content_views
                    ],
                    [
                        'name' => 'Adds to Cart',
                        'value' => (int)$data->total_adds_to_cart
                    ],
                    [
                        'name' => 'Purchases',
                        'value' => (int)$data->total_purchases
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching funnel data: ' . $e->getMessage()
            ], 422);
        }
    }
    public function getImpressionChartData(Request $request)
    {
        try {
            $dates = explode(' - ', $request->filterDates);
            $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]));
            $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]));

            $data = AdsMeta::select(
                'date',
                'impressions'
            )
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date->format('Y-m-d'),
                    'impressions' => (int)$item->impressions
                ];
            });

            return response()->json([
                'status' => 'success',
                'impressions' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching impression data: ' . $e->getMessage()
            ], 422);
        }
    }
}
