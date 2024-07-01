<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Employee\Models\Attendance;
use App\Domain\Employee\Models\Employee;
use Carbon\Carbon;

class CreateAttendanceAbsentCommand extends Command
{
    protected $signature = 'attendance:create-absent';
    protected $description = 'Create attendance data for employees who did not clock in the previous day with status "absent"';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $yesterday = Carbon::yesterday()->toDateString();
        $employees = Employee::all();

        foreach ($employees as $employee) {
            $attendance = Attendance::where('employee_id', $employee->employee_id)
                ->whereDate('created_at', $yesterday)
                ->first();

            if (!$attendance) {
                Attendance::create([
                    'employee_id' => $employee->employee_id,
                    'attendance_status' => 'absent',
                    'created_at' => $yesterday,
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
