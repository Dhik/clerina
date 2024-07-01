<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Employee\Models\Attendance;
use Carbon\Carbon;

class UpdateAttendanceClockOutCommand extends Command
{
    protected $signature = 'attendance:update-clockout';
    protected $description = 'Update clock out time to "19:00" for employees who did not clock out the previous day';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $yesterday = Carbon::yesterday()->toDateString();
        $defaultClockOutTime = Carbon::parse('19:00')->toTimeString();

        $attendances = Attendance::whereNull('clock_out')
            ->whereDate('created_at', $yesterday)
            ->get();

        foreach ($attendances as $attendance) {
            $attendance->update([
                'clock_out' => $defaultClockOutTime,
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
