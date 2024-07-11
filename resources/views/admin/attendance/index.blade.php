@extends('adminlte::page')

@section('title', trans('labels.attendance'))

@section('content_header')
    <h1>{{ trans('labels.attendance') }}</h1>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-info p-2">
                                <h5 class="text-center">Present</h5>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="inner">
                                            <h4 id="onTimeCount">0</h4>
                                            <p>On time</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="inner">
                                            <h4 id="lateClockInCount">0</h4>
                                            <p>Late clock in</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="inner">
                                            <h4 id="earlyClockOutCount">0</h4>
                                            <p>Early clock out</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-6">
                            <div class="small-box bg-purple p-2">
                                <h5 class="text-center">Not Present</h5>
                                <div class="row text-center">
                                    <div class="col-3">
                                        <div class="inner">
                                            <h4 id="absentCount">0</h4>
                                            <p>Absent</p>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="inner">
                                            <h4 id="noClockInCount">0</h4>
                                            <p>No clock in</p>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="inner">
                                            <h4 id="noClockOutCount">0</h4>
                                            <p>No clock out</p>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="inner">
                                            <h4 id="invalidCount">0</h4>
                                            <p>Invalid</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-success p-2">
                                <h5 class="text-center">Away</h5>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="inner">
                                            <h4 id="dayOffCount">0</h4>
                                            <p>Day off</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="inner">
                                            <h4 id="timeOffCount">0</h4>
                                            <p>Time off</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="col-auto">
                        <input type="date" class="form-control mb-2" id="attendanceDate">
                    </div>
                    <table id="attendanceTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="userTable-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.full_name') }}</th>
                                <th>{{ trans('labels.employee_id') }}</th>
                                <th>{{ trans('labels.date') }}</th>
                                <th>{{ trans('labels.shift') }}</th>
                                <th>{{ trans('labels.schedule_in') }}</th>
                                <th>{{ trans('labels.schedule_out') }}</th>
                                <th>{{ trans('labels.clock_in') }}</th>
                                <th>{{ trans('labels.clock_out') }}</th>
                                <th>{{ trans('labels.attendance_code') }}</th>
                                <!-- <th>{{ trans('labels.time_off_code') }}</th> -->
                                <th>{{ trans('labels.overtime') }}</th>
                                <th width="10%">{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.attendance.modal-edit')
    @include('admin.attendance.modal')
@stop

@section('js')
    <style>
        .smaller-text {
            font-size: 0.75rem; /* Adjust this value to make the text smaller */
        }
    </style>

<script>
$(function () {
    const userTableSelector = $('#attendanceTable');
    var baseUrl = "{{ asset('storage/') }}";
    var defaultImageUrl = "{{ asset('img/user.png') }}";
    var selectedEmployeeId = null;

    // datatable
    const table = userTableSelector.DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('attendance.get') }}",
            data: function (d) {
                d.date = $('#attendanceDate').val();
                d.employee_id = selectedEmployeeId;
            }
        },
        columns: [
            {
                data: 'employee_name',
                name: 'employee_name',
                render: function(data, type, row) {
                    var profilePictureUrl = row.profile_picture ? baseUrl + '/' + row.profile_picture : defaultImageUrl;
                    return '<img src="' + profilePictureUrl + '" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;" class="employee-filter" data-employee-id="' + row.employee_id + '">' + data;
                }
            },
            {data: 'employee_id', name: 'employee_id'},
            {data: 'date', name: 'date'},
            {data: 'shift', name: 'shift'},
            {data: 'schedule_in', name: 'schedule_in'},
            {data: 'schedule_out', name: 'schedule_out'},
            {data: 'clock_in', name: 'clock_in'},
            {data: 'clock_out', name: 'clock_out'},
            {data: 'attendance_code', name: 'attendance_code'},
            // {data: 'time_off_code', name: 'time_off_code'},
            {data: 'overtime', name: 'overtime'},
            {data: 'actions', sortable: false, orderable: false}
        ]
    });

    // Handle row click event to open modal and fill form
    table.on('draw.dt', function() {
        const tableBodySelector =  $('#attendanceTable tbody');

        tableBodySelector.on('click', '#attendanceShow', function(event) {
            event.preventDefault();
            let rowData = table.row($(this).closest('tr')).data();
            showAttendance(rowData);
        });

        tableBodySelector.on('click', '#attendanceEdit', function(event) {
            event.preventDefault();
            let rowData = table.row($(this).closest('tr')).data();
            editAttendance(rowData);
        });

        tableBodySelector.on('click', '.deleteButton', function() {
            let rowData = table.row($(this).closest('tr')).data();
            let route = '{{ route('attendance.destroy', ':id') }}';
            deleteAjax(route, rowData.id, table);
        });

        tableBodySelector.on('click', '.employee-filter', function() {
            selectedEmployeeId = $(this).data('employee-id');
            table.draw();
            updateOverviewCounts();
        });
    });

    function showAttendance(rowData) {
        console.log(rowData); // Debugging log

        $('#modal_employeeProfilePicture').attr('src', rowData.profile_picture ? baseUrl + '/' + rowData.profile_picture : defaultImageUrl);
        $('#modal_employee_id').text(rowData.employee_id);
        $('#modal_log_employee_id').text(rowData.employee_id);
        $('#modal_employee_name').text(rowData.employee_name);
        $('#modal_log_employee_name').text(rowData.employee_name);
        $('#modal_job_position').text(rowData.job_position);
        $('#modal_date').text(rowData.date);
        $('#modal_shiftTime').text(rowData.shift);
        $('#modal_clock_in').text(rowData.clock_in);
        $('#modal_clock_out').text(rowData.clock_out);
        $('#modal_schedule_in').text(rowData.schedule_in);
        $('#modal_schedule_out').text(rowData.schedule_out);
        $('#attendanceDetailModal').modal('show');
    }

    function editAttendance(rowData) {
        $('#attendance_id').val(rowData.id);
        $('#employee_name_modal').text(rowData.employee_name);
        $('#employee_id').val(rowData.employee_id);
        $('#created_at').val(rowData.date);
        $('#clock_in').val(rowData.clock_in);
        $('#clock_out').val(rowData.clock_out);

        // Set the form action URL dynamically
        $('#attendanceForm').attr('action', "{{ url('admin/attendance') }}/" + rowData.id);

        // Set the shift dropdown
        $('#shift').val(rowData.shift_id);

        $('#attendanceModal').modal('show');
    }

    // Apply the search
    $('.filter-input').keyup(function () {
        table.column($(this).data('column'))
             .search($(this).val())
             .draw();
        updateOverviewCounts($('#attendanceDate').val());
    });

    $('#attendanceDate').change(function () {
        table.draw();
        const selectedDate = $(this).val();
        updateOverviewCounts(selectedDate);
    });

    function updateOverviewCounts(date = null) {
    $.ajax({
        url: "{{ route('attendance.overview') }}",
        method: 'GET',
        data: { date: date, employee_id: selectedEmployeeId },
        success: function(data) {
            console.log(data);
            $('#onTimeCount').text(data.onTimeCount);
            $('#lateClockInCount').text(data.lateClockInCount);
            $('#earlyClockOutCount').text(data.earlyClockOutCount);
            $('#absentCount').text(data.absentCount);
            $('#noClockInCount').text(data.noClockInCount);
            $('#noClockOutCount').text(data.noClockOutCount);
            $('#invalidCount').text(data.invalidCount);
            $('#dayOffCount').text(data.dayOffCount);
            $('#timeOffCount').text(data.timeOffCount);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error fetching overview data:', textStatus, errorThrown);
        }
    });
}

    // Initial load
    updateOverviewCounts();
});
</script>

@stop
