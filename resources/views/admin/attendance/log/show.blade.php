@extends('adminlte::page')

@section('title', trans('labels.attendance'))

@section('content_header')
    <h1>{{ trans('labels.attendance') }} {{ trans('labels.request') }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="pill" href="#pendingTab" role="tab">
                            {{ trans('labels.pending') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#approvedTab" role="tab">
                            {{ trans('labels.approved') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#rejectedTab" role="tab">
                            {{ trans('labels.rejected') }}
                        </a>
                    </li>
                </ul>
                <div class="mt-3"></div>
                <div class="tab-content">
                    <div class="tab-pane active" id="pendingTab">
                        @include('admin.attendance.log.partials.table', ['tableId' => 'pendingRequests'])
                    </div>
                    <div class="tab-pane" id="approvedTab">
                        @include('admin.attendance.log.partials.table', ['tableId' => 'approvedRequests'])
                    </div>
                    <div class="tab-pane" id="rejectedTab">
                        @include('admin.attendance.log.partials.table', ['tableId' => 'rejectedRequests'])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Show Detail Modal -->
<div class="modal fade" id="attendanceDetailModal" tabindex="-1" role="dialog" aria-labelledby="attendanceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceDetailModalLabel">{{ trans('labels.details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p><strong>{{ trans('labels.employee_id') }}:</strong> <span id="modal_employee_id"></span></p>
                    <p><strong>{{ trans('labels.name') }}:</strong> <span id="modal_employee_name"></span></p>
                    <p><strong>{{ trans('labels.date') }}:</strong> <span id="modal_date"></span></p>
                    <p><strong>{{ trans('labels.clock_in') }}:</strong> <span id="modal_clock_in"></span></p>
                    <p><strong>{{ trans('labels.clock_out') }}:</strong> <span id="modal_clock_out"></span></p>
                    <p><strong>{{ trans('labels.work_note') }}:</strong> <span id="modal_work_note"></span></p>
                    <p><strong>{{ trans('labels.file') }}:</strong> <span id="modal_file"></span></p>
                </div>
                <hr>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    var baseUrl = "{{ asset('storage/') }}"; // Base URL for profile pictures
    var defaultImageUrl = "{{ asset('img/user.png') }}"; // Default profile picture URL

    var pendingTable = loadDataTable('pendingRequests', '{{ route("attendance_requests.pending") }}');
    var approvedTable = loadDataTable('approvedRequests', '{{ route("attendance_requests.approved") }}');
    var rejectedTable = loadDataTable('rejectedRequests', '{{ route("attendance_requests.rejected") }}');

    function loadDataTable(tableId, url) {
        return $('#' + tableId).DataTable({
            processing: true,
            serverSide: true,
            ajax: url,
            columns: [
                { data: 'date' },
                { data: 'employee_id' },
                {
                    data: 'full_name',
                    name: 'full_name',
                    render: function(data, type, row) {
                        var profilePictureUrl = row.profile_picture ? baseUrl + '/' + row.profile_picture : defaultImageUrl;
                        return '<img src="' + profilePictureUrl + '" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;">' + data;
                    }
                },
                { data: 'clock_in' },
                { data: 'clock_out' },
                { data: 'work_note' },
                {
                    data: 'file',
                    render: function(data, type, row) {
                        if (data) {
                            return `<a href="{{ url('storage/${data}') }}" target="_blank">View file</a>`;
                        } else {
                            return 'No file provided';
                        }
                    }
                },
                { data: 'status_approval' },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                            <a href="#" class="btn btn-sm btn-primary" id="attendanceShow" 
                               data-id="${row.id}" 
                               data-employee_id="${row.employee_id}" 
                               data-full_name="${row.full_name}" 
                               data-date="${row.date}" 
                               data-clock_in="${row.clock_in}" 
                               data-clock_out="${row.clock_out}" 
                               data-work_note="${row.work_note}" 
                               data-file="${row.file}" 
                               data-status="${row.status_approval}">
                               <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-success approveButton" data-id="${row.id}">Approve</button>
                            <button class="btn btn-sm btn-danger rejectButton" data-id="${row.id}">Reject</button>
                            <button class="btn btn-danger btn-sm deleteButton" data-id="${row.id}"><i class="fas fa-trash-alt"></i></button>
                        `;
                    }
                }
            ],
            drawCallback: function() {
                attachEventListeners();
            }
        });
    }

    function attachEventListeners() {
        $('#pendingRequests').off('click', '.deleteButton').on('click', '.deleteButton', function() {
            let rowData = pendingTable.row($(this).closest('tr')).data();
            if (rowData) {
                let route = '{{ route('attendance_requests.destroy', ':id') }}';
                deleteAjax(route.replace(':id', rowData.id), rowData.id, pendingTable);
            }
        });

        $('#approvedRequests').off('click', '.deleteButton').on('click', '.deleteButton', function() {
            let rowData = approvedTable.row($(this).closest('tr')).data();
            if (rowData) {
                let route = '{{ route('attendance_requests.destroy', ':id') }}';
                deleteAjax(route.replace(':id', rowData.id), rowData.id, approvedTable);
            }
        });

        $('#rejectedRequests').off('click', '.deleteButton').on('click', '.deleteButton', function() {
            let rowData = rejectedTable.row($(this).closest('tr')).data();
            if (rowData) {
                let route = '{{ route('attendance_requests.destroy', ':id') }}';
                deleteAjax(route.replace(':id', rowData.id), rowData.id, rejectedTable);
            }
        });
    }

    $(document).on('click', '#attendanceShow', function() {
        var data = $(this).data();
        $('#modal_employee_id').text(data.employee_id);
        $('#modal_employee_name').text(data.full_name);
        $('#modal_date').text(data.date);
        $('#modal_clock_in').text(data.clock_in);
        $('#modal_clock_out').text(data.clock_out);
        $('#modal_work_note').text(data.work_note);
        $('#modal_file').html(data.file ? `<a href="{{ url('storage/${data.file}') }}" target="_blank">View file</a>` : 'No file provided');
        $('#attendanceDetailModal').modal('show');
    });

    $(document).on('click', '.approveButton, .rejectButton, .pendingButton', function() {
        var id = $(this).data('id');
        var status = $(this).hasClass('approveButton') ? 'approved' :
                     $(this).hasClass('rejectButton') ? 'rejected' : 'pending';
        updateStatus(id, status);
    });

    function updateStatus(id, status) {
        $.ajax({
            url: '{{ route("attendance_requests.update-status", ":id") }}'.replace(':id', id),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: status
            },
            success: function(response) {
                if (response.success) {
                    alert('Status updated successfully');
                    pendingTable.ajax.reload();
                    approvedTable.ajax.reload();
                    rejectedTable.ajax.reload();
                } else {
                    alert(response.message || 'Failed to update status');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                alert('Error updating status: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unknown error'));
            }
        });
    }

    function deleteAjax(route, id, table) {
        if (confirm('Are you sure you want to delete this record?')) {
            $.ajax({
                url: route,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Record deleted successfully');
                        table.draw();
                    } else {
                        alert('Failed to delete the record');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Failed to delete the record');
                }
            });
        }
    }
});
</script>
<style>
    .smaller-text {
        font-size: 0.75rem; /* Adjust this value to make the text smaller */
    }
</style>
@stop
