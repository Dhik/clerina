<?php

namespace App\Domain\KPIEmployee\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\KPIEmployee\Models\KPIEmployee;
use App\Domain\KPIEmployee\Requests\KPIEmployeeRequest;
use App\Domain\Employee\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KPIEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get dashboard data for charts
        $departmentStats = $this->getDepartmentStats();
        $positionStats = $this->getPositionStats();
        $overallStats = $this->getOverallStats();
        
        return view('admin.kpi-employee.index', compact('departmentStats', 'positionStats', 'overallStats'));
    }

    /**
     * Get department statistics
     */
    private function getDepartmentStats()
    {
        $departments = ['Sales', 'Marketing (Ads)', 'Marketing (CRM)', 'Human Capital', 'KOL Admin', 'Affiliate Admin', 'Finance'];
        $stats = [];
        
        foreach ($departments as $department) {
            $kpis = KPIEmployee::where('department', $department)->get();
            $totalEmployees = $kpis->groupBy('employee_id')->count();
            $totalKpis = $kpis->count();
            
            if ($totalKpis > 0) {
                $totalAchievement = 0;
                foreach ($kpis as $kpi) {
                    if ($kpi->target > 0) {
                        $achievement = ($kpi->actual / $kpi->target) * 100;
                        $totalAchievement += $achievement * ($kpi->bobot / 100);
                    }
                }
                $avgAchievement = $totalAchievement / $totalKpis;
            } else {
                $avgAchievement = 0;
            }
            
            if ($totalEmployees > 0) {
                $stats[] = [
                    'department' => $department,
                    'employees' => $totalEmployees,
                    'kpis' => $totalKpis,
                    'achievement' => round($avgAchievement, 2)
                ];
            }
        }
        
        return $stats;
    }

    /**
     * Get position statistics
     */
    private function getPositionStats()
    {
        $positions = ['Leader', 'Staff'];
        $stats = [];
        
        foreach ($positions as $position) {
            $kpis = KPIEmployee::where('position', $position)->get();
            $totalEmployees = $kpis->groupBy('employee_id')->count();
            $totalKpis = $kpis->count();
            
            if ($totalKpis > 0) {
                $totalAchievement = 0;
                foreach ($kpis as $kpi) {
                    if ($kpi->target > 0) {
                        $achievement = ($kpi->actual / $kpi->target) * 100;
                        $totalAchievement += $achievement * ($kpi->bobot / 100);
                    }
                }
                $avgAchievement = $totalAchievement / $totalKpis;
            } else {
                $avgAchievement = 0;
            }
            
            if ($totalEmployees > 0) {
                $stats[] = [
                    'position' => $position,
                    'employees' => $totalEmployees,
                    'kpis' => $totalKpis,
                    'achievement' => round($avgAchievement, 2)
                ];
            }
        }
        
        return $stats;
    }

    /**
     * Get overall statistics
     */
    private function getOverallStats()
    {
        $totalEmployees = Employee::whereHas('kpiEmployees')->count();
        $totalKpis = KPIEmployee::count();
        $avgWeight = KPIEmployee::avg('bobot');
        
        $kpis = KPIEmployee::all();
        $totalAchievement = 0;
        $validKpis = 0;
        
        foreach ($kpis as $kpi) {
            if ($kpi->target > 0) {
                $achievement = ($kpi->actual / $kpi->target) * 100;
                $totalAchievement += $achievement;
                $validKpis++;
            }
        }
        
        $avgAchievement = $validKpis > 0 ? $totalAchievement / $validKpis : 0;
        
        return [
            'employees' => $totalEmployees,
            'kpis' => $totalKpis,
            'avg_weight' => round($avgWeight, 2),
            'avg_achievement' => round($avgAchievement, 2)
        ];
    }

    public function myKpi()
    {
        // Get current user's employee data
        $currentUser = auth()->user();
        $currentEmployee = Employee::where('email', $currentUser->email)->first();
        
        if (!$currentEmployee) {
            return redirect()->route('kPIEmployee.index')
                ->with('error', 'Employee record not found. Please contact administrator.');
        }
        
        $currentEmployee->load('kpiEmployees');
        
        // Calculate personal statistics
        $kpis = $currentEmployee->kpiEmployees;
        $totalKpis = $kpis->count();
        $totalWeight = $kpis->sum('bobot');
        
        // Calculate total achievement
        $totalAchievement = 0;
        $achievedKpis = 0;
        
        foreach ($kpis as $kpi) {
            if ($kpi->target > 0) {
                $achievement = ($kpi->actual / $kpi->target) * 100;
                $totalAchievement += $achievement * ($kpi->bobot / 100);
                if ($achievement >= 100) {
                    $achievedKpis++;
                }
            }
        }
        
        $avgAchievement = $totalKpis > 0 ? $totalAchievement : 0;
        
        // Get KPIs by perspective
        $kpisByPerspective = $kpis->groupBy('perspective');
        
        // Check if employee is a leader
        $hasLeaderKpis = $kpis->where('position', 'Leader')->count() > 0;
        
        $personalStats = [
            'total_kpis' => $totalKpis,
            'total_weight' => $totalWeight,
            'avg_achievement' => round($avgAchievement, 2),
            'achieved_kpis' => $achievedKpis,
            'is_leader' => $hasLeaderKpis
        ];
        
        return view('admin.kpi-employee.my-kpi', compact('currentEmployee', 'kpis', 'personalStats', 'kpisByPerspective'));
    }

    /**
     * Get data for DataTables
     */
    public function data()
    {
        $employees = Employee::whereHas('kpiEmployees')
            ->with(['kpiEmployees'])
            ->get();

        return DataTables::of($employees)
            ->addColumn('employee_info', function ($employee) {
                return '<strong>' . $employee->full_name . '</strong><br>' .
                       '<small>ID: ' . $employee->employee_id . '</small><br>' .
                       '<small>' . $employee->organization . ' - ' . $employee->job_position . '</small>';
            })
            ->addColumn('kpi_count', function ($employee) {
                return $employee->kpiEmployees->count() . ' KPI(s)';
            })
            ->addColumn('total_weight', function ($employee) {
                return $employee->kpiEmployees->sum('bobot') . '%';
            })
            ->addColumn('avg_achievement', function ($employee) {
                $kpis = $employee->kpiEmployees;
                if ($kpis->count() == 0) return '0%';
                
                $totalAchievement = 0;
                foreach ($kpis as $kpi) {
                    if ($kpi->target > 0) {
                        $achievement = ($kpi->actual / $kpi->target) * 100;
                        $totalAchievement += $achievement * ($kpi->bobot / 100);
                    }
                }
                return number_format($totalAchievement, 2) . '%';
            })
            ->addColumn('action', function ($employee) {
                return '<a href="' . route('kPIEmployee.show', $employee->id) . '" class="btn btn-sm btn-info">View Detail</a>';
            })
            ->rawColumns(['employee_info', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::select('id', 'employee_id', 'full_name', 'organization', 'job_position')
            ->orderBy('full_name')
            ->get();

        $departments = [
            'Sales',
            'Marketing (Ads)',
            'Marketing (CRM)',
            'Human Capital',
            'KOL Admin',
            'Affiliate Admin',
            'Finance'
        ];

        $positions = ['Leader', 'Staff'];
        $methods = ['higher better', 'lower better'];
        $perspectives = ['Financial', 'Customer', 'Business Process', 'Learn & Growth'];

        return view('admin.kpi-employee.create', compact('employees', 'departments', 'positions', 'methods', 'perspectives'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KPIEmployeeRequest $request)
    {
        $data = $request->validated();
        $employeeIds = $data['employee_id'];
        unset($data['employee_id']);
        
        // Set actual to 0 for new KPI
        $data['actual'] = 0;

        // Create KPI for each selected employee
        foreach ($employeeIds as $employeeId) {
            $data['employee_id'] = $employeeId;
            KPIEmployee::create($data);
        }

        return redirect()->route('kPIEmployee.index')
            ->with('success', 'KPI Employee created successfully for ' . count($employeeIds) . ' employee(s).');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load('kpiEmployees');
        
        // The currentEmployee is the employee whose detail page we're viewing
        $currentEmployee = $employee;
        
        // Check if the viewed employee has any Leader KPIs
        $hasLeaderKpis = $employee->kpiEmployees->where('position', 'Leader')->count() > 0;
        $departmentStaff = collect();
        
        // If this employee has leader KPIs, get staff data from their departments
        if ($hasLeaderKpis) {
            $leaderDepartments = $employee->kpiEmployees->where('position', 'Leader')->pluck('department')->unique();
            
            // Get all staff KPIs in leader's departments
            $departmentStaff = KPIEmployee::with('employee')
                                         ->whereIn('department', $leaderDepartments)
                                         ->where('position', 'Staff')
                                         ->get();
        }

        // Debug information - remove this after testing
        logger()->info('KPI Debug Info', [
            'viewed_employee_id' => $employee->id,
            'viewed_employee_employee_id' => $employee->employee_id,
            'hasLeaderKpis' => $hasLeaderKpis,
            'departmentStaff_count' => $departmentStaff->count(),
            'leaderKpis_count' => $employee->kpiEmployees->where('position', 'Leader')->count()
        ]);
        
        return view('admin.kpi-employee.show', compact('employee', 'hasLeaderKpis', 'departmentStaff', 'currentEmployee'));
    }

    /**
     * Get KPI data for specific employee
     */
    public function getKpiData(Employee $employee)
    {
        // Get current user's employee data
        $currentUser = auth()->user();
        $currentEmployee = Employee::where('email', $currentUser->email)->first();
        
        // Check if current employee is a leader
        $isLeader = false;
        $canInputActual = false;
        
        if ($currentEmployee) {
            $leaderKpis = KPIEmployee::where('employee_id', $currentEmployee->employee_id)
                                   ->where('position', 'Leader')
                                   ->get();
            
            if ($leaderKpis->count() > 0) {
                $isLeader = true;
                $leaderDepartments = $leaderKpis->pluck('department')->unique();
                
                // Check if the viewed employee is in leader's department
                $viewedEmployeeKpis = $employee->kpiEmployees->whereIn('department', $leaderDepartments);
                $canInputActual = $viewedEmployeeKpis->count() > 0;
            }
        }
        
        // If viewing own profile, always show own KPIs
        $kpis = $employee->kpiEmployees;
        
        return DataTables::of($kpis)
            ->addColumn('achievement', function ($kpi) {
                if ($kpi->target > 0) {
                    $achievement = ($kpi->actual / $kpi->target) * 100;
                    $class = $achievement >= 100 ? 'success' : ($achievement >= 75 ? 'warning' : 'danger');
                    return '<span class="badge badge-' . $class . '">' . number_format($achievement, 2) . '%</span>';
                }
                return '<span class="badge badge-secondary">0%</span>';
            })
            ->addColumn('action', function ($kpi) use ($canInputActual, $currentEmployee, $employee) {
                $actions = '<div class="btn-group">';
                
                // Edit button - only for own KPIs or if leader
                if ($currentEmployee && ($currentEmployee->employee_id === $employee->employee_id || $canInputActual)) {
                    $actions .= '<a href="' . route('kPIEmployee.edit', $kpi->id) . '" class="btn btn-sm btn-warning">Edit</a>';
                }
                
                // Input Actual button - only for leaders of staff's department
                if ($canInputActual && $kpi->position === 'Staff') {
                    $actions .= '<a href="' . route('kPIEmployee.inputActual', $kpi->id) . '" class="btn btn-sm btn-success">Input Actual</a>';
                }
                
                // Delete button - only for own KPIs or if leader
                if ($currentEmployee && ($currentEmployee->employee_id === $employee->employee_id || $canInputActual)) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteKpi(' . $kpi->id . ')">Delete</button>';
                }
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['achievement', 'action'])
            ->make(true);
    }

    /**
     * Get staff KPI data for leader's department (individual KPIs)
     */
    public function getStaffKpiData(Request $request)
    {
        $employeeId = $request->get('employee_id');
        
        if (!$employeeId) {
            return response()->json(['error' => 'Employee ID required'], 400);
        }
        
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        
        // Get the leader's departments
        $leaderDepartments = KPIEmployee::where('employee_id', $employee->employee_id)
                                      ->where('position', 'Leader')
                                      ->pluck('department')
                                      ->unique();
        
        if ($leaderDepartments->count() == 0) {
            return response()->json(['error' => 'Not a leader'], 403);
        }
        
        // Get all staff KPIs in leader's departments
        $staffKpis = KPIEmployee::with('employee')
                               ->whereIn('department', $leaderDepartments)
                               ->where('position', 'Staff')
                               ->get();

        return DataTables::of($staffKpis)
            ->addColumn('employee_name', function ($kpi) {
                return $kpi->employee ? $kpi->employee->full_name : 'N/A';
            })
            ->addColumn('achievement', function ($kpi) {
                if ($kpi->target > 0) {
                    if ($kpi->method_calculation === 'higher better') {
                        // For "higher better": actual/target * 100
                        if ($kpi->target == 0) {
                            $achievement = $kpi->actual > 0 ? 100 : 0; // or handle as needed
                        } else {
                            $achievement = ($kpi->actual / $kpi->target) * 100;
                        }
                    } else {
                        // For "lower better": target/actual * 100  
                        if ($kpi->actual == 0) {
                            $achievement = $kpi->target > 0 ? 0 : 100; // or handle as needed
                        } else {
                            $achievement = ($kpi->target / $kpi->actual) * 100;
                        }
                    }
                    $class = $achievement >= 100 ? 'success' : ($achievement >= 75 ? 'warning' : 'danger');
                    return '<span class="badge badge-' . $class . '">' . number_format($achievement, 2) . '%</span>';
                }
                return '<span class="badge badge-secondary">0%</span>';
            })
            ->addColumn('action', function ($kpi) {
                $actions = '<div class="btn-group btn-group-sm">';
                $actions .= '<a href="' . route('kPIEmployee.edit', $kpi->id) . '" class="btn btn-warning btn-xs">Edit</a>';
                $actions .= '<a href="' . route('kPIEmployee.inputActual', $kpi->id) . '" class="btn btn-success btn-xs">Input Actual</a>';
                $actions .= '<button type="button" class="btn btn-danger btn-xs" onclick="deleteKpi(' . $kpi->id . ')">Delete</button>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['achievement', 'action'])
            ->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KPIEmployee $kPIEmployee)
    {
        $employees = Employee::select('id', 'employee_id', 'full_name', 'organization', 'job_position')
            ->orderBy('full_name')
            ->get();

        $departments = [
            'Sales',
            'Marketing (Ads)',
            'Marketing (CRM)',
            'Human Capital',
            'KOL Admin',
            'Affiliate Admin',
            'Finance'
        ];

        $positions = ['Leader', 'Staff'];
        $methods = ['higher better', 'lower better'];
        $perspectives = ['Financial', 'Customer', 'Business Process', 'Learn & Growth'];

        return view('admin.kpi-employee.edit', compact('kPIEmployee', 'employees', 'departments', 'positions', 'methods', 'perspectives'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KPIEmployeeRequest $request, KPIEmployee $kPIEmployee)
    {
        $data = $request->validated();
        
        // Remove employee_id array for update (single employee)
        if (isset($data['employee_id']) && is_array($data['employee_id'])) {
            $data['employee_id'] = $data['employee_id'][0];
        }

        $kPIEmployee->update($data);

        return redirect()->route('kPIEmployee.show', $kPIEmployee->employee->id)
            ->with('success', 'KPI Employee updated successfully.');
    }

    /**
     * Show form for inputting actual value
     */
    public function inputActual(KPIEmployee $kPIEmployee)
    {
        // Get current user's employee data
        $currentUser = auth()->user();
        $currentEmployee = Employee::where('email', $currentUser->email)->first();
        
        if (!$currentEmployee) {
            return redirect()->route('kPIEmployee.index')
                ->with('error', 'Employee record not found.');
        }
        
        // Check if current employee is a leader and can input actual for this KPI
        $canInputActual = false;
        
        if ($kPIEmployee->position === 'Staff') {
            // Only leaders can input actual for staff
            $leaderKpis = KPIEmployee::where('employee_id', $currentEmployee->employee_id)
                                   ->where('position', 'Leader')
                                   ->get();
            
            if ($leaderKpis->count() > 0) {
                $leaderDepartments = $leaderKpis->pluck('department')->unique();
                $canInputActual = $leaderDepartments->contains($kPIEmployee->department);
            }
        } else {
            // Leaders can input actual for their own KPIs
            $canInputActual = ($currentEmployee->employee_id === $kPIEmployee->employee_id);
        }
        
        if (!$canInputActual) {
            return redirect()->route('kPIEmployee.show', $kPIEmployee->employee->id)
                ->with('error', 'You are not authorized to input actual values for this KPI.');
        }
        
        $kPIEmployee->load('employee');
        
        return view('admin.kpi-employee.input-actual', compact('kPIEmployee'));
    }

    /**
     * Update actual value
     */
    public function updateActual(Request $request, KPIEmployee $kPIEmployee)
    {
        $request->validate([
            'actual' => 'required|numeric|min:0'
        ]);

        $kPIEmployee->update([
            'actual' => $request->actual
        ]);

        return redirect()->route('kPIEmployee.show', $kPIEmployee->employee->id)
            ->with('success', 'Actual KPI value updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KPIEmployee $kPIEmployee)
    {
        $employeeId = $kPIEmployee->employee->id;
        $kPIEmployee->delete();

        return response()->json([
            'success' => true,
            'message' => 'KPI Employee deleted successfully.'
        ]);
    }
}