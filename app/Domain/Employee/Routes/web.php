<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Employee\Controllers\EmployeeController;
use App\Domain\Employee\Controllers\AttendanceController;
use App\Domain\Employee\Controllers\ShiftController;
use App\Domain\Employee\Controllers\LocationController;
use App\Domain\Employee\Controllers\TimeOffController;
use App\Domain\Employee\Controllers\OvertimeController;
use App\Domain\Employee\Controllers\AttendanceRequestController;
use App\Domain\Employee\Controllers\RequestChangeShiftController;
use App\Domain\Employee\Controllers\PayrollController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('admin')
    ->middleware('auth')
    ->group(function () {
        Route::prefix('attendance')
        ->group(function () {
            Route::get('/', [AttendanceController::class, 'attendance_log'])->name('attendance_log.index');
            Route::get('/get', [AttendanceController::class, 'get'])->name('attendance.get');
            Route::get('/get_overview', [AttendanceController::class, 'getOverview'])->name('attendance.overview');
            Route::get('/get_ovw', [AttendanceController::class, 'getOvw'])->name('attendance.ovw');
            Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
            Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
            Route::post('/store', [AttendanceController::class, 'store'])->name('attendance.store'); 
            Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
            Route::get('/approval', [AttendanceRequestController::class, 'show_all'])->name('attendance_requests.approval');
            Route::get('/pending', [AttendanceRequestController::class, 'getPendingRequests'])->name('attendance_requests.pending');
            Route::get('/approved', [AttendanceRequestController::class, 'getApprovedRequests'])->name('attendance_requests.approved');
            Route::get('/rejected', [AttendanceRequestController::class, 'getRejectedRequests'])->name('attendance_requests.rejected');
            Route::post('/update-status/{id}', [AttendanceRequestController::class, 'updateRequestStatus'])->name('attendance_requests.update-status');
            Route::delete('/req/{attendanceRequest}', [AttendanceRequestController::class, 'destroy'])->name('attendance_requests.destroy');


            Route::post('/requests', [AttendanceController::class, 'store_request'])->name('attendance.requests');
            

            Route::prefix('app')
            ->group(function () {
                Route::get('/', [AttendanceController::class, 'show'])->name('attendance.app');
                Route::post('/clockin', [AttendanceController::class, 'clockIn'])->name('attendance.clockin');
                Route::post('/clockout', [AttendanceController::class, 'clockOut'])->name('attendance.clockout');
            });  
        });
        Route::prefix('absensi')
            ->group(function () {
                Route::get('/', [AttendanceController::class, 'absensi'])->name('attendance.absensi');
                Route::get('/log', [AttendanceController::class, 'log'])->name('attendance.log');
                Route::get('/overview', [AttendanceController::class, 'getOverviewById'])->name('attendance.overviewbyid');
                Route::get('/history', [AttendanceController::class, 'getAttendanceHistory'])->name('attendance.history');
                Route::post('/requests', [AttendanceController::class, 'store_request'])->name('attendance.requests');
                Route::get('/get', [AttendanceController::class, 'get_request'])->name('attendance.get_requests');
                Route::get('/overtime', [AttendanceController::class, 'overtime'])->name('attendance.overtime');
                Route::get('/timeoff', [AttendanceController::class, 'timeoff'])->name('attendance.timeoff');
            });
        Route::prefix('performances')
            ->group(function () {
                Route::get('/', [EmployeeController::class, 'performances'])->name('employee.performances');
                Route::get('/weekly_work_hours', [EmployeeController::class, 'getWeeklyWorkHours'])->name('performances.weekly_work_hours');
            });
        Route::prefix('location')
            ->group(function() {
                Route::get('/', [LocationController::class, 'index'])->name('locations.index');
                Route::get('/create', [LocationController::class, 'create'])->name('locations.create');
                Route::post('/', [LocationController::class, 'store'])->name('locations.store');
                Route::get('/{location}', [LocationController::class, 'show'])->name('locations.show');
                Route::get('/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
                Route::put('/{location}', [LocationController::class, 'update'])->name('locations.update');
                Route::delete('/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
            });
        Route::prefix('timeOff')
            ->group(function() {
                Route::get('/', [TimeOffController::class, 'index'])->name('timeoffs.index');
                Route::get('/create', [TimeOffController::class, 'create'])->name('timeoffs.create');
                Route::get('/get', [TimeOffController::class, 'get'])->name('timeoffs.get');
                Route::get('/approval', [TimeOffController::class, 'show_all'])->name('timeoffs.approval');
                Route::get('/pending', [TimeOffController::class, 'getPendingTimeOffs'])->name('timeoffs.pending');
                Route::get('/approved', [TimeOffController::class, 'getApprovedTimeOffs'])->name('timeoffs.approved');
                Route::get('/rejected', [TimeOffController::class, 'getRejectedTimeOffs'])->name('timeoffs.rejected');
                Route::post('/update-status/{id}', [TimeOffController::class, 'updateTimeOffStatus'])->name('timeoffs.update-status');
                Route::post('/', [TimeOffController::class, 'store'])->name('timeoffs.store');
                Route::get('/{timeOff}', [TimeOffController::class, 'show'])->name('timeoffs.show');
                Route::get('/{timeOff}/edit', [TimeOffController::class, 'edit'])->name('timeoffs.edit');
                Route::put('/{timeOff}', [TimeOffController::class, 'update'])->name('timeoffs.update');
                Route::delete('/{timeOff}', [TimeOffController::class, 'destroy'])->name('timeoffs.destroy');
            });
        Route::prefix('overtime')
            ->group(function() {
                Route::get('/', [OvertimeController::class, 'index'])->name('overtimes.index');
                Route::get('/create', [OvertimeController::class, 'create'])->name('overtimes.create');
                Route::get('/get', [OvertimeController::class, 'get'])->name('overtimes.get');
                Route::get('/approval', [OvertimeController::class, 'show_all'])->name('overtime.approval');
                Route::get('/pending', [OvertimeController::class, 'getPendingOvertime'])->name('overtime.pending');
                Route::get('/approved', [OvertimeController::class, 'getApprovedOvertime'])->name('overtime.approved');
                Route::get('/rejected', [OvertimeController::class, 'getRejectedOvertime'])->name('overtime.rejected');
                Route::post('/update-status/{id}', [OvertimeController::class, 'updateOvertimeStatus'])->name('overtime.update-status');
                Route::post('/', [OvertimeController::class, 'store'])->name('overtimes.store');
                Route::get('/{overtime}', [OvertimeController::class, 'show'])->name('overtimes.show');
                Route::get('/{overtime}/edit', [OvertimeController::class, 'edit'])->name('overtimes.edit');
                Route::put('/{overtime}', [OvertimeController::class, 'update'])->name('overtimes.update');
                Route::delete('/{overtime}', [OvertimeController::class, 'destroy'])->name('overtimes.destroy');
            });

            Route::prefix('requestChangeShifts')
            ->group(function() {
                Route::get('/', [RequestChangeShiftController::class, 'index'])->name('requestChangeShifts.index');
                Route::get('/create', [RequestChangeShiftController::class, 'create'])->name('requestChangeShifts.create');
                Route::get('/get', [RequestChangeShiftController::class, 'get'])->name('requestChangeShifts.get');
                Route::get('/approval', [RequestChangeShiftController::class, 'show_all'])->name('requestChangeShifts.approval');
                Route::get('/pending', [RequestChangeShiftController::class, 'getPendingRequestChangeShifts'])->name('requestChangeShifts.pending');
                Route::get('/approved', [RequestChangeShiftController::class, 'getApprovedRequestChangeShifts'])->name('requestChangeShifts.approved');
                Route::get('/rejected', [RequestChangeShiftController::class, 'getRejectedRequestChangeShifts'])->name('requestChangeShifts.rejected');
                Route::post('/update-status/{id}', [RequestChangeShiftController::class, 'updateRequestChangeShiftStatus'])->name('requestChangeShifts.update-status');
                Route::post('/', [RequestChangeShiftController::class, 'store'])->name('requestChangeShifts.store');
                Route::get('/{requestChangeShift}', [RequestChangeShiftController::class, 'show'])->name('requestChangeShifts.show');
                Route::get('/{requestChangeShift}/edit', [RequestChangeShiftController::class, 'edit'])->name('requestChangeShifts.edit');
                Route::put('/{requestChangeShift}', [RequestChangeShiftController::class, 'update'])->name('requestChangeShifts.update');
                Route::delete('/{requestChangeShift}', [RequestChangeShiftController::class, 'destroy'])->name('requestChangeShifts.destroy');
            });

        Route::prefix('employees')
        ->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('employee.index');
            Route::get('/get', [EmployeeController::class, 'get'])->name('employee.get');
            Route::get('/export', [EmployeeController::class, 'export'])->name('employee.export');
            Route::get('/get_overview', [EmployeeController::class, 'getOverview'])->name('employee.overview');
            Route::get('/new-hires', [EmployeeController::class, 'getNewHires'])->name('employees.newHires');
            Route::get('/leavings', [EmployeeController::class, 'getLeavings'])->name('employees.leavings');
            Route::get('/active-employees', [EmployeeController::class, 'getActiveEmployees'])->name('employees.activeEmployees');
            Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create');
            Route::post('/', [EmployeeController::class, 'store'])->name('employee.store');
            Route::get('/{employee}', [EmployeeController::class, 'show'])->name('employee.show');
            Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('employee.edit');
            Route::put('/{employee}', [EmployeeController::class, 'update'])->name('employee.update');
            Route::delete('{employee}', [EmployeeController::class, 'destroy'])->name('employee.destroy');
        });
        Route::prefix('location')
            ->group(function () {
                Route::get('/', [LocationController::class, 'index'])->name('location.index');
                Route::get('/create', [LocationController::class, 'create'])->name('location.create');
                Route::post('/store', [LocationController::class, 'store'])->name('location.store');
                Route::get('/edit/{id}', [LocationController::class, 'edit'])->name('location.edit');
                Route::put('/update/{id}', [LocationController::class, 'update'])->name('location.update');
                Route::delete('/destroy/{id}', [LocationController::class, 'destroy'])->name('location.destroy');
                Route::get('/data', [LocationController::class, 'getData'])->name('location.data');
            });
            Route::prefix('shift')
            ->group(function () {
                Route::get('/', [ShiftController::class, 'index'])->name('shift.index');
                Route::get('/create', [ShiftController::class, 'create'])->name('shift.create');
                Route::post('/store', [ShiftController::class, 'store'])->name('shift.store');
                Route::get('/edit/{id}', [ShiftController::class, 'edit'])->name('shift.edit');
                Route::put('/update/{id}', [ShiftController::class, 'update'])->name('shift.update');
                Route::delete('/destroy/{id}', [ShiftController::class, 'destroy'])->name('shift.destroy');
                Route::get('/data', [ShiftController::class, 'getData'])->name('shift.data');
            });

        Route::prefix('payroll')
            ->group(function () {
                Route::get('/', [PayrollController::class, 'index'])->name('payroll.index');
                Route::get('/get', [PayrollController::class, 'get'])->name('payroll.get');
                Route::get('/import', [PayrollController::class, 'importPage'])->name('payroll.import');
                Route::post('/import-payrolls', [PayrollController::class, 'import'])->name('payrolls.import');
                Route::get('/payrolls-data', [PayrollController::class, 'getPayrollsData'])->name('payrolls.data');
                Route::get('/{employee}', [PayrollController::class, 'show'])->name('payroll.show');
                Route::get('/data/{employee}', [PayrollController::class, 'getPayrollData'])->name('payroll.data');
                Route::get('/attendance/{employee}', [PayrollController::class, 'getAttendanceData'])->name('payroll.attendance');
                Route::get('/edit/{id}', [PayrollController::class, 'edit'])->name('payroll.edit');
                Route::put('/update/{id}', [PayrollController::class, 'update'])->name('payroll.update');
                Route::delete('/destroy/{id}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
            });
        
    });
    


