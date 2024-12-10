<?php

namespace App\Domain\Employee\Controllers;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Utilities\Request;
use Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Domain\Employee\Models\TimeOff;
use App\Domain\Employee\Models\Shift;
use App\Domain\Employee\Models\Employee;

class TimeOffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $timeOffs = TimeOff::where('employee_id', $employeeId)->get();
        $employees = Employee::all();
        return view('admin.attendance.timeoff.index', compact('timeOffs', 'employees'));
    }

    public function get(Request $request): JsonResponse
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $timeOffs = TimeOff::where('employee_id', $employeeId)->get();

        return DataTables::of($timeOffs)
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.attendance.timeoff.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'time_off_type' => 'required|string',
            'date' => 'required|date',
            'request_type' => 'nullable|string',
            'reason' => 'required|string',
            'delegate_to' => 'string',
            'file' => 'nullable|file|max:10240', // 10MB max size
        ]);

        try {
            $user = Auth::user();
            $employeeId = $user->employee_id;
            $validatedData['employee_id'] = $employeeId;
            $validatedData['status_approval'] = 'Pending';

            // Check if the time off request already exists
            $existingTimeOff = TimeOff::where('employee_id', $employeeId)
                ->where('date', $validatedData['date'])
                ->where('time_off_type', $validatedData['time_off_type'])
                ->first();

            if (!$existingTimeOff) {
                if ($request->hasFile('file')) {
                    $validatedData['file'] = $request->file('file')->store('timeoff_files', 'public');
                }

                TimeOff::create($validatedData);

                return redirect()
                    ->route('timeoffs.index')
                    ->with('success', 'Time Off created successfully.');
            } else {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['message' => 'Time off request already exists for this date and type.']);
            }
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['message' => 'Failed to create time off request: ' . $e->getMessage()]);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(TimeOff $timeOff)
    {
        return view('admin.attendance.timeoff.show', compact('timeOff'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeOff $timeOff)
    {
        return view('admin.attendance.timeoff.edit', compact('timeOff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimeOff $timeOff)
    {
        $request->validate([
            'time_off_type' => 'required',
            'date' => 'required',
            'request_type' => 'required',
            'reason' => 'required',
            'delegate_to' => 'required',
            'employee_id' => 'required',
            'status_approval' => 'required',
        ]);

        $timeOff->update($request->all());
        return redirect()->route('timeOffs.index')->with('success', 'Time Off updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeOff $timeOff)
    {
        if ($timeOff->file) {
            \Storage::disk('public')->delete($timeOff->file);
        }
        $timeOff->delete();
        return response()->json(['success' => 'Time off record deleted successfully']);
    }

    public function show_all()
    {
        return view('admin.attendance.timeoff.show');
    }
    public function getPendingTimeOffs()
    {
        $pendingTimeOffs = TimeOff::select('time_offs.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'time_offs.employee_id', '=', 'employees.employee_id')
            ->where('time_offs.status_approval', 'pending');

        return DataTables::of($pendingTimeOffs)
            ->addColumn('actions', function ($row) {
                return '
                <a href="#" class="btn btn-sm btn-primary" id="attendanceShow" 
                   data-id="' . $row->id . '" 
                   data-employee_id="' . $row->employee_id . '" 
                   data-full_name="' . $row->full_name . '" 
                   data-date="' . $row->date . '" 
                   data-reason="' . $row->reason . '" 
                   data-status="' . $row->status_approval . '" 
                   data-time_off_type="' . $row->time_off_type . '" 
                   data-request_type="' . $row->request_type . '" 
                   data-delegate_to="' . $row->delegate_to . '" 
                   data-file="' . $row->file . '">
                   <i class="fas fa-eye"></i>
                </a>
                <button class="btn btn-sm btn-success approveButton" data-id="' . $row->id . '">Approve</button>
                <button class="btn btn-sm btn-danger rejectButton" data-id="' . $row->id . '">Reject</button>
                <button class="btn btn-danger btn-sm deleteButton"><i class="fas fa-trash-alt"></i></button>';
            })
            ->rawColumns(['actions'])
            ->filterColumn('full_name', function ($query, $keyword) {
                $query->where('employees.full_name', 'like', "%{$keyword}%");
            })
            ->toJson();
    }

    public function getApprovedTimeOffs()
    {
        $approvedTimeOffs = TimeOff::select('time_offs.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'time_offs.employee_id', '=', 'employees.employee_id')
            ->where('time_offs.status_approval', 'approved');

        return DataTables::of($approvedTimeOffs)
            ->addColumn('actions', function ($row) {
                return '
                <a href="#" class="btn btn-sm btn-primary" id="attendanceShow" 
                   data-id="' . $row->id . '" 
                   data-employee_id="' . $row->employee_id . '" 
                   data-full_name="' . $row->full_name . '" 
                   data-date="' . $row->date . '" 
                   data-reason="' . $row->reason . '" 
                   data-status="' . $row->status_approval . '" 
                   data-time_off_type="' . $row->time_off_type . '" 
                   data-request_type="' . $row->request_type . '" 
                   data-delegate_to="' . $row->delegate_to . '" 
                   data-file="' . $row->file . '">
                   <i class="fas fa-eye"></i>
                </a>
                <button class="btn btn-sm btn-warning pendingButton" data-id="' . $row->id . '">Pending</button>
                <button class="btn btn-danger btn-sm deleteButton"><i class="fas fa-trash-alt"></i></button>';
            })
            ->rawColumns(['actions'])
            ->filterColumn('full_name', function ($query, $keyword) {
                $query->where('employees.full_name', 'like', "%{$keyword}%");
            })
            ->toJson();
    }

    public function getRejectedTimeOffs()
    {
        $rejectedTimeOffs = TimeOff::select('time_offs.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'time_offs.employee_id', '=', 'employees.employee_id')
            ->where('time_offs.status_approval', 'rejected');

        return DataTables::of($rejectedTimeOffs)
            ->addColumn('actions', function ($row) {
                return '
                <a href="#" class="btn btn-sm btn-primary" id="attendanceShow" 
                   data-id="' . $row->id . '" 
                   data-employee_id="' . $row->employee_id . '" 
                   data-full_name="' . $row->full_name . '" 
                   data-date="' . $row->date . '" 
                   data-reason="' . $row->reason . '" 
                   data-status="' . $row->status_approval . '" 
                   data-time_off_type="' . $row->time_off_type . '" 
                   data-request_type="' . $row->request_type . '" 
                   data-delegate_to="' . $row->delegate_to . '" 
                   data-file="' . $row->file . '">
                   <i class="fas fa-eye"></i>
                </a>
                <button class="btn btn-sm btn-warning pendingButton" data-id="' . $row->id . '">Pending</button>
                <button class="btn btn-sm btn-success approveButton" data-id="' . $row->id . '">Approve</button>
                <button class="btn btn-danger btn-sm deleteButton"><i class="fas fa-trash-alt"></i></button>';
            })
            ->rawColumns(['actions'])
            ->filterColumn('full_name', function ($query, $keyword) {
                $query->where('employees.full_name', 'like', "%{$keyword}%");
            })
            ->toJson();
    }


    public function updateTimeOffStatus(Request $request, $id)
    {
        $timeOff = TimeOff::find($id);
        if ($timeOff) {
            $timeOff->status_approval = $request->status;
            $timeOff->save();
            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        }
        return response()->json(['success' => false, 'message' => 'Time off not found'], 404);
    }
}
