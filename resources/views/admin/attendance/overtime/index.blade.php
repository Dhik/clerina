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
            <h4>Overtime</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs justify-content-center">
                <li class="nav-item">
                    <a class="nav-link active" id="request-tab" href="#request">Request</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" id="assigned-tab" href="#assigned">Assigned</a>
                </li> -->
            </ul>
        </div>
    </div>

    <!-- Assigned Content -->
    <div class="row mt-3 tab-content d-none" id="assigned-content">
        <div class="col-12 text-center">
            <input type="month" class="form-control d-inline-block" style="width: auto;" value="2024-06">
            <button class="btn btn-outline-secondary">
                <span class="fas fa-search"></span>
            </button>
        </div>
        <div class="col-12 mt-3">
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <strong>21 May</strong><br>
                        Work shift
                    </span>
                    <span>-</span>
                    <button class="btn btn-link p-0">&gt;</button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Request Content -->
    <div class="row mt-3 tab-content" id="request-content">
        <div class="col-12 text-center">
            <!-- <select class="form-control d-inline-block" style="width: auto;">
                <option>Jun 2024 - Jul 2024</option>
            </select>
            <button class="btn btn-outline-secondary ml-2">
                Filter
            </button> -->
        </div>
        <div class="col-12 text-center mt-3">
            <button class="btn btn-primary" id="request-overtime-btn">Request Overtime</button>
        </div>
        <div class="col-12 mt-3">
            <ul class="list-group" id="overtimeList">
                <!-- DataTables will populate this -->
            </ul>
        </div>
        
    </div>
</div>

<!-- Request Overtime Modal -->
<div class="modal fade" id="requestOvertimeModal" tabindex="-1" aria-labelledby="requestOvertimeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestOvertimeModalLabel">Request Overtime</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="requestOvertimeForm" method="POST" action="{{ route('overtimes.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="overtimeDate">When</label>
                        <input type="date" class="form-control" id="overtimeDate" name="date">
                    </div>
                    <div class="form-group">
                        <label for="shift_id">Shift</label>
                        <select class="form-control" id="shift_id" name="shift_id">
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->shift_name }} ({{ $shift->schedule_in }} - {{ $shift->schedule_out }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="compensation">Compensation</label>
                        <select class="form-control" id="compensation" name="compensation">
                            <option>Paid overtime</option>
                        </select>
                    </div>
                    <h6>Before shift</h6>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="beforeShiftOvertimeDuration">Overtime duration</label>
                            <input type="time" class="form-control" id="beforeShiftOvertimeDuration" name="before_shift_overtime_duration">
                        </div>
                        <div class="form-group col-6">
                            <label for="beforeShiftBreakDuration">Break duration</label>
                            <input type="time" class="form-control" id="beforeShiftBreakDuration" name="before_shift_break_duration">
                        </div>
                    </div>
                    <h6>After shift</h6>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="afterShiftOvertimeDuration">Overtime duration</label>
                            <input type="time" class="form-control" id="afterShiftOvertimeDuration" name="after_shift_overtime_duration">
                        </div>
                        <div class="form-group col-6">
                            <label for="afterShiftBreakDuration">Break duration</label>
                            <input type="time" class="form-control" id="afterShiftBreakDuration" name="after_shift_break_duration">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="workNote">Work Note</label>
                        <textarea class="form-control" id="workNote" name="note" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="uploadFile">Upload file</label>
                        <input type="file" class="form-control-file" id="uploadFile" name="file">
                        <small class="form-text text-muted">Max file size is 10 MB.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitOvertimeRequest()">
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
    function submitOvertimeRequest() {
        const form = $('#requestOvertimeForm');
        const submitButton = $('.modal-footer .btn-primary');
        const spinner = submitButton.find('.spinner-border');

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
                $('#requestOvertimeModal').modal('hide');
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
        $('#assigned-tab').click(function() {
            $(this).addClass('active');
            $('#request-tab').removeClass('active');
            $('#assigned-content').removeClass('d-none');
            $('#request-content').addClass('d-none');
        });

        $('#request-tab').click(function() {
            $(this).addClass('active');
            $('#assigned-tab').removeClass('active');
            $('#request-content').removeClass('d-none');
            $('#assigned-content').addClass('d-none');
        });

        $('#request-overtime-btn').click(function() {
            $('#requestOvertimeModal').modal('show');
        });

        function loadOvertimeRequests() {
            $.ajax({
                url: '{{ route("overtimes.get") }}',
                method: 'GET',
                success: function(response) {
                    const list = $('#overtimeList');
                    list.empty();

                    response.data.forEach(function(overtime) {
                        const statusClass = overtime.status_approval === 'approved' ? 'text-success' :
                                            overtime.status_approval === 'rejected' ? 'text-danger' : 'text-warning';

                        const listItem = `
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <strong>${new Date(overtime.date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</strong><br>
                                        Clock in at ${new Date(overtime.date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })} 08:00
                                        <br><span class="${statusClass}">${overtime.status_approval.charAt(0).toUpperCase() + overtime.status_approval.slice(1)}</span>
                                    </span>
                                    <button class="arrow-btn" data-toggle="collapse" data-target="#detail-${overtime.id}" aria-expanded="false" aria-controls="detail-${overtime.id}">&gt;</button>
                                </div>
                                <div id="detail-${overtime.id}" class="collapse collapse-content">
                                    <p><strong>Shift:</strong> ${overtime.shift_id ? 'Shift ' + overtime.shift_id : 'N/A'}</p>
                                    <p><strong>Compensation:</strong> ${overtime.compensation}</p>
                                    <p><strong>Before Shift Overtime:</strong> ${overtime.before_shift_overtime_duration}</p>
                                    <p><strong>Before Shift Break:</strong> ${overtime.before_shift_break_duration}</p>
                                    <p><strong>After Shift Overtime:</strong> ${overtime.after_shift_overtime_duration}</p>
                                    <p><strong>After Shift Break:</strong> ${overtime.after_shift_break_duration}</p>
                                    <p><strong>Note:</strong> ${overtime.note}</p>
                                    <p><strong>File:</strong> ${overtime.file ? `<a href="{{ asset('storage/${overtime.file}') }}" target="_blank">View file</a>` : 'No file uploaded'}</p>
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

        loadOvertimeRequests();
    });
</script>
@stop
