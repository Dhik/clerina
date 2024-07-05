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

class ChangeRequestController extends Controller
{
    public function index() {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $change = RequestChangeShift::where('employee_id', $employeeId)->get();
        $employees = Employee::all();
        return view('admin.attendance.change_shift.index', compact('timeOffs', 'employees'));
    }
    public function get(Request $request): JsonResponse {
        $user = Auth::user();
        $employeeId = $user->employee_id;
        $change = RequestChangeShift::where('employee_id', $employeeId)->get();

        return DataTables::of($timeOffs)
            ->toJson();
    }
    public function create() {
        return view('admin.attendance.change_request.create');
    }
    public function store() {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'change_shift_id' => 'required',
            'note' => 'nullable',
            'clocktime' => 'nullable',
            'file' => 'nullable|file|max:10240',
        ]);
        try {
            $user = Auth::user();
            $employeeId = $user->employee_id;
            $validatedData['employee_id'] = $employeeId;
            $validatedData['status_approval'] = 'Pending';
            if ($request->hasFile('file')) {
                $validatedData['file'] = $request->file('file')->store('change_request_files', 'public');
            }
            RequestChangeShift::create($validatedData);
        }
        catch (Exception $e) {

        }
    }

}