<?php

namespace App\Domain\Competitor\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Competitor\BLL\Competitor\CompetitorBLLInterface;
use App\Domain\Competitor\Models\CompetitorBrand;
use App\Domain\Competitor\Models\CompetitorSales;
use App\Domain\Sales\Models\Sales;
use App\Domain\Competitor\Requests\CompetitorRequest;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * @property CompetitorBLLInterface competitorBLL
 */
class CompetitorBrandController extends Controller
{
    public function __construct(CompetitorBLLInterface $competitorBLL)
    {
        $this->competitorBLL = $competitorBLL;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $competitorBrands = CompetitorBrand::all();
        return view('admin.competitor_brands.index', compact('competitorBrands'));
    }

    public function data()
    {
        $competitorBrands = CompetitorBrand::select(['id', 'brand', 'keterangan', 'logo']);

        return DataTables::of($competitorBrands)
            ->addColumn('action', function ($competitorBrand) {
                return '
                    <a href="' . route('competitor_brands.show', $competitorBrand->id) . '" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                    <button class="btn btn-sm btn-success editButton" data-id="' . $competitorBrand->id . '"><i class="fas fa-pencil-alt"></i></button>
                    <button class="btn btn-sm btn-danger deleteButton" data-id="' . $competitorBrand->id . '"><i class="fas fa-trash-alt"></i></button>';
            })
            ->editColumn('logo', function ($competitorBrand) {
                return $competitorBrand->logo ? '<img src="' . asset('storage/' . $competitorBrand->logo) . '" width="150">' : 'No Logo';
            })
            ->rawColumns(['action', 'logo'])
            ->make(true);
    }



    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        return view('admin.competitor_brands.create');
    }

    public function show(CompetitorBrand $competitorBrand)
    {
        $competitorSales = CompetitorSales::where('competitor_brand_id', $competitorBrand->id)->get();
        return view('admin.competitor_brands.show', compact('competitorBrand', 'competitorSales'));
    }

    public function getCompetitorSalesChart(int $competitorBrandId, Request $request): JsonResponse
    {
        // Base query to get sales data filtered by competitor_brand_id
        $query = CompetitorSales::where('competitor_brand_id', $competitorBrandId)
            ->selectRaw('date, SUM(omset) as omset')
            ->groupBy('date');

        // Apply channel filter if present
        if (!is_null($request->input('filterChannel'))) {
            $query->where('channel', $request->input('filterChannel'));
        }

        // Apply type filter if present
        if (!is_null($request->input('filterType'))) {
            $query->where('type', $request->input('filterType'));
        }

        // If date filter is applied, modify the query
        if ($request->has('filterDates')) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Execute the query and get the result
        $salesData = $query->get();

        // Format the response
        $formattedData = $salesData->map(function ($item) {
            return [
                'date' => $item->date,
                'omset' => $item->omset,
            ];
        });

        return response()->json($formattedData);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param CompetitorRequest $request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        $logoPath = $request->file('logo') ? $request->file('logo')->store('logos', 'public') : null;

        CompetitorBrand::create([
            'brand' => $validated['brand'],
            'keterangan' => $validated['keterangan'],
            'logo' => $logoPath,
        ]);

        return redirect()->route('competitor_brands.index')->with('success', 'Competitor brand created successfully.');
    }
    /**
     * Display the specified resource.
     *
     * @param Competitor $competitor
     */
    /**
     * Show the form for editing the specified resource.
     *
     * @param  Competitor  $competitor
     */
    public function edit(CompetitorBrand $competitorBrand)
    {
        return response()->json(['competitorBrand' => $competitorBrand]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param CompetitorRequest $request
     * @param  Competitor  $competitor
     */
    public function update(Request $request, CompetitorBrand $competitorBrand)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $competitorBrand->update([
                'brand' => $validated['brand'],
                'keterangan' => $validated['keterangan'],
                'logo' => $logoPath,
            ]);
        } else {
            $competitorBrand->update($validated);
        }

        return redirect()->route('competitor_brands.index')->with('success', 'Competitor brand updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Competitor $competitor
     */
    public function destroy(CompetitorBrand $competitorBrand)
    {
        if ($competitorBrand->logo) {
            \Storage::delete($competitorBrand->logo);
        }
        $competitorBrand->delete();

        return redirect()->route('competitor_brands.index')->with('success', 'Competitor brand deleted successfully.');
    }
    public function getCompetitorSalesData(Request $request, $competitorBrandId)
    {
        $query = CompetitorSales::where('competitor_brand_id', $competitorBrandId);

        // Apply filters if they are present
        if (!is_null($request->input('filterChannel'))) {
            $query->where('channel', $request->input('filterChannel'));
        }

        if (!is_null($request->input('filterType'))) {
            $query->where('type', $request->input('filterType'));
        }

        return DataTables::of($query)
            ->addColumn('competitor_brand', function ($sale) {
                return $sale->competitorBrand->brand;
            })
            ->addColumn('action', function ($sale) {
                return '
                    <button class="btn btn-sm btn-primary viewSaleButton" data-id="' . $sale->id . '">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success editButton" data-id="' . $sale->id . '"><i class="fas fa-pencil-alt"></i></button>
                    <form action="' . route('competitor_sales.destroy', $sale->id) . '" method="POST" style="display:inline-block;">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger deleteButton"><i class="fas fa-trash-alt"></i></button>
                    </form>';
            })
            ->rawColumns(['action']) // To render HTML in the action column
            ->make(true);
    }


    public function show_sales($id)
    {
        $competitorSale = CompetitorSales::find($id);

        if (!$competitorSale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }
        return response()->json(['competitorSale' => $competitorSale]);
    }

    public function getMonthlySalesData(): JsonResponse
    {
        $monthlySales = Sales::selectRaw("DATE_FORMAT(date, '%Y-%m') as date, SUM(turnover) as sales")
            ->where('tenant_id', 1) 
            ->groupByRaw("DATE_FORMAT(date, '%Y-%m')")
            ->orderBy('date', 'asc')
            ->get();

        $formattedData = $monthlySales->map(function ($item) {
            return [
                'date' => $item->date,
                'sales' => $item->sales,
            ];
        });
        return response()->json($formattedData);
    }

}
