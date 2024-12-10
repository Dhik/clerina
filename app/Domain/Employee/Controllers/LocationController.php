<?php

namespace App\Domain\Employee\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Domain\Employee\Models\Location;
use App\Domain\Employee\Models\Employee;

class LocationController extends Controller
{
    public function index()
    {
        return view('admin.attendance.location.index');
    }

    public function create()
    {
        return view('admin.attendance.location.create');
    }

    public function store(Request $request)
    {
        Location::create($request->all());
        return redirect()->route('location.index');
    }

    public function edit($id)
    {
        $location = Location::with('employees')->findOrFail($id);
        $employees = Employee::whereNull('resign_date')->get();
        return view('admin.attendance.location.edit', compact('location', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);
        $location->update($request->only(['setting_name', 'lat', 'long']));
        $this->assignEmployeesToLocation($location, $request->employees);

        return redirect()->route('location.index')->with('success', 'Location updated successfully.');
    }

    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->employees()->update(['location_id' => null]);
        $location->delete();
        return response()->json(['success' => true]);
    }
    public function show()
    {
        $locations = Location::withCount('employees')->get();

        return DataTables::of($locations)
            ->addColumn('action', function ($location) {
                return '
                    <a href="' . route('location.edit', $location->id) . '" class="btn btn-sm btn-primary">Edit</a>
                    <button class="btn btn-sm btn-danger deleteButton" data-id="' . $location->id . '">Delete</button>
                ';
            })
            ->make(true);
    }
    private function assignEmployeesToLocation($location, $employeeIds)
    {
        // Clear existing location assignments for these employees
        Employee::whereIn('id', $employeeIds)->update(['location_id' => null]);

        // Fetch the employees to be assigned
        $employees = Employee::whereIn('id', $employeeIds)->get();

        // Assign the new location
        $location->employees()->saveMany($employees);
    }
}
