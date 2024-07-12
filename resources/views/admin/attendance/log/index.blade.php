@extends('attendance.base')

@section('title', 'Attendance App')

@section('navbar')
<nav class="navbar navbar-custom">
    <div class="container d-flex align-items-center justify-content-between">
        <!-- Navbar content here -->
    </div>
</nav>
@stop

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12 text-center mb-3">
            <h4>Attendance Log</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs justify-content-center">
                <li class="nav-item">
                    <a class="nav-link active" id="logs-tab" href="#logs">Logs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="attendance-tab" href="#attendance">Attendance</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" id="shift-tab" href="#shift">Shift</a>
                </li> -->
            </ul>
        </div>
    </div>

    <!-- Logs Content -->
    <div class="row mt-3 tab-content" id="logs-content">
        <div class="col-12 text-center">
            <input type="month" class="form-control d-inline-block" id="logMonth" style="width: auto;" value="{{ \Carbon\Carbon::now()->format('Y-m') }}">
            <button class="btn btn-outline-secondary" id="logMonthBtn">
                <span class="fas fa-search"></span>
            </button>
        </div>
        <div class="col-12 text-center mt-3">
            <div class="card p-3">
                <div class="row">
                    <div class="col-4">
                        <div>Absent</div>
                        <div id="absentCount">0</div>
                    </div>
                    <div class="col-4">
                        <div>Late Clock in</div>
                        <div id="lateClockInCount">0</div>
                    </div>
                    <div class="col-4">
                        <div>Early Clock out</div>
                        <div id="earlyClockOutCount">0</div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-6">
                        <div>No Clock in</div>
                        <div id="noClockInCount">0</div>
                    </div>
                    <div class="col-6">
                        <div>No Clock out</div>
                        <div id="noClockOutCount">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mt-3" style="height: 400px; overflow-y: auto;">
            <ul class="list-group" id="attendanceList">
                <!-- Attendance history will be populated here -->
            </ul>
        </div>
    </div>

    <!-- Attendance Content -->
    <div class="row mt-3 tab-content d-none" id="attendance-content">
        <div class="col-12 text-center mt-3">
            <button class="btn btn-primary" id="request-attendance-btn">Request Attendance</button>
        </div>
        <div class="col-12 mt-3" style="height: 400px; overflow-y: auto;">
            <ul class="list-group" id="attendanceRequestList">
                <!-- Attendance history will be populated here -->
            </ul>
        </div>
    </div>
</div>

<!-- Request Attendance Modal -->
<div class="modal fade" id="requestAttendanceModal" tabindex="-1" aria-labelledby="requestAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestAttendanceModalLabel">Request Attendance</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="requestAttendanceForm" method="POST" action="{{ route('attendance.requests') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="attendanceDate">Select date</label>
                        <input type="date" class="form-control" id="attendanceDate" name="date" required>
                        <div class="invalid-feedback">Date is required.</div>
                    </div>
                    <div class="form-group">
                        <label for="shift_id">Shift</label>
                        <select class="form-control" id="shift_id" name="shift_id">
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->shift_name }} ({{ $shift->schedule_in }} - {{ $shift->schedule_out }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="clockIn">Clock In</label>
                            <input type="time" class="form-control" id="clockIn" name="clock_in" required>
                            <div class="invalid-feedback">Clock In time is required.</div>
                        </div>
                        <div class="form-group col-6">
                            <label for="clockOut">Clock Out</label>
                            <input type="time" class="form-control" id="clockOut" name="clock_out" required>
                            <div class="invalid-feedback">Clock Out time is required.</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="workNote">Work Note</label>
                        <textarea class="form-control" id="workNote" name="work_note" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="uploadFile">Upload file</label>
                        <input type="file" class="form-control-file" id="uploadFile" name="file">
                        <small class="form-text text-muted">Max file size is 10 MB.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitAttendanceRequest()">
                    Submit request
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('footer')
    @include('attendance.footer')
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    function submitAttendanceRequest() {
        const form = $('#requestAttendanceForm'); // Ensure form is a jQuery object
        const submitButton = $('.modal-footer .btn-primary');
        const spinner = submitButton.find('.spinner-border');

        if (form[0].checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
            form.addClass('was-validated');
            return;
        }

        submitButton.prop('disabled', true);
        spinner.removeClass('d-none');

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: new FormData(form[0]),
            processData: false,
            contentType: false,
            success: function(response) {
                // Handle success response
                $('#requestAttendanceModal').modal('hide');
                location.reload(); // Refresh the page to see the new entry
            },
            error: function(response) {
                // Handle error response
                console.error(response);
            },
            complete: function() {
                submitButton.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    }

    $(document).ready(function() {
        $('#logs-tab').click(function() {
            $(this).addClass('active');
            $('#attendance-tab').removeClass('active');
            $('#logs-content').removeClass('d-none');
            $('#attendance-content').addClass('d-none');
            loadOverviewData(); // Load overview data when Logs tab is clicked
        });

        $('#attendance-tab').click(function() {
            $(this).addClass('active');
            $('#logs-tab').removeClass('active');
            $('#logs-content').addClass('d-none');
            $('#attendance-content').removeClass('d-none');
            // Load attendance history when Attendance tab is clicked
            loadAttendanceRequests();
        });

        $('#request-attendance-btn').click(function() {
            $('#requestAttendanceModal').modal('show');
        });

        $('#logMonthBtn').click(function() {
            loadOverviewData();
            loadAttendanceHistory();
        });

        function loadOverviewData() {
            const selectedMonth = $('#logMonth').val();
            $.ajax({
                url: '{{ route("attendance.overviewbyid") }}',
                method: 'GET',
                data: { date: selectedMonth },
                success: function(response) {
                    $('#absentCount').text(response.absentCount);
                    $('#lateClockInCount').text(response.lateClockInCount);
                    $('#earlyClockOutCount').text(response.earlyClockOutCount);
                    $('#noClockInCount').text(response.noClockInCount);
                    $('#noClockOutCount').text(response.noClockOutCount);
                },
                error: function(response) {
                    console.error(response);
                }
            });
        }

        function loadAttendanceRequests() {
            $.ajax({
                url: '{{ route("attendance.get_requests") }}',
                method: 'GET',
                success: function(response) {
                    const list = $('#attendanceRequestList');
                    list.empty();

                    response.data.forEach(function(request) {
                        const statusClass = request.status_approval === 'approved' ? 'text-success' :
                                            request.status_approval === 'rejected' ? 'text-danger' : 'text-warning';

                        const listItem = `
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <strong>${new Date(request.date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</strong><br>
                                        Clock in at ${new Date(request.clock_in).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}
                                        <br><span class="${statusClass}">${request.status_approval.charAt(0).toUpperCase() + request.status_approval.slice(1)}</span>
                                    </span>
                                    <button class="arrow-btn" data-toggle="collapse" data-target="#detail-${request.id}" aria-expanded="false" aria-controls="detail-${request.id}">&gt;</button>
                                </div>
                                <div id="detail-${request.id}" class="collapse collapse-content">
                                    <p><strong>Shift:</strong> ${request.shift_id ? 'Shift ' + request.shift_id : 'N/A'}</p>
                                    <p><strong>Clock Out:</strong> ${new Date(request.clock_out).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}</p>
                                    <p><strong>Work Note:</strong> ${request.work_note}</p>
                                    <p><strong>File:</strong> ${request.file ? `<a href="{{ asset('storage/${request.file}') }}" target="_blank">View file</a>` : 'No file uploaded'}</p>
                                </div>
                            </li>
                        `;
                        list.append(listItem);
                    });

                    // Toggle icon direction on collapse show/hide
                    $('.arrow-btn').on('click', function() {
                        const button = $(this);
                        const target = $(button.data('target'));

                        target.on('show.bs.collapse', function () {
                            button.html('&lt;');
                        });

                        target.on('hide.bs.collapse', function () {
                            button.html('&gt;');
                        });

                        target.collapse('toggle');
                    });
                },
                error: function(response) {
                    console.error(response);
                }
            });
        }

        function loadAttendanceHistory() {
            const selectedMonth = $('#logMonth').val();
            $.ajax({
                url: '{{ route("attendance.history") }}',
                method: 'GET',
                data: { date: selectedMonth },
                success: function(response) {
                    const list = $('#attendanceList');
                    list.empty();

                    Object.values(response).forEach(function(attendance) {
                        const clockInTime = attendance.clock_in ? new Date(attendance.clock_in).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) : '-';
                        const clockOutTime = attendance.clock_out ? new Date(attendance.clock_out).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) : '-';
                        
                        const listItem = `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${new Date(attendance.date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })}</strong>
                                    <br>
                                    <small class="text-muted">Work shift</small>
                                </div>
                                <div>
                                    <span>${clockInTime}&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp; ${clockOutTime}</span>
                                </div>
                                <button class="btn btn-link">
                                    &gt;
                                </button>
                            </li>
                        `;
                        list.append(listItem);
                    });
                },
                error: function(response) {
                    console.error(response);
                }
            });
        }

        loadOverviewData(); // Load initial data
        loadAttendanceHistory(); // Load attendance history when the page is accessed
    });
</script>
@stop
