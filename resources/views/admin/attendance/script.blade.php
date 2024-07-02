<script>
    $(document).ready(function () {
        const userTableSelector = $('#userTable');
        const baseUrl = "{{ asset('storage/') }}";
        const defaultImageUrl = "{{ asset('img/user.png') }}";

        // Initialize DataTable
        let table;
        if (!$.fn.dataTable.isDataTable(userTableSelector)) {
            table = userTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('attendance.get') }}",
                    data: function (d) {
                        d.date = $('#attendanceDate').val();
                    }
                },
                columns: [
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        render: function(data, type, row) {
                            const profilePictureUrl = row.profile_picture ? `${baseUrl}/${row.profile_picture}` : defaultImageUrl;
                            return `<img src="${profilePictureUrl}" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;">${data}`;
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
                    {data: 'time_off_code', name: 'time_off_code'},
                    {data: 'overtime', name: 'overtime'},
                    {
                        data: 'actions',
                        sortable: false,
                        orderable: false,
                        render: function(data, type, row) {
                            return `<button class="btn btn-primary btnEditAttendance" data-id="${data.id}">Edit</button>`;
                        }
                    }
                ]
            });
        } else {
            table = userTableSelector.DataTable();
        }

        // Filter by date
        $('#attendanceDate').change(function () {
            table.draw();
            updateOverviewCounts();
        });

        // Show modal for editing attendance
        userTableSelector.on('click', '.btnEditAttendance', function() {
            const attendanceId = $(this).data('id');
            $.ajax({
                url: `{{ url('admin/attendance') }}/${attendanceId}`,
                method: 'GET',
                success: function(data) {
                    $('#attendance_id').val(data.id);
                    $('#employee_id').val(data.employee_id);
                    $('#date').val(data.date);
                    $('#clock_in').val(data.clock_in);
                    $('#clock_out').val(data.clock_out);
                    $('#attendanceModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching attendance:', error);
                    alert('An error occurred while fetching attendance data');
                }
            });
        });

        // Update overview counts
        function updateOverviewCounts() {
            $.ajax({
                url: "{{ route('attendance.overview') }}",
                method: 'GET',
                data: {
                    date: $('#attendanceDate').val()
                },
                success: function(data) {
                    $('#onTimeCount').text(data.onTimeCount);
                    $('#lateClockInCount').text(data.lateClockInCount);
                    $('#earlyClockOutCount').text(data.earlyClockOutCount);
                    $('#absentCount').text(data.absentCount);
                    $('#noClockInCount').text(data.noClockInCount);
                    $('#noClockOutCount').text(data.noClockOutCount);
                    $('#invalidCount').text(data.invalidCount);
                    $('#dayOffCount').text(data.dayOffCount);
                    $('#timeOffCount').text(data.timeOffCount);
                }
            });
        }

        // Initial load
        updateOverviewCounts();

        // Handle form submission for updating attendance
        $('#attendanceForm').submit(function(e) {
            e.preventDefault();
            const attendanceId = $('#attendance_id').val();
            const formData = $(this).serialize();
            $.ajax({
                url: `{{ url('admin/attendance') }}/${attendanceId}`,
                method: 'PUT',
                data: formData,
                success: function(response) {
                    $('#attendanceModal').modal('hide');
                    table.draw();
                    updateOverviewCounts();
                    alert('Attendance updated successfully');
                },
                error: function(xhr, status, error) {
                    console.error('Error updating attendance:', error);
                    alert('An error occurred while updating attendance');
                }
            });
        });
    });
</script>
