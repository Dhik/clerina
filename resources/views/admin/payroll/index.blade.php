@extends('adminlte::page')

@section('title', trans('labels.employee'))

@section('content_header')
    <h1>Payroll {{ trans('labels.employee') }}</h1>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="userTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="userTable-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.id') }}</th>
                                <th>{{ trans('labels.name') }}</th>
                                <th>{{ trans('labels.gaji_pokok') }}</th>
                                <th>{{ trans('labels.netSalary') }}</th>
                                <th>{{ trans('labels.salaryDeductions') }}</th>
                                <th>{{ trans('labels.insentif') }}</th>
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
                    url: "{{ route('payroll.get') }}",
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
                    {data: 'gaji_pokok', name: 'gaji_pokok'},
                    {data: 'netSalary', name: 'netSalary'},
                    {data: 'salaryDeductions', name: 'salaryDeductions'},
                    {data: 'insentif', name: 'insentif'},
                    {
                        data: 'actions', 
                        name: 'actions', 
                        orderable: false, 
                        searchable: false,
                        render: function(data, type, row) {
                            return data;
                        }
                    }
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
