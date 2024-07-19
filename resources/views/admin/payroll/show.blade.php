@extends('adminlte::page')

@section('title', trans('labels.employee'))

@section('content_header')
<div class="row">
    <div class="col-md-4 text-center">
        <img id="profile_picture" src="" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 150px; height: 150px;">
    </div>
    <div class="col-md-8">
        <h2 id="full_name"></h2>
        <p id="job_position" class="text-muted"></p>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Payroll Data Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Payroll Data</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Full Name</th>
                                <th>Gaji Pokok</th>
                                <th>Tunjangan Jabatan</th>
                                <th>Insentif Live</th>
                                <th>Insentif</th>
                                <th>Function</th>
                                <th>BPJS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td id="payroll_employee_id"></td>
                                <td id="payroll_full_name"></td>
                                <td id="payroll_gaji_pokok"></td>
                                <td id="payroll_tunjangan_jabatan"></td>
                                <td id="payroll_insentif_live"></td>
                                <td id="payroll_insentif"></td>
                                <td id="payroll_function"></td>
                                <td id="payroll_bpjs"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Attendance Records Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Attendance Records</h4>
                    <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#attendanceRecords" aria-expanded="false" aria-controls="attendanceRecords">
                        See More
                    </button>
                </div>
                <div class="card-body collapse" id="attendanceRecords">
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Shift ID</th>
                                <th>Attendance Status</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                            </tr>
                        </thead>
                        <tbody id="attendance_records">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Time Offs Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Time Offs</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time Off Type</th>
                                <th>Reason</th>
                                <th>Delegate To</th>
                            </tr>
                        </thead>
                        <tbody id="time_offs_records">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Salary Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Salary Details</h4>
                </div>
                <div class="card-body">
                    <h5>Base salary calculations</h5>
                    <div class="row">
                        <div class="col-md-4"><strong>Attendance count</strong></div>
                        <div class="col-md-4"><strong>Salary per day</strong></div>
                        <div class="col-md-4"><strong>Total</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><span id="attendance_days"></span> days</div>
                        <div class="col-md-4"><span id="salary_per_day"></span> IDR</div>
                        <div class="col-md-4"><span id="base_salary"></span> IDR</div>
                    </div>
                    <h5 class="mt-3">Salary Deductions:</h5>
                    <div class="row">
                        <div class="col-md-4"><strong>Time off count</strong></div>
                        <div class="col-md-4"><strong>Salary per day</strong></div>
                        <div class="col-md-4"><strong>Total</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><span id="time_off_days"></span> days</div>
                        <div class="col-md-4"><span id="time_off_salary_per_day"></span> IDR</div>
                        <div class="col-md-4"><span id="total_salary_deductions"></span> IDR</div>
                    </div>
                    <h5 class="mt-3">Net Salary</h5>
                    <div class="row">
                        <div class="col-md-12"><span id="net_salary"></span> IDR</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function(){
        var employeeId = "{{ $employee->id }}";
        
        // Fetch payroll data
        $.ajax({
            url: "{{ route('payroll.data', ['employee' => $employee->id]) }}",
            method: 'GET',
            success: function(data) {
                // Populate employee details
                $('#profile_picture').attr('src', data.employee.profile_picture ? '/storage/' + data.employee.profile_picture : '/img/user.png');
                $('#full_name').text(data.employee.full_name);
                $('#job_position').text(data.employee.job_position);

                // Populate payroll data
                $('#payroll_employee_id').text(data.payroll.employee_id);
                $('#payroll_full_name').text(data.payroll.full_name);
                $('#payroll_gaji_pokok').text(data.payroll.gaji_pokok.toFixed(2));
                $('#payroll_tunjangan_jabatan').text(data.payroll.tunjangan_jabatan.toFixed(2));
                $('#payroll_insentif_live').text(data.payroll.insentif_live.toFixed(2));
                $('#payroll_insentif').text(data.payroll.insentif.toFixed(2));
                $('#payroll_function').text(data.payroll.function.toFixed(2));
                $('#payroll_bpjs').text(data.payroll.BPJS.toFixed(2));

                // Populate time offs
                var timeOffsRecords = '';
                var salaryDeductionsList = '';
                var totalSalaryDeductions = 0;
                data.timeOffs.forEach(function(timeOff) {
                    timeOffsRecords += '<tr>';
                    timeOffsRecords += '<td>' + timeOff.date + '</td>';
                    timeOffsRecords += '<td>' + timeOff.time_off_type + '</td>';
                    timeOffsRecords += '<td>' + timeOff.reason + '</td>';
                    timeOffsRecords += '<td>' + timeOff.delegate_to + '</td>';
                    timeOffsRecords += '</tr>';
                    if(timeOff.time_off_type == 'Izin') {
                        var deduction = (data.payroll.gaji_pokok / 26 + 20000);
                        salaryDeductionsList += '<li>' + timeOff.time_off_type + ' on ' + timeOff.date + ': ' + deduction + ' IDR</li>';
                        totalSalaryDeductions += parseFloat(deduction);
                    }
                });
                $('#time_offs_records').html(timeOffsRecords);
                $('#salary_deductions').html(salaryDeductionsList);
                $('#total_salary_deductions').text(totalSalaryDeductions + ' IDR');

                // Populate salary details
                $('#attendance_days').text(data.attendanceDays);
                $('#salary_per_day').text(data.salaryPerDay);
                $('#base_salary').text(data.baseSalary);
                $('#net_salary').text(data.netSalary);
            }
        });

        // Fetch attendance data
        $.ajax({
            url: "{{ route('payroll.attendance', ['employee' => $employee->id]) }}",
            method: 'GET',
            success: function(data) {
                // Populate attendance records
                var attendanceRecords = '';
                data.forEach(function(attendance) {
                    attendanceRecords += '<tr>';
                    attendanceRecords += '<td>' + attendance.date + '</td>';
                    attendanceRecords += '<td>' + attendance.shift_id + '</td>';
                    attendanceRecords += '<td>' + attendance.attendance_status + '</td>';
                    attendanceRecords += '<td>' + attendance.clock_in + '</td>';
                    attendanceRecords += '<td>' + attendance.clock_out + '</td>';
                    attendanceRecords += '</tr>';
                });
                $('#attendance_records').html(attendanceRecords);
            }
        });

        $('[data-toggle="collapse"]').click(function() {
            $(this).text(function(i, text) {
                return text === "See More" ? "See Less" : "See More";
            })
        });
    });
</script>
@stop
