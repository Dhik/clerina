@extends('adminlte::page')

@section('title', trans('labels.employee'))

@section('content_header')
<div class="row">
    <div class="col-md-4 text-center">
        <img src="{{ $employee->profile_picture ? asset('storage/' . $employee->profile_picture) : asset('img/user.png') }}" 
                alt="Profile Picture" 
                class="img-fluid rounded-circle" 
                style="width: 150px; height: 150px;">
    </div>
    <div class="col-md-8">
        <h2>{{ $employee->full_name }}</h2>
        <p class="text-muted">{{ $employee->job_position }}</p>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h4>Attendance Records</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Shift ID</th>
                        <th>Attendance Status</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->date }}</td>
                        <td>{{ $attendance->shift_id }}</td>
                        <td>{{ $attendance->attendance_status }}</td>
                        <td>{{ $attendance->clock_in }}</td>
                        <td>{{ $attendance->clock_out }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h4>Overtimes</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Shift ID</th>
                        <th>Compensation</th>
                        <th>Before Shift Overtime Duration</th>
                        <th>After Shift Overtime Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overtimes as $overtime)
                    <tr>
                        <td>{{ $overtime->date }}</td>
                        <td>{{ $overtime->shift_id }}</td>
                        <td>{{ $overtime->compensation }}</td>
                        <td>{{ $overtime->before_shift_overtime_duration }}</td>
                        <td>{{ $overtime->after_shift_overtime_duration }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h4>Time Offs</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time Off Type</th>
                        <th>Reason</th>
                        <th>Delegate To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeOffs as $timeOff)
                    <tr>
                        <td>{{ $timeOff->date }}</td>
                        <td>{{ $timeOff->time_off_type }}</td>
                        <td>{{ $timeOff->reason }}</td>
                        <td>{{ $timeOff->delegate_to }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h4>Attendance Requests</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Shift ID</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Work Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendanceRequests as $attendanceRequest)
                    <tr>
                        <td>{{ $attendanceRequest->date }}</td>
                        <td>{{ $attendanceRequest->shift_id }}</td>
                        <td>{{ $attendanceRequest->clock_in }}</td>
                        <td>{{ $attendanceRequest->clock_out }}</td>
                        <td>{{ $attendanceRequest->work_note }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h4>Request Change Shifts</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Start Shift</th>
                        <th>End Shift</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requestChangeShifts as $requestChangeShift)
                    <tr>
                        <td>{{ $requestChangeShift->date }}</td>
                        <td>{{ $requestChangeShift->starts_shift }}</td>
                        <td>{{ $requestChangeShift->end_shift }}</td>
                        <td>{{ $requestChangeShift->note }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h4>Total Work Hours</h4>
            <p>{{ $totalWorkHours }} hours</p>

            <h4>Salary Details</h4>
            <p>Full Salary: {{ number_format($takeHomePay, 2) }} IDR</p>
            <p>Base Salary Calculation: {{ $attendanceDays }} days * {{ number_format($salaryPerDay, 2) }} IDR = {{ number_format($baseSalary, 2) }} IDR</p>
            <p>Salary Deductions:</p>
            <ul>
                @foreach($timeOffs as $timeOff)
                    @if(in_array($timeOff->time_off_type, ['work_from_home', 'izin 5 jam', 'izin 6 jam', 'izin 7 jam']))
                        <li>{{ $timeOff->time_off_type }} on {{ $timeOff->date }}: 20,000 IDR</li>
                    @endif
                @endforeach
            </ul>
            <p>Total Salary Deductions: {{ number_format($salaryDeductions, 2) }} IDR</p>
            <p>Net Salary: {{ number_format($netSalary, 2) }} IDR</p>
        </div>
    </div>
</div>
@endsection

@section('js')
@stop
