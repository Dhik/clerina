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
            <h4>Request Change Shift</h4>
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
        <div class="col-12 text-center mt-3">
            <button class="btn btn-primary" id="request-change-shift-btn">Request Change Shift</button>
        </div>
        <div class="col-12 mt-3" style="height: 400px; overflow-y: auto;">
            <ul class="list-group" id="changeShiftList">
                <!-- DataTables will populate this -->
            </ul>
        </div>
    </div>
</div>

<!-- Request Change Shift Modal -->
<div class="modal fade" id="requestChangeShiftModal" tabindex="-1" aria-labelledby="requestChangeShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestChangeShiftModalLabel">Request Change Shift</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="requestChangeShiftForm" method="POST" action="{{ route('requestChangeShifts.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="changeShiftDate">Select date</label>
                        <input type="date" class="form-control" id="changeShiftDate" name="date">
                    </div>
                    <div class="form-group">
                        <label for="startsShiftId">Starts</label>
                        <input type="time" class="form-control" id="startsShiftId" name="starts_shift">
                    </div>
                    <div class="form-group">
                        <label for="changeShiftId">End</label>
                        <input type="time" class="form-control" id="changeShiftId" name="end_shift">
                    </div>
                    <div class="form-group">
                        <label for="note">Note</label>
                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="uploadFile">Upload file</label>
                        <input type="file" class="form-control-file" id="uploadFile" name="file">
                        <small class="form-text text-muted">Max file size is 10 MB.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitChangeShiftRequest()">
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
    function submitChangeShiftRequest() {
        const form = $('#requestChangeShiftForm');
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
                $('#requestChangeShiftModal').modal('hide');
                location.reload();
            },
            error: function(response) {
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

        $('#request-change-shift-btn').click(function() {
            $('#requestChangeShiftModal').modal('show');
        });

        function loadChangeShiftRequests() {
            $.ajax({
                url: '{{ route("requestChangeShifts.get") }}',
                method: 'GET',
                success: function(response) {
                    const list = $('#changeShiftList');
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
                                        ${request.note}
                                        <br><span class="${statusClass}">${request.status_approval.charAt(0).toUpperCase() + request.status_approval.slice(1)}</span>
                                    </span>
                                    <button class="arrow-btn" data-toggle="collapse" data-target="#detail-${request.id}" aria-expanded="false" aria-controls="detail-${request.id}">&gt;</button>
                                </div>
                                <div id="detail-${request.id}" class="collapse collapse-content">
                                    <p><strong>Starts Shift:</strong> ${request.starts_shift}</p>
                                    <p><strong>Change Shift:</strong> ${request.end_shift}</p>
                                    <p><strong>File:</strong> ${fileLink}</p>
                                </div>
                            </li>
                        `;
                        list.append(listItem);
                    });

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

        loadChangeShiftRequests();
    });
</script>
@stop
