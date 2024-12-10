<?php

namespace App\Domain\Employee\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Domain\Employee\Models\Overtime;
use App\Domain\Employee\Models\Shift;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $overtimes = Overtime::where('employee_id', $employeeId)->get();

        //add shift
        $shifts = Shift::all();

        return view('admin.attendance.overtime.index', compact('overtimes', 'shifts'));
    }
    public function get(Request $request): JsonResponse
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $overtimes = Overtime::where('employee_id', $employeeId)->get();

        return DataTables::of($overtimes)
            ->toJson();
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('overtimes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required',
            'shift_id' => 'required',
            'compensation' => 'required',
            'before_shift_overtime_duration' => 'nullable',
            'before_shift_break_duration' => 'nullable',
            'after_shift_overtime_duration' => 'nullable',
            'after_shift_break_duration' => 'nullable',
            'note' => 'nullable',
            'file' => 'nullable|file|max:20480', // 10MB max size
        ]);

        try {
            $user = Auth::user();
            $employeeId = $user->employee_id;
            $validatedData['employee_id'] = $employeeId;
            $validatedData['status_approval'] = 'Pending';

            // Check if the overtime data already exists
            $existingOvertime = Overtime::where('employee_id', $employeeId)
                ->where('date', $validatedData['date'])
                ->where('shift_id', $validatedData['shift_id'])
                ->first();

            if (!$existingOvertime) {
                if ($request->hasFile('file')) {
                    $validatedData['file'] = $request->file('file')->store('overtime_files', 'public');
                }

                Overtime::create($validatedData);

                return redirect()
                    ->route('overtimes.index')
                    ->with('success', 'Overtime created successfully.');
            } else {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['message' => 'Overtime entry already exists for this date and shift.']);
            }
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['message' => 'Failed to create overtime: ' . $e->getMessage()]);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Overtime $overtime)
    {
        return view('overtimes.show', compact('overtime'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Overtime $overtime)
    {
        return view('overtimes.edit', compact('overtime'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Overtime $overtime)
    {
        $request->validate([
            'date' => 'required',
            'shift' => 'required',
            'compensation' => 'required',
            'before_shift_overtime_duration' => 'required',
            'before_shift_break_duration' => 'required',
            'after_shift_overtime_duration' => 'required',
            'after_shift_break_duration' => 'required',
            'note' => 'nullable',
            'file' => 'nullable|file|max:10240', // 10MB max size
            'status_approval' => 'required',
        ]);

        $data = $request->all();

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('overtime_files', 'public');
        }

        $overtime->update($data);

        return redirect()->route('overtimes.index')->with('success', 'Overtime updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Overtime $overtime)
    {
        if ($overtime->file) {
            \Storage::disk('public')->delete($overtime->file);
        }
        $overtime->delete();
        return response()->json(['success' => 'Overtime record deleted successfully']);
    }

    public function show_all()
    {
        return view('admin.attendance.overtime.show');
    }

    public function getPendingOvertime()
    {
        $pendingOvertime = Overtime::select('overtimes.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'overtimes.employee_id', '=', 'employees.employee_id')
            ->where('overtimes.status_approval', 'pending');

        return DataTables::of($pendingOvertime)
            ->addColumn('actions', function ($row) {
                return '
                    <a href="#" class="btn btn-sm btn-primary" id="overtimeShow" 
                       data-id="' . $row->id . '" 
                       data-employee_id="' . $row->employee_id . '" 
                       data-full_name="' . $row->full_name . '" 
                       data-date="' . $row->date . '" 
                       data-compensation="' . $row->compensation . '" 
                       data-before_shift_overtime_duration="' . $row->before_shift_overtime_duration . '" 
                       data-before_shift_break_duration="' . $row->before_shift_break_duration . '" 
                       data-after_shift_overtime_duration="' . $row->after_shift_overtime_duration . '" 
                       data-after_shift_break_duration="' . $row->after_shift_break_duration . '" 
                       data-note="' . $row->note . '" 
                       data-file="' . $row->file . '" 
                       data-status="' . $row->status_approval . '">
                       <i class="fas fa-eye"></i>
                    </a>
                    <button class="btn btn-sm btn-success approveButton" data-id="' . $row->id . '">Approve</button>
                    <button class="btn btn-sm btn-danger rejectButton" data-id="' . $row->id . '">Reject</button>
                    <button class="btn btn-danger btn-sm deleteButton"><i class="fas fa-trash-alt"></i></button>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function getApprovedOvertime()
    {
        $approvedOvertime = Overtime::select('overtimes.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'overtimes.employee_id', '=', 'employees.employee_id')
            ->where('overtimes.status_approval', 'approved');

        return DataTables::of($approvedOvertime)
            ->addColumn('actions', function ($row) {
                return '
                    <a href="#" class="btn btn-sm btn-primary" id="overtimeShow" 
                       data-id="' . $row->id . '" 
                       data-employee_id="' . $row->employee_id . '" 
                       data-full_name="' . $row->full_name . '" 
                       data-date="' . $row->date . '" 
                       data-compensation="' . $row->compensation . '" 
                       data-before_shift_overtime_duration="' . $row->before_shift_overtime_duration . '" 
                       data-before_shift_break_duration="' . $row->before_shift_break_duration . '" 
                       data-after_shift_overtime_duration="' . $row->after_shift_overtime_duration . '" 
                       data-after_shift_break_duration="' . $row->after_shift_break_duration . '" 
                       data-note="' . $row->note . '" 
                       data-file="' . $row->file . '" 
                       data-status="' . $row->status_approval . '">
                       <i class="fas fa-eye"></i>
                    </a>
                    <button class="btn btn-sm btn-warning pendingButton" data-id="' . $row->id . '">Pending</button>
                    <button class="btn btn-danger btn-sm deleteButton"><i class="fas fa-trash-alt"></i></button>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function getRejectedOvertime()
    {
        $rejectedOvertime = Overtime::select('overtimes.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'overtimes.employee_id', '=', 'employees.employee_id')
            ->where('overtimes.status_approval', 'rejected');

        return DataTables::of($rejectedOvertime)
            ->addColumn('actions', function ($row) {
                return '
                    <a href="#" class="btn btn-sm btn-primary" id="overtimeShow" 
                       data-id="' . $row->id . '" 
                       data-employee_id="' . $row->employee_id . '" 
                       data-full_name="' . $row->full_name . '" 
                       data-date="' . $row->date . '" 
                       data-compensation="' . $row->compensation . '" 
                       data-before_shift_overtime_duration="' . $row->before_shift_overtime_duration . '" 
                       data-before_shift_break_duration="' . $row->before_shift_break_duration . '" 
                       data-after_shift_overtime_duration="' . $row->after_shift_overtime_duration . '" 
                       data-after_shift_break_duration="' . $row->after_shift_break_duration . '" 
                       data-note="' . $row->note . '" 
                       data-file="' . $row->file . '" 
                       data-status="' . $row->status_approval . '">
                       <i class="fas fa-eye"></i>
                    </a>
                    <button class="btn btn-sm btn-warning pendingButton" data-id="' . $row->id . '">Pending</button>
                    <button class="btn btn-sm btn-success approveButton" data-id="' . $row->id . '">Approve</button>
                    <button class="btn btn-danger btn-sm deleteButton"><i class="fas fa-trash-alt"></i></button>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function updateOvertimeStatus(Request $request, $id)
    {
        $overtime = Overtime::find($id);
        if ($overtime) {
            $overtime->status_approval = $request->status;
            $overtime->save();
            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        }
        return response()->json(['success' => false, 'message' => 'Overtime not found'], 404);
    }
}
