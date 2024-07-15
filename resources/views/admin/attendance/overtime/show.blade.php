@extends('adminlte::page')

@section('title', trans('labels.overtime'))

@section('content_header')
    <h1>{{ trans('labels.overtime') }} {{ trans('labels.request') }}</h1>
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
                        @include('admin.attendance.overtime.partials.table', ['tableId' => 'pendingOvertime'])
                    </div>
                    <div class="tab-pane" id="approvedTab">
                        @include('admin.attendance.overtime.partials.table', ['tableId' => 'approvedOvertime'])
                    </div>
                    <div class="tab-pane" id="rejectedTab">
                        @include('admin.attendance.overtime.partials.table', ['tableId' => 'rejectedOvertime'])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Show Detail Modal -->
<div class="modal fade" id="overtimeDetailModal" tabindex="-1" role="dialog" aria-labelledby="overtimeDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="overtimeDetailModalLabel">{{ trans('labels.details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p><strong>{{ trans('labels.employee_id') }}:</strong> <span id="modal_employee_id"></span></p>
                    <p><strong>{{ trans('labels.name') }}:</strong> <span id="modal_employee_name"></span></p>
                    <p><strong>{{ trans('labels.date') }}:</strong> <span id="modal_date"></span></p>
                    <p><strong>{{ trans('labels.compensation') }}:</strong> <span id="modal_compensation"></span></p>
                    <p><strong>{{ trans('labels.before_shift_overtime_duration') }}:</strong> <span id="modal_before_shift_overtime_duration"></span></p>
                    <p><strong>{{ trans('labels.before_shift_break_duration') }}:</strong> <span id="modal_before_shift_break_duration"></span></p>
                    <p><strong>{{ trans('labels.after_shift_overtime_duration') }}:</strong> <span id="modal_after_shift_overtime_duration"></span></p>
                    <p><strong>{{ trans('labels.after_shift_break_duration') }}:</strong> <span id="modal_after_shift_break_duration"></span></p>
                    <p><strong>{{ trans('labels.note') }}:</strong> <span id="modal_note"></span></p>
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
    var defaultImageUrl = "{{ asset('img/user.png') }}";// Default profile picture URL

    var pendingTable = loadDataTable('pendingOvertime', '{{ route("overtime.pending") }}');
    var approvedTable = loadDataTable('approvedOvertime', '{{ route("overtime.approved") }}');
    var rejectedTable = loadDataTable('rejectedOvertime', '{{ route("overtime.rejected") }}');

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
                { data: 'compensation' },
                { data: 'note' },
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
                            <a href="#" class="btn btn-sm btn-primary" id="overtimeShow" 
                               data-id="${row.id}" 
                               data-employee_id="${row.employee_id}" 
                               data-full_name="${row.full_name}" 
                               data-date="${row.date}" 
                               data-compensation="${row.compensation}" 
                               data-before_shift_overtime_duration="${row.before_shift_overtime_duration}" 
                               data-before_shift_break_duration="${row.before_shift_break_duration}" 
                               data-after_shift_overtime_duration="${row.after_shift_overtime_duration}" 
                               data-after_shift_break_duration="${row.after_shift_break_duration}" 
                               data-note="${row.note}" 
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
        $('#pendingOvertime').off('click', '.deleteButton').on('click', '.deleteButton', function() {
            let rowData = pendingTable.row($(this).closest('tr')).data();
            let route = '{{ route('overtimes.destroy', ':id') }}';
            deleteAjax(route.replace(':id', rowData.id), rowData.id, pendingTable);
        });

        $('#approvedOvertime').off('click', '.deleteButton').on('click', '.deleteButton', function() {
            let rowData = approvedTable.row($(this).closest('tr')).data();
            let route = '{{ route('overtimes.destroy', ':id') }}';
            deleteAjax(route.replace(':id', rowData.id), rowData.id, approvedTable);
        });

        $('#rejectedOvertime').off('click', '.deleteButton').on('click', '.deleteButton', function() {
            let rowData = rejectedTable.row($(this).closest('tr')).data();
            let route = '{{ route('overtimes.destroy', ':id') }}';
            deleteAjax(route.replace(':id', rowData.id), rowData.id, rejectedTable);
        });
    }

    $(document).on('click', '#overtimeShow', function() {
        var data = $(this).data();
        $('#modal_employee_id').text(data.employee_id);
        $('#modal_employee_name').text(data.full_name);
        $('#modal_date').text(data.date);
        $('#modal_compensation').text(data.compensation);
        $('#modal_before_shift_overtime_duration').text(data.before_shift_overtime_duration);
        $('#modal_before_shift_break_duration').text(data.before_shift_break_duration);
        $('#modal_after_shift_overtime_duration').text(data.after_shift_overtime_duration);
        $('#modal_after_shift_break_duration').text(data.after_shift_break_duration);
        $('#modal_note').text(data.note);
        $('#modal_file').html(data.file ? `<a href="{{ url('storage/${data.file}') }}" target="_blank">View file</a>` : 'No file provided');
        $('#overtimeDetailModal').modal('show');
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
            url: '{{ route("overtime.update-status", ":id") }}'.replace(':id', id),
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

