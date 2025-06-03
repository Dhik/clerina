<?php
namespace App\Domain\Sales\Controllers;

use App\Domain\Sales\Models\OperationalSpent;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Domain\Sales\Models\NetProfit;
use Carbon\Carbon;
use Auth;
use App\Http\Controllers\Controller;

class OperationalSpentController extends Controller
{
    public function index()
    {
        return view('admin.sales.operational_spent.index');
    }

    public function get()
    {
        $query = OperationalSpent::query()
            ->where('tenant_id', Auth::user()->current_tenant_id);

        return DataTables::of($query)
            ->addColumn('actions', function ($row) {
                return '
                    <button onclick="editData('.$row->id.')" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button onclick="deleteData('.$row->id.')" class="btn btn-sm btn-danger ml-1">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                ';
            })
            ->editColumn('spent', function ($row) {
                return number_format($row->spent, 0, ',', '.');
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    // EXISTING METHOD - kept for backward compatibility
    public function getByDate(Request $request)
    {
        $date = Carbon::parse($request->date);
        return OperationalSpent::where('month', $date->month)
            ->where('year', $date->year)
            ->where('tenant_id', Auth::user()->current_tenant_id)
            ->first();
    }

    // NEW METHOD - get by ID for editing
    public function getById(Request $request)
    {
        $operationalSpent = OperationalSpent::where('id', $request->id)
            ->where('tenant_id', Auth::user()->current_tenant_id)
            ->first();

        if (!$operationalSpent) {
            return response()->json([
                'success' => false,
                'message' => 'Operational spent not found'
            ], 404);
        }

        return response()->json($operationalSpent);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|exists:operational_spents,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'spent' => 'required'
        ]);

        $data['spent'] = (float) str_replace(['Rp ', '.', ','], ['', '', '.'], $data['spent']);
        $data['tenant_id'] = Auth::user()->current_tenant_id;
        
        // Check if updating existing record
        if (!empty($data['id'])) {
            $existingRecord = OperationalSpent::where('id', $data['id'])
                ->where('tenant_id', Auth::user()->current_tenant_id)
                ->first();
            
            if (!$existingRecord) {
                return response()->json([
                    'success' => false,
                    'errors' => ['id' => ['Record not found']]
                ], 422);
            }
        }

        OperationalSpent::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
        
        $daysInMonth = Carbon::create($data['year'], $data['month'])->daysInMonth;
        $dailyOperational = $data['spent'] / $daysInMonth;

        NetProfit::query()
            ->whereYear('date', $data['year'])
            ->whereMonth('date', $data['month'])
            ->where('tenant_id', Auth::user()->current_tenant_id)
            ->update(['operasional' => $dailyOperational]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        try {
            $operationalSpent = OperationalSpent::where('id', $request->id)
                ->where('tenant_id', Auth::user()->current_tenant_id)
                ->first();

            if (!$operationalSpent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operational spent not found'
                ], 404);
            }

            // Reset the operational value in NetProfit to 0 when deleting
            NetProfit::query()
                ->whereYear('date', $operationalSpent->year)
                ->whereMonth('date', $operationalSpent->month)
                ->where('tenant_id', Auth::user()->current_tenant_id)
                ->update(['operasional' => 0]);

            $operationalSpent->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete operational spent'
            ], 500);
        }
    }
}