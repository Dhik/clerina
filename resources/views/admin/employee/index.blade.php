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
                    <div class="row">
                        <div class="col-lg-2 col-6">
                            <div id="totalEmployeesCard" class="small-box bg-purple p-2 filter-card" data-filter="all">
                                <h5 class="text-center">Total Employees</h5>
                                <div class="row text-center">
                                    <div class="col-12">
                                        <div class="inner">
                                            <h4 id="totalEmployees">0</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div id="newHiresCard" class="small-box bg-success p-2 filter-card" data-filter="newHires">
                                <h5 class="text-center">New Hire</h5>
                                <div class="row text-center">
                                    <div class="col-12">
                                        <div class="inner">
                                            <h4 id="newHires">0</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div id="leavingsCard" class="small-box bg-maroon p-2 filter-card" data-filter="leavings">
                                <h5 class="text-center">Leaving</h5>
                                <div class="row text-center">
                                    <div class="col-12">
                                        <div class="inner">
                                            <h4 id="leavings">0</h4>
                                        </div>
                                    </div>
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
