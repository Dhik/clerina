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
    public function edit($id)
    {
        $competitorSales = CompetitorSales::find($id);

        if (!$competitorSales) {
            return response()->json(['error' => 'Competitor sale not found'], 404);
        }
        
        return response()->json(['competitorSales' => $competitorSales]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param CompetitorRequest $request
     * @param  Competitor  $competitor
     */
    public function update(Request $request, $id)
    {
        $competitorSales = CompetitorSales::find($id);
        if (!$competitorSales) {
            return response()->json(['error' => 'Competitor sale not found'], 404);
        }
        $validated = $request->validate([
            'channel' => 'required|string|max:255',
            'omset' => 'required|integer',
            'date' => 'required|date',
            'type' => 'required|string|max:255',
        ]);

        try {
            // Update the competitor sale record with validated data
            $competitorSales->update($validated);
    
            // Return a success response
            return response()->json([
                'status' => 'success',
                'message' => 'Competitor sale updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Return an error response if there's an issue with the update
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update competitor sale.',
            ], 500);
        }
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
