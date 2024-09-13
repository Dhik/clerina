@extends('adminlte::page')

@section('title', trans('labels.employee'))

@section('content_header')
    <h1>{{ trans('labels.employee') }}</h1>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-lg-4 col-6">
                            <a href="{{ route('employee.export') }}" class="btn btn-success"><i class="fas fa-file-download"></i> Download Employees Data</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-6">
                            <div id="totalEmployeesCard" class="small-box bg-info p-2 filter-card" data-filter="all">
                                <div class="inner">
                                    <h4 id="totalEmployees">0</h4>
                                    <p>Total Employees</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-id-badge"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div id="newHiresCard" class="small-box bg-success p-2 filter-card" data-filter="newHires">
                                <div class="inner">
                                    <h4 id="newHires">0</h4>
                                    <p>New Hire</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-arrow-up"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div id="leavingsCard" class="small-box bg-maroon p-2 filter-card" data-filter="leavings">
                                <div class="inner">
                                    <h4 id="leavings">0</h4>
                                    <p>Leaving</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-arrow-down"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table id="userTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="userTable-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.id') }}</th>
                                <th>{{ trans('labels.name') }}</th>
                                <th>{{ trans('labels.branch') }}</th>
                                <th>{{ trans('labels.organization') }}</th>
                                <th>{{ trans('labels.job_position') }}</th>
                                <th>{{ trans('labels.job_level') }}</th>
                                <th>{{ trans('labels.status') }}</th>
                                <th width="10%">{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <style>
        .smaller-text {
            font-size: 0.75rem; /* Adjust this value to make the text smaller */
        }
    </style>

    <script>
        $(function () {
            const userTableSelector = $('#userTable');
            var baseUrl = "{{ asset('storage/') }}";
            var defaultImageUrl = "{{ asset('img/user.png') }}";

            // datatable
            const table = userTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employee.get') }}",
                    data: function (d) {
                        d.date = $('#attendanceDate').val();
                    }
                },
                columns: [
                    {data: 'employee_id', name: 'employee_id'},
                    {
                        data: 'full_name', 
                        name: 'full_name',
                        render: function(data, type, row) {
                            var profilePictureUrl = row.profile_picture ? baseUrl + '/' + row.profile_picture : defaultImageUrl;
                            return '<img src="' + profilePictureUrl + '" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;">' + data;
                        }
                    },
                    {data: 'branch_name', name: 'branch_name'},
                    {data: 'organization', name: 'organization'},
                    {data: 'job_position', name: 'job_position'},
                    {data: 'job_level', name: 'job_level'},
                    {data: 'status_employee', name: 'status_employee'},
                    {data: 'actions', sortable: false, orderable: false}
                ]
            });

            // Apply the search
            $('.filter-input').keyup(function () {
                table.column($(this).data('column'))
                     .search($(this).val())
                     .draw();
            });

            $('#attendanceDate').change(function () {
                table.draw();
            });

            function updateOverviewCounts() {
                $.ajax({
                    url: "{{ route('employee.overview') }}",
                    method: 'GET',
                    success: function(data) {
                        $('#totalEmployees').text(data.totalEmployees);
                        $('#newHires').text(data.newHires);
                        $('#leavings').text(data.leavings);
                    }
                });
            }
            $(document).on('click', '.deleteButton', function () {
                var employeeId = $(this).data('id');
                var deleteUrl = "{{ route('employee.destroy', ':id') }}";
                deleteUrl = deleteUrl.replace(':id', employeeId);

                deleteAjax(deleteUrl, employeeId, table);
            });

            function deleteAjax(route, id, table) {
                Swal.fire({
                    title: '{{ trans('labels.are_you_sure') }}',
                    text: '{{ trans('labels.not_be_able_to_recover') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ trans('buttons.confirm_swal') }}',
                    cancelButtonText: '{{ trans('buttons.cancel_swal') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: route,
                            type: 'DELETE', // This ensures the correct HTTP method is used
                            data: {
                                _token: '{{ csrf_token() }}' // Include the CSRF token
                            },
                            success: function (response) {
                                Swal.fire(
                                    '{{ trans('labels.success') }}',
                                    '{{ trans('messages.success_delete') }}',
                                    'success'
                                ).then(() => {
                                    table.ajax.reload(); // Reload the DataTable after successful deletion
                                });
                            },
                            error: function (xhr, status, error) {
                                if (xhr.status === 422) {
                                    Swal.fire(
                                        '{{ trans('labels.failed') }}',
                                        xhr.responseJSON.message,
                                        'error'
                                    );
                                } else {
                                    Swal.fire(
                                        '{{ trans('labels.failed') }}',
                                        '{{ trans('messages.error_delete') }}',
                                        'error'
                                    );
                                }
                            }
                        });
                    }
                });
            }

            function reloadTable(filter) {
                var ajaxUrl;
                switch(filter) {
                    case 'newHires':
                        ajaxUrl = "{{ route('employees.newHires') }}";
                        break;
                    case 'leavings':
                        ajaxUrl = "{{ route('employees.leavings') }}";
                        break;
                    default:
                        ajaxUrl = "{{ route('employee.get') }}";
                        break;
                }

                table.ajax.url(ajaxUrl).load();
            }

            updateOverviewCounts();

            // Event listeners for the filter cards
            $('.filter-card').click(function() {
                var filter = $(this).data('filter');
                reloadTable(filter);
            });

            // Optional: Update counts when the date is changed if needed
            $('#attendanceDate').change(function () {
                updateOverviewCounts();
            });
        });
    </script>
@stop
