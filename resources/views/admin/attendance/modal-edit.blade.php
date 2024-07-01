<div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceModalLabel">Edit attendance - <span id="employee_name_modal"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="attendanceForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="attendance_id" name="attendance_id">
                    <div class="form-group">
                        <label for="created_at">Date*</label>
                        <input type="date" class="form-control" id="created_at" name="created_at" required>
                    </div>
                    <div class="form-group">
                        <label for="shift">Shift*</label>
                        <select class="form-control" id="shift" name="shift_id">
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->shift_name }} ({{ $shift->schedule_in }} - {{ $shift->schedule_out }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="clock_in">Clock in</label>
                        <input type="time" class="form-control" id="clock_in" name="clock_in">
                    </div>
                    <div class="form-group">
                        <label for="clock_out">Clock out</label>
                        <input type="time" class="form-control" id="clock_out" name="clock_out">
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
