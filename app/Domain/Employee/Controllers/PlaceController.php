<?php

namespace App\Domain\Employee\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Domain\Employee\Models\Place;
use App\Domain\Employee\Models\Employee;

class PlaceController extends Controller
{
    public function index()
    {
        return view('admin.attendance.place.index');
    }

    public function create()
    {
        return view('admin.attendance.place.create');
    }

    public function store(Request $request)
    {
        Place::create($request->all());
        return redirect()->route('place.index');
    }

    public function edit($id)
    {
        $place = Place::with('employees')->findOrFail($id);
        $employees = Employee::all();
        return view('admin.attendance.place.edit', compact('place', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $place = Place::findOrFail($id);
        $place->update($request->only(['place']));
        $this->assignEmployeesToPlace($place, $request->employees);

        return redirect()->route('place.index')->with('success', 'Place updated successfully.');
    }

    public function destroy($id)
    {
        $place = Place::findOrFail($id);
        $place->employees()->update(['place_id' => null]);
        $place->delete();
        return response()->json(['success' => true]);
    }

    public function show()
    {
        $places = Place::withCount('employees')->get();

        return DataTables::of($places)
            ->addColumn('action', function ($place) {
                return '
                    <a href="'.route('place.edit', $place->id).'" class="btn btn-sm btn-primary">Edit</a>
                    <button class="btn btn-sm btn-danger deleteButton" data-id="'.$place->id.'">Delete</button>
                ';
            })
            ->make(true);
    }

    private function assignEmployeesToPlace($place, $employeeIds)
    {
        // Clear existing place assignments for these employees
        Employee::whereIn('id', $employeeIds)->update(['place_id' => null]);

        // Fetch the employees to be assigned
        $employees = Employee::whereIn('id', $employeeIds)->get();

        // Assign the new place
        $place->employees()->saveMany($employees);
    }
}
