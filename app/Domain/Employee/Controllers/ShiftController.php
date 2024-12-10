<?php

namespace App\Domain\Employee\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Domain\Employee\Models\Shift;
use App\Domain\Employee\Models\Employee;

class ShiftController extends Controller
{
    public function index()
    {
        return view('admin.attendance.shift.index');
    }

    public function getData()
    {
        $shifts = Shift::withCount('employees')->get();

        return DataTables::of($shifts)
            ->addColumn('action', function ($shift) {
                return '
                    <a href="' . route('shift.edit', $shift->id) . '" class="btn btn-sm btn-primary">Edit</a>
                    <button class="btn btn-sm btn-danger deleteButton" data-id="' . $shift->id . '">Delete</button>
                ';
            })
            ->make(true);
    }

    public function create()
    {
        $employees = Employee::all();
        return view('admin.attendance.shift.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $shift = Shift::create($request->only(['shift_name', 'schedule_in', 'schedule_out']));
        $this->assignEmployeesToShift($shift, $request->employees);

        return redirect()->route('shift.index');
    }

    public function edit($id)
    {
        $shift = Shift::with('employees')->findOrFail($id);
        $employees = Employee::all();
        return view('admin.attendance.shift.edit', compact('shift', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);
        $shift->update($request->only(['shift_name', 'schedule_in', 'schedule_out']));
        $this->assignEmployeesToShift($shift, $request->employees);

        return redirect()->route('shift.index');
    }

    private function assignEmployeesToShift($shift, $employeeIds)
    {
        Employee::whereIn('id', $employeeIds)->update(['shift_id' => null]);
        $employees = Employee::whereIn('id', $employeeIds)->get();
        $shift->employees()->saveMany($employees);
    }

    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->employees()->update(['shift_id' => null]);
        $shift->delete();
        return response()->json(['success' => true]);
    }
}
