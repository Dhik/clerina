<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\Models\LiveTiktok;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Auth;
use DB;
use Log;

class LiveTiktokController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.live_tiktok.index');
    }

    /**
     * Get Live TikTok data for DataTables
     */
    public function get_live_tiktok(Request $request) 
    {
        $query = LiveTiktok::query();

        // Apply filters
        if ($request->has('date_start') && $request->has('date_end')) {
            $query->whereBetween('date', [$request->date_start, $request->date_end]);
        } else {
            $query->whereMonth('date', now()->month)
                ->whereYear('date', now()->year);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('d M Y');
            })
            ->editColumn('gmv_live', function ($row) {
                return $row->gmv_live ? 'Rp ' . number_format($row->gmv_live, 0, ',', '.') : 'Rp 0';
            })
            ->editColumn('pesanan', function ($row) {
                return $row->pesanan ?? 0;
            })
            ->editColumn('tayangan', function ($row) {
                return $row->tayangan ? number_format($row->tayangan, 0, ',', '.') : 0;
            })
            ->editColumn('gpm', function ($row) {
                return $row->gpm ? 'Rp ' . number_format($row->gpm, 0, ',', '.') : 'Rp 0';
            })
            ->addColumn('conversion_rate', function ($row) {
                if ($row->tayangan > 0 && $row->pesanan > 0) {
                    return number_format(($row->pesanan / $row->tayangan) * 100, 4) . '%';
                }
                return '0%';
            })
            ->addColumn('avg_order_value', function ($row) {
                if ($row->pesanan > 0 && $row->gmv_live > 0) {
                    return 'Rp ' . number_format($row->gmv_live / $row->pesanan, 0, ',', '.');
                }
                return 'Rp 0';
            })
            ->addColumn('performance', function ($row) {
                // Performance based on conversion rate
                if ($row->tayangan > 0 && $row->pesanan > 0) {
                    $conversionRate = ($row->pesanan / $row->tayangan) * 100;
                    
                    if ($conversionRate >= 1) {
                        return '<span class="badge badge-success">Excellent</span>';
                    } elseif ($conversionRate >= 0.5) {
                        return '<span class="badge badge-primary">Good</span>';
                    } elseif ($conversionRate >= 0.1) {
                        return '<span class="badge badge-info">Average</span>';
                    } else {
                        return '<span class="badge badge-warning">Poor</span>';
                    }
                }
                return '<span class="badge badge-secondary">N/A</span>';
            })
            ->addColumn('action', function ($row) {
                return '<div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-info edit-record" data-id="'.$row->id.'" data-toggle="tooltip" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger delete-record" data-id="'.$row->id.'" data-toggle="tooltip" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['performance', 'action'])
            ->make(true);
    }

    /**
     * Get line chart data for GMV over time
     */
    public function get_line_data(Request $request)
    {
        try {
            $query = LiveTiktok::query();
            
            // Apply date filter
            if ($request->has('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
                
                $query->whereBetween('date', [$startDate, $endDate]);
            } else {
                // If no date filter, show data from the last 30 days
                $query->where('date', '>=', Carbon::now()->subDays(30)->format('Y-m-d'));
            }
            
            // Get GMV data ordered by date
            $gmvData = $query->select('date', 'gmv_live')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('d M Y'),
                        'gmv' => (int)$item->gmv_live
                    ];
                });
            
            // If no data, return empty array
            if ($gmvData->isEmpty()) {
                $gmvData = collect([]);
            }
            
            return response()->json([
                'status' => 'success',
                'gmv' => $gmvData,
                'has_data' => $gmvData->isNotEmpty()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Line chart data error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get funnel data for Live TikTok analysis
     */
    public function get_funnel_data(Request $request)
    {
        try {
            $query = LiveTiktok::query();
            
            // Apply date filter
            if ($request->has('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
                
                $query->whereBetween('date', [$startDate, $endDate]);
            } else {
                // If no date filter, show data from the last 30 days
                $query->where('date', '>=', Carbon::now()->subDays(30)->format('Y-m-d'));
            }
            
            // Get aggregate data
            $aggregates = $query->select(
                DB::raw('SUM(tayangan) as total_tayangan'),
                DB::raw('SUM(pesanan) as total_pesanan'),
                DB::raw('SUM(gmv_live) as total_gmv'),
                DB::raw('SUM(gpm) as total_gpm')
            )->first();
            
            // Handle empty data case
            if (!$aggregates || $aggregates->total_tayangan == 0) {
                $funnelData = [
                    ['name' => 'Total Tayangan', 'value' => 0],
                    ['name' => 'Total Pesanan', 'value' => 0],
                    ['name' => 'GMV Live (K)', 'value' => 0],
                    ['name' => 'GPM (K)', 'value' => 0]
                ];
            } else {
                // Prepare data for funnel chart
                $funnelData = [
                    ['name' => 'Total Tayangan', 'value' => (int)$aggregates->total_tayangan],
                    ['name' => 'Total Pesanan', 'value' => (int)$aggregates->total_pesanan],
                    ['name' => 'GMV Live (K)', 'value' => (int)($aggregates->total_gmv / 1000)],
                    ['name' => 'GPM (K)', 'value' => (int)($aggregates->total_gpm / 1000)]
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $funnelData,
                'has_data' => $aggregates && $aggregates->total_tayangan > 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'gmv_live' => 'required|numeric|min:0',
                'pesanan' => 'required|integer|min:0',
                'tayangan' => 'required|integer|min:0',
                'gpm' => 'required|numeric|min:0'
            ]);

            // Check if record already exists for this date
            $existingRecord = LiveTiktok::where('date', $request->date)->first();
            
            if ($existingRecord) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Record for this date already exists. Please use edit to update the existing record.'
                ], 422);
            }

            LiveTiktok::create([
                'date' => $request->date,
                'gmv_live' => $request->gmv_live,
                'pesanan' => $request->pesanan,
                'tayangan' => $request->tayangan,
                'gpm' => $request->gpm
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Live TikTok record created successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating record: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $liveTiktok = LiveTiktok::findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $liveTiktok
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Record not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'gmv_live' => 'required|numeric|min:0',
                'pesanan' => 'required|integer|min:0',
                'tayangan' => 'required|integer|min:0',
                'gpm' => 'required|numeric|min:0'
            ]);

            $liveTiktok = LiveTiktok::findOrFail($id);

            // Check if another record exists for this date (excluding current record)
            $existingRecord = LiveTiktok::where('date', $request->date)
                ->where('id', '!=', $id)
                ->first();
            
            if ($existingRecord) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Another record for this date already exists.'
                ], 422);
            }

            $liveTiktok->update([
                'date' => $request->date,
                'gmv_live' => $request->gmv_live,
                'pesanan' => $request->pesanan,
                'tayangan' => $request->tayangan,
                'gpm' => $request->gpm
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Live TikTok record updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating record: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $liveTiktok = LiveTiktok::findOrFail($id);
            $liveTiktok->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Live TikTok record deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting record: ' . $e->getMessage()
            ], 422);
        }
    }
}