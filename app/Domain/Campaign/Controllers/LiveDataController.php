<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\Models\LiveData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

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
        return view('admin.live_data.create');
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
        ]);

        LiveData::create($request->all());

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
        return view('admin.live_data.edit', compact('liveData'));
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
        $liveData = LiveData::all();

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
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Get chart data for live data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function chartData()
    {
        $chartData = LiveData::select('date', 
                'dilihat as total_view', 
                'komentar as total_comment',
                'penonton_tertinggi as peak_viewers',
                'pesanan as orders')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($chartData);
    }
}