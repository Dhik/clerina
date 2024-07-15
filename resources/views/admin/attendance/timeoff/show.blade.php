@extends('adminlte::page')

@section('title', trans('labels.timeoff'))

@section('content_header')
    <h1>{{ trans('labels.timeoff') }} {{ trans('labels.request') }}</h1>
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
                        @include('admin.attendance.timeoff.partials.table', ['tableId' => 'pendingTimeOffs'])
                    </div>
                    <div class="tab-pane" id="approvedTab">
                        @include('admin.attendance.timeoff.partials.table', ['tableId' => 'approvedTimeOffs'])
                    </div>
                    <div class="tab-pane" id="rejectedTab">
                        @include('admin.attendance.timeoff.partials.table', ['tableId' => 'rejectedTimeOffs'])
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
                    <p><strong>{{ trans('labels.time_off_type') }}:</strong> <span id="modal_time_off_type"></span></p>
                    <p><strong>{{ trans('labels.request_type') }}:</strong> <span id="modal_request_type"></span></p>
                    <p><strong>{{ trans('labels.delegate_to') }}:</strong> <span id="modal_delegate_to"></span></p>
                    <p><strong>{{ trans('labels.file') }}:</strong> <span id="modal_file"></span></p>
                    <p><strong>{{ trans('labels.reason') }}:</strong> <span id="modal_reason"></span></p>
                </div>
                <hr>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="attendanceEditModal" tabindex="-1" role="dialog" aria-labelledby="attendanceEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceEditModalLabel">{{ trans('labels.edit') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Edit Form Section -->
                <form id="editForm">
                    <div class="form-group">
                        <label for="edit_employee_id">{{ trans('labels.employee_id') }}</label>
                        <input type="text" class="form-control" id="edit_employee_id" name="employee_id" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_employee_name">{{ trans('labels.employee_name') }}</label>
                        <input type="text" class="form-control" id="edit_employee_name" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_date">{{ trans('labels.date') }}</label>
                        <input type="date" class="form-control" id="edit_date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_reason">{{ trans('labels.reason') }}</label>
                        <textarea class="form-control" id="edit_reason" name="reason" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">{{ trans('labels.status') }}</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="pending">{{ trans('labels.pending') }}</option>
                            <option value="approved">{{ trans('labels.approved') }}</option>
                            <option value="rejected">{{ trans('labels.rejected') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ trans('labels.save_changes') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    var baseUrl = "{{ asset('storage/') }}"; // Base URL for profile pictures
    var defaultImageUrl = "{{ asset('img/user.png') }}";// Default profile picture URL

    var pendingTable = loadDataTable('pendingTimeOffs', '{{ route("timeoffs.pending") }}');
    var approvedTable = loadDataTable('approvedTimeOffs', '{{ route("timeoffs.approved") }}');
    var rejectedTable = loadDataTable('rejectedTimeOffs', '{{ route("timeoffs.rejected") }}');

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
                { data: 'time_off_type' },
                { data: 'request_type' },
                { data: 'reason' },
                { data: 'delegate_to' },
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
                               data-time_off_type="${row.time_off_type}" 
                               data-request_type="${row.request_type}" 
                               data-delegate_to="${row.delegate_to}" 
                               data-file="${row.file}" 
                               data-reason="${row.reason}" 
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
        $('#pendingTimeOffs').off('click', '.deleteButton').on('click', '.deleteButton', function() {
            let rowData = pendingTable.row($(this).closest('tr')).data();
            let route = '{{ route('timeoffs.destroy', ':id') }}';
            deleteAjax(route.replace(':id', rowData.id), rowData.id, pendingTable);
        });

        $('#approvedTimeOffs').off('click', '.deleteButton').on('click', '.deleteButton', function() {
            let rowData = approvedTable.row($(this).closest('tr')).data();
            let route = '{{ route('timeoffs.destroy', ':id') }}';
            deleteAjax(route.replace(':id', rowData.id), rowData.id, approvedTable);
        });

        $('#rejectedTimeOffs').off('click', '.deleteButton').on('click', '.deleteButton', function() {
            let rowData = rejectedTable.row($(this).closest('tr')).data();
            let route = '{{ route('timeoffs.destroy', ':id') }}';
            deleteAjax(route.replace(':id', rowData.id), rowData.id, rejectedTable);
        });
    }

    $(document).on('click', '#attendanceShow', function() {
        var data = $(this).data();
        $('#modal_employee_id').text(data.employee_id);
        $('#modal_employee_name').text(data.full_name);
        $('#modal_date').text(data.date);
        $('#modal_reason').text(data.reason);
        $('#modal_time_off_type').text(data.time_off_type);
        $('#modal_request_type').text(data.request_type);
        $('#modal_delegate_to').text(data.delegate_to);
        $('#modal_file').html(data.file ? `<a href="{{ url('storage/${data.file}') }}" target="_blank">View file</a>` : 'No file provided');
        $('#attendanceDetailModal').modal('show');
    });

    $(document).on('click', '#attendanceEdit', function() {
        var data = $(this).data();
        $('#edit_employee_id').val(data.employee_id);
        $('#edit_employee_name').val(data.full_name);
        $('#edit_date').val(data.date);
        $('#edit_reason').val(data.reason);
        $('#edit_time_off_type').val(data.time_off_type);
        $('#edit_request_type').val(data.request_type);
        $('#edit_delegate_to').val(data.delegate_to);
        $('#edit_file').val(data.file);
        $('#edit_status').val(data.status);
        $('#attendanceEditModal').modal('show');
    });

    $(document).on('click', '.approveButton', function() {
        var id = $(this).data('id');
        updateStatus(id, 'approved');
    });

    $(document).on('click', '.rejectButton', function() {
        var id = $(this).data('id');
        updateStatus(id, 'rejected');
    });

    $(document).on('click', '.pendingButton', function() {
        var id = $(this).data('id');
        updateStatus(id, 'pending');
    });

    function updateStatus(id, status) {
        $.ajax({
            url: '{{ route("timeoffs.update-status", ":id") }}'.replace(':id', id),
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
                    alert('Failed to update status');
                }
            },
            error: function() {
                alert('Error updating status');
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
