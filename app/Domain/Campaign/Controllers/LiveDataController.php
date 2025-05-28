<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\Models\LiveData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Domain\Sales\Models\SalesChannel;
use Auth;

class LiveDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.live_data.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $salesChannels = SalesChannel::all();
        return view('admin.live_data.create', compact('salesChannels'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'dilihat' => 'required|integer',
            'penonton_tertinggi' => 'required|integer',
            'rata_rata_durasi' => 'required|integer',
            'komentar' => 'required|integer',
            'pesanan' => 'required|integer',
            'penjualan' => 'required|numeric',
            'sales_channel_id' => 'nullable|exists:sales_channels,id',
        ]);
        
        // Get validated data
        $data = $request->all();
        
        // Assign the current authenticated user's employee_id to the live_data entry
        $data['employee_id'] = auth()->user()->employee_id;
        
        LiveData::create($data);
        
        return redirect()->route('live_data.index')
            ->with('success', 'Live data created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Domain\Campaign\Models\LiveData  $liveData
     * @return \Illuminate\Http\Response
     */
    public function show(LiveData $liveData)
    {
        return view('admin.live_data.show', compact('liveData'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Domain\Campaign\Models\LiveData  $liveData
     * @return \Illuminate\Http\Response
     */
    public function edit(LiveData $liveData)
    {
        $salesChannels = SalesChannel::all();
        return view('admin.live_data.edit', compact('liveData', 'salesChannels'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Domain\Campaign\Models\LiveData  $liveData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LiveData $liveData)
    {
        $request->validate([
            'date' => 'required|date',
            'shift' => 'required|string',
            'dilihat' => 'required|integer',
            'penonton_tertinggi' => 'required|integer',
            'rata_rata_durasi' => 'required|integer',
            'komentar' => 'required|integer',
            'pesanan' => 'required|integer',
            'penjualan' => 'required|numeric',
            'sales_channel_id' => 'nullable|exists:sales_channels,id',
        ]);

        $liveData->update($request->all());

        return redirect()->route('live_data.index')
            ->with('success', 'Live data updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Domain\Campaign\Models\LiveData  $liveData
     * @return \Illuminate\Http\Response
     */
    public function destroy(LiveData $liveData)
    {
        $liveData->delete();

        return redirect()->route('live_data.index')
            ->with('success', 'Live data deleted successfully');
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data()
    {
        $liveData = LiveData::with(['salesChannel', 'user'])->get();

        return DataTables::of($liveData)
            ->addColumn('actions', function ($data) {
                return '
                    <a href="' . route('live_data.show', $data->id) . '" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                    <a href="' . route('live_data.edit', $data->id) . '" class="btn btn-sm btn-success"><i class="fas fa-pencil-alt"></i></a>
                    <form action="' . route('live_data.destroy', $data->id) . '" method="POST" style="display:inline-block;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                    </form>
                ';
            })
            ->editColumn('date', function ($data) {
                return Carbon::parse($data->date)->format('d-m-Y');
            })
            ->editColumn('penjualan', function ($data) {
                return number_format($data->penjualan, 2);
            })
            ->addColumn('sales_channel', function ($data) {
                return $data->salesChannel ? $data->salesChannel->name : 'N/A';
            })
            ->addColumn('employee_name', function ($data) {
                return $data->user ? $data->user->name : 'N/A';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }



    // Add these functions to your LiveDataController

    public function dashboard()
    {
        // KPI summary data
        $totalViews = LiveData::sum('dilihat');
        $totalOrders = LiveData::sum('pesanan');
        $totalSales = LiveData::sum('penjualan');
        $averageConversionRate = $totalViews > 0 ? ($totalOrders / $totalViews) * 100 : 0;
        
        return view('admin.live_data.dashboard', compact('totalViews', 'totalOrders', 'totalSales', 'averageConversionRate'));
    }

    public function chartData()
    {
        // Data for line chart - Group by shift time and aggregate
        $rawData = LiveData::all()
            ->groupBy('shift')
            ->map(function ($group, $shift) {
                $startHour = $this->extractStartHour($shift);
                
                return [
                    'label' => $shift,
                    'start_hour' => $startHour,
                    'dilihat' => $group->sum('dilihat'),
                    'penonton_tertinggi' => $group->max('penonton_tertinggi'), // or avg() if you prefer
                    'komentar' => $group->sum('komentar'),
                    'pesanan' => $group->sum('pesanan'),
                    'penjualan' => $group->sum('penjualan')
                ];
            })
            ->sortBy('start_hour')
            ->values()
            ->map(function ($item) {
                // Remove start_hour from final output
                unset($item['start_hour']);
                return $item;
            });
        
        $lineChartData = $rawData;
        
        // Data for funnel chart
        $funnelData = [
            ['stage' => 'Dilihat', 'value' => LiveData::sum('dilihat')],
            ['stage' => 'Komentar', 'value' => LiveData::sum('komentar')],
            ['stage' => 'Pesanan', 'value' => LiveData::sum('pesanan')],
        ];
        
        return response()->json([
            'lineChartData' => $lineChartData,
            'funnelData' => $funnelData
        ]);
    }

    /**
     * Extract start hour from shift string
     * Examples: "06:00 - 08:00" -> 6, "21:00 - 01:00" -> 21
     */
    private function extractStartHour($shift)
    {
        // Match the first time in the shift string
        if (preg_match('/(\d{1,2}):(\d{2})/', $shift, $matches)) {
            return (int) $matches[1];
        }
        
        // Fallback: return 0 if no match found
        return 0;
    }
}