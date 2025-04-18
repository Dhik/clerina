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
            <h4>Time Off</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs justify-content-center">
                <li class="nav-item">
                    <a class="nav-link active" id="request-tab" href="#request">Request</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Request Content -->
    <div class="row mt-3 tab-content" id="request-content">
        <div class="col-12 text-center">
            <!-- <select class="form-control d-inline-block" style="width: auto;">
                <option>Jun 2024 - Jul 2024</option>
            </select> -->
            <!-- <button class="btn btn-outline-secondary ml-2">
                Filter
            </button> -->
        </div>
        <div class="col-12 text-center mt-3">
            <button class="btn btn-primary" id="request-timeoff-btn">Request Time Off</button>
        </div>
        <div class="col-12 mt-3" style="height: 400px; overflow-y: auto;">
            <ul class="list-group" id="timeOffList">
                <!-- DataTables will populate this -->
            </ul>
        </div>
        
    </div>
</div>

<!-- Request Time Off Modal -->
<div class="modal fade" id="requestTimeOffModal" tabindex="-1" aria-labelledby="requestTimeOffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestTimeOffModalLabel">Request Time Off</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="requestTimeOffForm" method="POST" action="{{ route('timeoffs.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="timeOffDate">Select date</label>
                        <input type="date" class="form-control" id="timeOffDate" name="date">
                    </div>
                    <div class="form-group">
                        <label for="timeOffType">Time Off Type</label>
                        <select class="form-control" id="timeOffType" name="time_off_type">
                            <option value="Sakit">Sakit</option>
                            <option value="Izin">Izin</option>
                            <option value="Izin 1 jam">Izin 1 jam</option>
                            <option value="Izin 2 jam">Izin 2 jam</option>
                            <option value="Izin 3 jam">Izin 3 jam</option>
                            <option value="Izin 4 jam">Izin 4 jam</option>
                            <option value="Izin 5 jam">Izin 5 jam</option>
                            <option value="Izin 6 jam">Izin 6 jam</option>
                            <option value="Izin 7 jam">Izin 7 jam</option>
                            <option value="Cuti">Cuti</option>
                            <option value="Work From Home">Work From Home</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="requestType">Request Type</label>
                        <input type="text" class="form-control" id="requestType" name="request_type">
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="delegateTo">Delegate To</label>
                        <select class="form-control" id="delegate_to" name="delegate_to">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->employee_id }}">{{ $employee->full_name }} ({{ $employee->job_position }} - {{ $employee->job_level }})</option>
                        @endforeach
                    </div>
                    <div class="form-group">
                        <label for="uploadFile">Upload file</label>
                        <input type="file" class="form-control-file" id="uploadFile" name="file">
                        <small class="form-text text-muted">Max file size is 10 MB.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitTimeOffRequest()">
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
    function submitTimeOffRequest() {
        const form = $('#requestTimeOffForm');
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
                $('#requestTimeOffModal').modal('hide');
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
        $('#request-tab').click(function() {
            $(this).addClass('active');
            $('#assigned-content').addClass('d-none');
            $('#request-content').removeClass('d-none');
        });

        $('#request-timeoff-btn').click(function() {
            $('#requestTimeOffModal').modal('show');
        });

        function loadTimeOffRequests() {
            $.ajax({
                url: '{{ route("timeoffs.get") }}',
                method: 'GET',
                success: function(response) {
                    const list = $('#timeOffList');
                    list.empty();

                    response.data.forEach(function(request) {
                        const statusClass = request.status_approval === 'approved' ? 'text-success' :
                                            request.status_approval === 'rejected' ? 'text-danger' : 'text-warning';

                        const fileLink = request.file ? `<a href="{{ asset('storage/${request.file}') }}" target="_blank">View file</a>` : 'No file uploaded';

                        const listItem = `
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <strong>${new Date(request.date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</strong><br>
                                        ${request.reason}
                                        <br><span class="${statusClass}">${request.status_approval.charAt(0).toUpperCase() + request.status_approval.slice(1)}</span>
                                    </span>
                                    <button class="arrow-btn" data-toggle="collapse" data-target="#detail-${request.id}" aria-expanded="false" aria-controls="detail-${request.id}">&gt;</button>
                                </div>
                                <div id="detail-${request.id}" class="collapse collapse-content">
                                    <p><strong>Type:</strong> ${request.time_off_type}</p>
                                    <p><strong>Delegate To:</strong> ${request.delegate_to}</p>
                                    <p><strong>File:</strong> ${fileLink}</p>
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

        loadTimeOffRequests();
    });
</script>
@stop
