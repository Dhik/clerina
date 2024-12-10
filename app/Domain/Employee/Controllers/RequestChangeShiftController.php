<?php

namespace App\Domain\Employee\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Domain\Employee\Models\RequestChangeShift;
use App\Domain\Employee\Models\Shift;
use App\Domain\Employee\Models\Employee;

class RequestChangeShiftController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $requestChangeShifts = RequestChangeShift::where('employee_id', $employeeId)->get();
        $employees = Employee::all();
        return view('admin.attendance.change_shift.index', compact('requestChangeShifts', 'employees'));
    }
    public function get(Request $request): JsonResponse
    {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $requestChangeShifts = RequestChangeShift::where('employee_id', $employeeId)->get();

        return DataTables::of($requestChangeShifts)->toJson();
    }
    // public function create() {
    //     return view('admin.attendance.change_request.create');
    // }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'starts_shift' => 'nullable',
            'end_shift' => 'nullable',
            'note' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB max size
        ]);
        try {
            $user = Auth::user();
            $employeeId = $user->employee_id;
            $validatedData['employee_id'] = $employeeId;
            $validatedData['status_approval'] = 'Pending';

            if ($request->hasFile('file')) {
                $validatedData['file'] = $request->file('file')->store('change_shift_files', 'public');
            }
            RequestChangeShift::create($validatedData);
            return redirect()
                ->route('requestChangeShifts.index')
                ->with('success', 'Change Shift Request created successfully.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['message' => 'Failed to create request change shift: ' . $e->getMessage()]);
        }
    }
    public function show(RequestChangeShift $requestChangeShift)
    {
        return view('admin.attendance.change_shift.show', compact('requestChangeShift'));
    }

    public function edit(RequestChangeShift $requestChangeShift)
    {
        return view('admin.attendance.change_shift.edit', compact('requestChangeShift'));
    }

    public function update(Request $request, RequestChangeShift $requestChangeShift)
    {
        $request->validate([
            'employee_id' => 'required|string',
            'date' => 'required|date',
            'starts_shift' => 'nullable',
            'end_shift' => 'nullable',
            'status_approval' => 'nullable|string',
            'note' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB max size
        ]);

        if ($request->hasFile('file')) {
            $requestChangeShift->file = $request->file('file')->store('change_shift_files', 'public');
        }

        $requestChangeShift->update($request->all());

        return redirect()->route('requestChangeShifts.index')->with('success', 'Change Shift Request updated successfully.');
    }

    public function destroy(RequestChangeShift $requestChangeShift)
    {
        if ($requestChangeShift->file) {
            \Storage::disk('public')->delete($requestChangeShift->file);
        }
        $requestChangeShift->delete();

        return response()->json(['success' => true, 'message' => 'Change Shift Request deleted successfully.']);
    }

    public function show_all()
    {
        return view('admin.attendance.change_shift.show');
    }

    public function getPendingRequestChangeShifts()
    {
        $pendingRequestChangeShifts = RequestChangeShift::select('request_change_shifts.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'request_change_shifts.employee_id', '=', 'employees.employee_id')
            ->where('request_change_shifts.status_approval', 'pending');

        return DataTables::of($pendingRequestChangeShifts)
            ->addColumn('actions', function ($row) {
                return '
                <a href="#" class="btn btn-sm btn-primary" id="attendanceShow" 
                   data-id="' . $row->id . '" 
                   data-employee_id="' . $row->employee_id . '" 
                   data-full_name="' . $row->full_name . '" 
                   data-date="' . $row->date . '" 
                   data-starts_shift="' . $row->starts_shift . '" 
                   data-end_shift="' . $row->end_shift . '" 
                   data-status="' . $row->status_approval . '" 
                   data-note="' . $row->note . '" 
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

    public function getApprovedRequestChangeShifts()
    {
        $approvedRequestChangeShifts = RequestChangeShift::select('request_change_shifts.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'request_change_shifts.employee_id', '=', 'employees.employee_id')
            ->where('request_change_shifts.status_approval', 'approved');

        return DataTables::of($approvedRequestChangeShifts)
            ->addColumn('actions', function ($row) {
                return '
                <a href="#" class="btn btn-sm btn-primary" id="attendanceShow" 
                   data-id="' . $row->id . '" 
                   data-employee_id="' . $row->employee_id . '" 
                   data-full_name="' . $row->full_name . '" 
                   data-date="' . $row->date . '" 
                   data-starts_shift="' . $row->starts_shift . '" 
                   data-end_shift="' . $row->end_shift . '" 
                   data-status="' . $row->status_approval . '" 
                   data-note="' . $row->note . '" 
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

    public function getRejectedRequestChangeShifts()
    {
        $rejectedRequestChangeShifts = RequestChangeShift::select('request_change_shifts.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'request_change_shifts.employee_id', '=', 'employees.employee_id')
            ->where('request_change_shifts.status_approval', 'rejected');

        return DataTables::of($rejectedRequestChangeShifts)
            ->addColumn('actions', function ($row) {
                return '
                <a href="#" class="btn btn-sm btn-primary" id="attendanceShow" 
                   data-id="' . $row->id . '" 
                   data-employee_id="' . $row->employee_id . '" 
                   data-full_name="' . $row->full_name . '" 
                   data-date="' . $row->date . '" 
                   data-starts_shift="' . $row->starts_shift . '" 
                   data-end_shift="' . $row->end_shift . '" 
                   data-status="' . $row->status_approval . '" 
                   data-note="' . $row->note . '" 
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


    public function updateRequestChangeShiftStatus(Request $request, $id)
    {
        $requestChangeShift = RequestChangeShift::find($id);
        $requestChangeShift->status_approval = $request->status;
        $requestChangeShift->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }
}
