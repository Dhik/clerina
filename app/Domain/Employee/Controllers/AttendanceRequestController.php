<?php

namespace App\Domain\Employee\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Domain\Employee\Models\AttendanceRequest;
use App\Domain\Employee\Models\Attendance;
use App\Domain\Employee\Models\Employee;

class AttendanceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function show_all()
    {
        return view('admin.attendance.log.show');
    }
    public function destroy(AttendanceRequest $attendanceRequest)
    {
        if ($attendanceRequest->file) {
            \Storage::disk('public')->delete($attendanceRequest->file);
        }
        $attendanceRequest->delete();
        return response()->json(['success' => 'Attendance request record deleted successfully']);
    }

    public function getPendingRequests()
    {
        $pendingRequests = AttendanceRequest::select('attendance_requests.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'attendance_requests.employee_id', '=', 'employees.employee_id')
            ->where('attendance_requests.status_approval', 'Pending');

        return DataTables::of($pendingRequests)
            ->editColumn('clock_in', function ($row) {
                return \Carbon\Carbon::parse($row->clock_in)->format('H:i:s');
            })
            ->editColumn('clock_out', function ($row) {
                return \Carbon\Carbon::parse($row->clock_out)->format('H:i:s');
            })
            ->addColumn('actions', function ($row) {
                return '<a href="#" class="btn btn-sm btn-primary" id="attendanceShow" data-id="' . $row->id . '" data-employee_id="' . $row->employee_id . '" data-full_name="' . $row->full_name . '" data-date="' . $row->date . '" data-clock_in="' . $row->clock_in . '" data-clock_out="' . $row->clock_out . '" data-work_note="' . $row->work_note . '" data-file="' . $row->file . '" data-status="' . $row->status_approval . '"><i class="fas fa-eye"></i></a>
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



    public function getApprovedRequests()
    {
        $approvedRequests = AttendanceRequest::select('attendance_requests.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'attendance_requests.employee_id', '=', 'employees.employee_id')
            ->where('attendance_requests.status_approval', 'approved');

        return DataTables::of($approvedRequests)
            ->editColumn('clock_in', function ($row) {
                return \Carbon\Carbon::parse($row->clock_in)->format('H:i:s');
            })
            ->editColumn('clock_out', function ($row) {
                return \Carbon\Carbon::parse($row->clock_out)->format('H:i:s');
            })
            ->addColumn('actions', function ($row) {
                return '<a href="#" class="btn btn-sm btn-primary" id="attendanceShow" data-id="' . $row->id . '" data-employee_id="' . $row->employee_id . '" data-full_name="' . $row->full_name . '" data-date="' . $row->date . '" data-clock_in="' . $row->clock_in . '" data-clock_out="' . $row->clock_out . '" data-work_note="' . $row->work_note . '" data-file="' . $row->file . '" data-status="' . $row->status_approval . '"><i class="fas fa-eye"></i></a>
                        <button class="btn btn-sm btn-warning pendingButton" data-id="' . $row->id . '">Pending</button>
                        <button class="btn btn-danger btn-sm deleteButton"><i class="fas fa-trash-alt"></i></button>';
            })
            ->rawColumns(['actions'])
            ->filterColumn('full_name', function ($query, $keyword) {
                $query->where('employees.full_name', 'like', "%{$keyword}%");
            })
            ->toJson();
    }


    public function getRejectedRequests()
    {
        $rejectedRequests = AttendanceRequest::select('attendance_requests.*', 'employees.full_name', 'employees.profile_picture')
            ->join('employees', 'attendance_requests.employee_id', '=', 'employees.employee_id')
            ->where('attendance_requests.status_approval', 'rejected');

        return DataTables::of($rejectedRequests)
            ->editColumn('clock_in', function ($row) {
                return \Carbon\Carbon::parse($row->clock_in)->format('H:i:s');
            })
            ->editColumn('clock_out', function ($row) {
                return \Carbon\Carbon::parse($row->clock_out)->format('H:i:s');
            })
            ->addColumn('actions', function ($row) {
                return '<a href="#" class="btn btn-sm btn-primary" id="attendanceShow" data-id="' . $row->id . '" data-employee_id="' . $row->employee_id . '" data-full_name="' . $row->full_name . '" data-date="' . $row->date . '" data-clock_in="' . $row->clock_in . '" data-clock_out="' . $row->clock_out . '" data-work_note="' . $row->work_note . '" data-file="' . $row->file . '" data-status="' . $row->status_approval . '"><i class="fas fa-eye"></i></a>
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


    public function updateRequestStatus(Request $request, $id)
    {
        $attendanceRequest = AttendanceRequest::find($id);
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $employee = Employee::where('employee_id', $employeeId)->first();
        $shiftId = $employee->shift_id;
        if ($attendanceRequest) {
            $attendanceRequest->status_approval = $request->status;
            $attendanceRequest->save();

            if ($request->status == 'approved') {
                $attendance = Attendance::where('employee_id', $attendanceRequest->employee_id)
                    ->whereDate('created_at', $attendanceRequest->date)
                    ->first();

                if ($attendance) {
                    $attendance->clock_in = $attendanceRequest->clock_in;
                    $attendance->clock_out = $attendanceRequest->clock_out;
                    $attendance->save();
                } else {
                    // Create new attendance record
                    Attendance::create([
                        'shift_id' => $shiftId,
                        'attendance_status' => 'present',
                        'clock_in' => $attendanceRequest->clock_in,
                        'clock_out' => $attendanceRequest->clock_out,
                        'employee_id' => $employeeId,
                        'date' => $attendanceRequest->date,
                    ]);
                }
            }
            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        }
        return response()->json(['success' => false, 'message' => 'Attendance request not found'], 404);
    }
}
