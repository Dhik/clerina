<?php

namespace App\Domain\Competitor\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Competitor\BLL\Competitor\CompetitorBLLInterface;
use App\Domain\Competitor\Models\CompetitorSales;
use App\Domain\Competitor\Models\CompetitorBrand;
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
class CompetitorSalesController extends Controller
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
        return view('admin.competitor_brands.show', compact('competitorBrands'));
    }
    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        return view('admin.competitor_brands.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CompetitorRequest $request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'competitor_brand_id' => 'required|exists:competitor_brands,id',
            'channel' => 'required|string|max:255',
            'omset' => 'required|integer',
            'date' => 'required|date',
            'type' => 'required|string|max:255',
        ]);
        CompetitorSales::create($validated);
        return redirect()->route('competitor_brands.show', ['competitorBrand' => $validated['competitor_brand_id']])
            ->with('success', 'Competitor sale created successfully.');
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
    public function edit(CompetitorSales $competitorSales)
    {
        return response()->json(['competitorSales' => $competitorSales]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param CompetitorRequest $request
     * @param  Competitor  $competitor
     */
    public function update(Request $request, CompetitorSales $competitorSales)
    {
        $validated = $request->validate([
            'competitor_brand_id' => 'required|exists:competitor_brands,id',
            'channel' => 'required|string|max:255',
            'omset' => 'required|integer',
            'date' => 'required|date',
            'type' => 'required|string|max:255',
        ]);

        $competitorSale->update($validated);

        return redirect()->route('admin.competitor_brands.show')->with('success', 'Competitor sale updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Competitor $competitor
     */
    public function destroy(CompetitorSales $competitorSale)
    {
        $competitorBrandId = $competitorSale->competitor_brand_id;
        $competitorSale->delete();
        return redirect()->route('competitor_brands.show', ['competitorBrand' => $competitorBrandId])
            ->with('success', 'Competitor sale deleted successfully.');
    }
}
