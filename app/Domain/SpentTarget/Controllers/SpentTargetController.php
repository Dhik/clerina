<?php

namespace App\Domain\SpentTarget\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\SpentTarget\BLL\SpentTarget\SpentTargetBLLInterface;
use App\Domain\SpentTarget\Models\SpentTarget;
use App\Domain\Sales\Models\Sales;
use App\Domain\SpentTarget\Requests\SpentTargetRequest;
use Yajra\DataTables\Utilities\Request;
use App\Domain\Talent\Models\TalentContent;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Auth;

/**
 * @property SpentTargetBLLInterface spentTargetBLL
 */
class SpentTargetController extends Controller
{
    public function __construct(SpentTargetBLLInterface $spentTargetBLL)
    {
        $this->spentTargetBLL = $spentTargetBLL;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return view('admin.spent_target.index');
    }
    
    /**
     * Fetch the data for the DataTable.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data()
    {
        $spentTargets = SpentTarget::all();

        return DataTables::of($spentTargets)
            ->addColumn('action', function ($spentTarget) {
                return '
                    <a href="' . route('spentTarget.show', $spentTarget->id) . '" class="btn btn-sm btn-info viewButton" data-id="' . $spentTarget->id . '">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="' . route('spentTarget.edit', $spentTarget->id) . '" class="btn btn-sm btn-warning editButton" data-id="' . $spentTarget->id . '">
                        <i class="fas fa-pencil-alt"></i> Edit
                    </a>
                    <form action="' . route('spentTarget.destroy', $spentTarget->id) . '" method="POST" style="display:inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger deleteButton">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </form>
                ';
            })
            ->editColumn('budget', function ($spentTarget) {
                return 'Rp ' . number_format($spentTarget->budget, 2, ',', '.');  // Format as Rupiah
            })
            ->editColumn('kol_percentage', function ($spentTarget) {
                return $spentTarget->kol_percentage . '%';
            })
            ->editColumn('ads_percentage', function ($spentTarget) {
                return $spentTarget->ads_percentage . '%';
            })
            ->editColumn('creative_percentage', function ($spentTarget) {
                return $spentTarget->creative_percentage . '%';
            })
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SpentTargetRequest $request
     */
    public function store(SpentTargetRequest $request)
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        $validatedData = $request->validated();
        $validatedData['tenant_id'] = $currentTenantId;

        $spentTarget = SpentTarget::create($validatedData);
        return redirect()->route('spentTarget.index')->with('success', 'Spent target created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param SpentTarget $spentTarget
     */
    public function show(SpentTarget $spentTarget)
    {
        return response()->json($spentTarget);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  SpentTarget  $spentTarget
     */
    public function edit(SpentTarget $spentTarget)
    {
        return response()->json($spentTarget);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SpentTargetRequest $request
     * @param  SpentTarget  $spentTarget
     */
    public function update(SpentTargetRequest $request, SpentTarget $spentTarget)
    {
        $spentTarget->update($request->validated());
        return redirect()->route('spentTarget.index')->with('success', 'Spent target updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SpentTarget $spentTarget
     */
    public function destroy(SpentTarget $spentTarget)
    {
        $spentTarget->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Fetch spent target data for the current month.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpentTargetThisMonth()
    {
        $currentDate = now();
        $currentMonth = now()->format('m/Y'); 
        $currentTenantId = Auth::user()->current_tenant_id;

        $talentShouldGetTotal = TalentContent::with('talent')
            ->whereHas('talent', function ($query) use ($currentTenantId) {
                $query->where('tenant_id', $currentTenantId);
            })
            ->whereRaw("DATE_FORMAT(posting_date, '%m/%Y') = ?", [$currentMonth]) 
            ->get()
            ->sum(function ($item) {
                $talent = $item->talent;
                if ($item->upload_link) {
                    $rateFinal = $talent->rate_final ?? 0;
                    $slotFinal = max($talent->slot_final ?? 1, 1);
                    return $rateFinal / $slotFinal;
                }
                return 0;
            });

        $daysInMonth = $currentDate->daysInMonth;

        $adsSpent = Sales::where('tenant_id', $currentTenantId)
            ->whereMonth('date', now()->month) 
            ->whereYear('date', now()->year) 
            ->get()
            ->sum(function ($sale) {
                return $sale->ad_spent_social_media + $sale->ad_spent_market_place;
            });

        $spentTargets = SpentTarget::where('month', $currentMonth)->get()->map(function ($spentTarget) use ($talentShouldGetTotal, $daysInMonth, $currentDate, $adsSpent) {
            $currentDay = $currentDate->day;

            $kolTargetSpent = ($spentTarget->budget / 100) * $spentTarget->kol_percentage;
            $kolTargetToday = ($kolTargetSpent / $daysInMonth) * $currentDay;
            
            return [
                'id' => $spentTarget->id,
                'budget' => $spentTarget->budget,
                'kol_percentage' => $spentTarget->kol_percentage,
                'ads_percentage' => $spentTarget->ads_percentage,
                'creative_percentage' => $spentTarget->creative_percentage,
                'other_percentage' => $spentTarget->other_percentage,
                'affiliate_percentage' => $spentTarget->affiliate_percentage,
                'month' => $spentTarget->month,
                'tenant_id' => $spentTarget->tenant_id,
                'created_at' => $spentTarget->created_at,
                'updated_at' => $spentTarget->updated_at,
                'kol_target_spent' => ($spentTarget->budget / 100) * $spentTarget->kol_percentage,
                'ads_target_spent' => ($spentTarget->budget / 100) * $spentTarget->ads_percentage,
                'creative_target_spent' => ($spentTarget->budget / 100) * $spentTarget->creative_percentage,
                'other_target_spent' => ($spentTarget->budget / 100) * $spentTarget->other_percentage,
                'affiliate_target_spent' => ($spentTarget->budget / 100) * $spentTarget->affiliate_percentage,
                'talent_should_get_total' => $talentShouldGetTotal,
                'kol_target_today' => $kolTargetToday,
                'ads_spent' => $adsSpent,
            ];
        });

        return response()->json($spentTargets);
    }


    public function getTalentShouldGetByDay(Request $request)
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        $currentMonth = now()->format('m/Y'); 

        $spentTarget = SpentTarget::where('tenant_id', $currentTenantId)
            ->where('month', $currentMonth) 
            ->first();

        $targetSpentKolDay = 0;
        if ($spentTarget) {
            $targetSpentKolMonth = ($spentTarget->budget / 100) * $spentTarget->kol_percentage;
            $daysInMonth = now()->daysInMonth;
            $targetSpentKolDay = $targetSpentKolMonth / $daysInMonth;
        }

        $talentShouldGets = TalentContent::with('talent')
            ->whereHas('talent', function ($query) use ($currentTenantId) {
                $query->where('tenant_id', $currentTenantId);
            })
            ->whereRaw("DATE_FORMAT(posting_date, '%m/%Y') = ?", [$currentMonth]) 
            ->get()
            ->groupBy(function ($item) {
                return $item->posting_date->format('Y-m-d'); 
            })
            ->map(function ($items) {
                return $items->sum(function ($item) {
                    $talent = $item->talent;
                    if ($item->upload_link) {
                        $rateFinal = $talent->rate_final ?? 0;
                        $slotFinal = max($talent->slot_final ?? 1, 1); 
                        return $rateFinal / $slotFinal;
                    }
                    return 0;
                });
            });

        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        $labels = [];
        $talentShouldGetValues = [];
        $targetSpentKolDayValues = [];

        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $formattedDate = $date->format('Y-m-d');
            $labels[] = $formattedDate;
            $talentShouldGetValues[] = $talentShouldGets->get($formattedDate, 0);
            $targetSpentKolDayValues[] = $targetSpentKolDay;
        }

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Talent Should Get',
                    'data' => $talentShouldGetValues,
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Target Spent Per Day',
                    'data' => $targetSpentKolDayValues,
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'tension' => 0.4
                ]
            ]
        ];

        return response()->json($chartData);
    }

    public function getAdsSpentByDay(Request $request)
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        $currentMonth = now()->format('m/Y'); 

        $spentTarget = SpentTarget::where('tenant_id', $currentTenantId)
            ->where('month', $currentMonth) 
            ->first();

        $targetAdsSpentDay = 0;
        if ($spentTarget) {
            $targetAdsSpentMonth = ($spentTarget->budget / 100) * $spentTarget->ads_percentage;
            $daysInMonth = now()->daysInMonth;
            $targetAdsSpentDay = $targetAdsSpentMonth / $daysInMonth;
        }

        $adsSpentByDay = Sales::where('tenant_id', $currentTenantId)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d'); 
            })
            ->map(function ($items) {
                return $items->sum(function ($item) {
                    return $item->ad_spent_social_media + $item->ad_spent_market_place;
                });
            });

        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        $labels = [];
        $adsSpentValues = []; 
        $targetSpentAdsDayValues = []; 

        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $formattedDate = $date->format('Y-m-d');
            $labels[] = $formattedDate;
            $adsSpentValues[] = $adsSpentByDay->get($formattedDate, 0);
            $targetSpentAdsDayValues[] = $targetAdsSpentDay; 
        }

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Ads Spent',
                    'data' => $adsSpentValues,
                    'borderColor' => 'rgba(75, 192, 192, 1)',  
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)', 
                    'tension' => 0.4
                ],
                [
                    'label' => 'Target Ads Spent Per Day',
                    'data' => $targetSpentAdsDayValues,
                    'borderColor' => 'rgba(255, 99, 132, 1)',  
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)', 
                    'tension' => 0.4
                ]
            ]
        ];

        return response()->json($chartData);
    }


    

}
