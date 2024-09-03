<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Employee\Models\Employee;
use App\Domain\Employee\Models\Attendance;
use Carbon\Carbon;

class PopulateDailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:populate';
    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate daily attendance records for all active employees';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $employees = Employee::whereNull('resign_date')->get();
        $today = Carbon::today()->format('Y-m-d');

        foreach ($employees as $employee) {
            Attendance::firstOrCreate(
                [
                    'employee_id' => $employee->employee_id,
                    'date' => $today,
                ],
                [
                    'shift_id' => $employee->shift_id,
                    'attendance_status' => 'absent', // Default to absent until clock in
                    'clock_in' => null,
                    'clock_out' => null,
                ]
            );
        }

        $this->info('Daily attendance records populated successfully.');
    }
}
