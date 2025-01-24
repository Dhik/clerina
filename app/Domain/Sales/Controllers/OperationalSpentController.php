<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Sales\Models\OperationalSpent;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class OperationalSpentController extends Controller
{
    public function index()
    {
        return view('admin.sales.operational_spent.index');
    }

    public function get()
    {
        $query = OperationalSpent::query();

        return DataTables::of($query)
            ->addColumn('actions', function ($row) {
                return '<button onclick="editData('.$row->id.')" class="btn btn-sm btn-primary">Edit</button>';
            })
            ->editColumn('spent', function ($row) {
                return number_format($row->spent, 0, ',', '.');
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function getByDate(Request $request)
    {
        $date = Carbon::parse($request->date);
        
        return OperationalSpent::where('month', $date->month)
            ->where('year', $date->year)
            ->first();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|exists:operational_spents,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'spent' => 'required|numeric|min:0'
        ]);

        OperationalSpent::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );

        return response()->json(['success' => true]);
    }
}