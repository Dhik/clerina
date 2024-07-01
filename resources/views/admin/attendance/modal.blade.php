<div class="modal fade" id="attendanceDetailModal" tabindex="-1" role="dialog" aria-labelledby="attendanceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceDetailModalLabel">History Log</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Shift Detail Section -->
                <div class="mb-3">
                    <h6>Shift Detail</h6>
                    <p><strong>Employee ID:</strong> <span id="modal_employee_id"></span></p>
                    <p><strong>Employee Name:</strong> <span id="modal_employee_name"></span></p>
                    <p><strong>Date:</strong> <span id="modal_date"></span></p>
                    <p><strong>Shift:</strong> <span id="modal_shiftTime"></span> (<span id="modal_schedule_in"></span> - <span id="modal_schedule_out"></span>)</p>
                </div>
                <hr>
                <!-- Log Detail Section -->
                <div class="d-flex">
                    <div class="col-md-2 text-center">
                        <img id="modal_employeeProfilePicture" src="" alt="Profile Picture" class="img-fluid rounded-circle">
                    </div>
                    <div class="col-md-10">
                        <h6>Log Detail</h6>
                        <p><strong><span id="modal_log_employee_name"></span></strong></p>
                        <h6><strong><span id="modal_log_employee_id"></span> - <span id="modal_job_position"></span></strong></h6>
                        <p><strong>Clock In:</strong> <span id="modal_clock_in"></span> - <strong>Clock Out:</strong> <span id="modal_clock_out"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
