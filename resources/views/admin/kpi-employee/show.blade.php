@extends('adminlte::page')

@section('title', 'Employee KPI Detail')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Employee KPI Detail</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kPIEmployee.index') }}">KPI Employee</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Employee Information -->
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ $employee->profile_picture ? asset('storage/' . $employee->profile_picture) : asset('vendor/adminlte/dist/img/user4-128x128.jpg') }}"
                             alt="User profile picture">
                    </div>

                    <h3 class="profile-username text-center">{{ $employee->full_name }}</h3>

                    <p class="text-muted text-center">{{ $employee->job_position }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Employee ID</b> <a class="float-right">{{ $employee->employee_id }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Organization</b> <a class="float-right">{{ $employee->organization }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Job Level</b> <a class="float-right">{{ $employee->job_level }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Join Date</b> <a class="float-right">{{ \Carbon\Carbon::parse($employee->join_date)->format('d M Y') }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Status</b> <a class="float-right">
                                <span class="badge badge-{{ $employee->status_employee == 'Active' ? 'success' : 'danger' }}">
                                    {{ $employee->status_employee }}
                                </span>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <b>Email</b> <a class="float-right">{{ $employee->email }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Total KPIs</b> <a class="float-right">{{ $employee->kpiEmployees->count() }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Total Weight</b> <a class="float-right">{{ $employee->kpiEmployees->sum('bobot') }}%</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-8">
            @if($isLeader && $isViewingOwnProfile)
                <!-- Leader View: Show Department Staff KPIs -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Department Staff KPIs</h3>
                        <div class="card-tools">
                            <a href="{{ route('kPIEmployee.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add New KPI
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            As a department leader, you can view and manage KPIs for all staff in your department(s).
                        </div>
                        <table id="staff-kpi-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Employee Name</th>
                                    <th>Perspective</th>
                                    <th>Key Performance Indicator (KPI)</th>
                                    <th>Target</th>
                                    <th>Method</th>
                                    <th>Weight %</th>
                                    <th>Data Source</th>
                                    <th>Actual</th>
                                    <th>Achievement %</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <!-- Leader's Own KPIs -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">My KPIs</h3>
                    </div>
                    <div class="card-body">
                        <table id="leader-kpi-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Perspective</th>
                                    <th>KPI</th>
                                    <th>Target</th>
                                    <th>Method</th>
                                    <th>Weight %</th>
                                    <th>Data Source</th>
                                    <th>Actual</th>
                                    <th>Achievement %</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            @else
                <!-- Staff View: Show Only Own KPIs OR when viewing other employee's profile -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            @if($currentEmployee && $currentEmployee->id === $employee->id)
                                My KPIs
                            @else
                                KPI Assignments
                            @endif
                        </h3>
                        @if($currentEmployee && $currentEmployee->id === $employee->id)
                            <div class="card-tools">
                                <a href="{{ route('kPIEmployee.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add New KPI
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($employee->kpiEmployees->where('position', 'Staff')->count() > 0 && $currentEmployee && $currentEmployee->id !== $employee->id)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Only department leaders can input actual values for staff KPIs.
                            </div>
                        @endif
                        <table id="kpi-detail-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Perspective</th>
                                    <th>KPI</th>
                                    <th>Target</th>
                                    <th>Method</th>
                                    <th>Weight %</th>
                                    <th>Data Source</th>
                                    <th>Actual</th>
                                    <th>Achievement %</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            @if($isLeader && $isViewingOwnProfile)
                // Leader view: Staff KPIs table (individual KPIs, not grouped by employee)
                $('#staff-kpi-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('kPIEmployee.staffKpiData') }}",
                    columns: [
                        {
                            data: null,
                            name: 'row_number',
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: 'employee_name',
                            name: 'employee.full_name',
                            orderable: true,
                            searchable: true
                        },
                        {
                            data: 'perspective',
                            name: 'perspective'
                        },
                        {
                            data: 'kpi',
                            name: 'kpi'
                        },
                        {
                            data: 'target',
                            name: 'target',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {
                            data: 'method_calculation',
                            name: 'method_calculation'
                        },
                        {
                            data: 'bobot',
                            name: 'bobot'
                        },
                        {
                            data: 'data_source',
                            name: 'data_source'
                        },
                        {
                            data: 'actual',
                            name: 'actual',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {
                            data: 'achievement',
                            name: 'achievement',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [[1, 'asc']],
                    pageLength: 10,
                    responsive: true
                });

                // Leader's own KPIs
                $('#leader-kpi-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('kPIEmployee.kpiData', $employee->id) }}",
                    columns: [
                        {
                            data: null,
                            name: 'row_number',
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {data: 'perspective', name: 'perspective'},
                        {data: 'kpi', name: 'kpi'},
                        {
                            data: 'target', 
                            name: 'target',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {data: 'method_calculation', name: 'method_calculation'},
                        {data: 'bobot', name: 'bobot'},
                        {data: 'data_source', name: 'data_source'},
                        {
                            data: 'actual', 
                            name: 'actual',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {data: 'achievement', name: 'achievement', orderable: false, searchable: false},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ],
                    order: [[2, 'asc']],
                    pageLength: 5,
                    responsive: true
                });
            @else
                // Staff view: Own KPIs only
                $('#kpi-detail-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('kPIEmployee.kpiData', $employee->id) }}",
                    columns: [
                        {
                            data: null,
                            name: 'row_number',
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {data: 'perspective', name: 'perspective'},
                        {data: 'kpi', name: 'kpi'},
                        {
                            data: 'target', 
                            name: 'target',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {data: 'method_calculation', name: 'method_calculation'},
                        {data: 'bobot', name: 'bobot'},
                        {data: 'data_source', name: 'data_source'},
                        {
                            data: 'actual', 
                            name: 'actual',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {data: 'achievement', name: 'achievement', orderable: false, searchable: false},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ],
                    order: [[2, 'asc']],
                    pageLength: 10,
                    responsive: true
                });
            @endif
        });

        function deleteKpi(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/kpi-employee/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                );
                                @if($isLeader && $isViewingOwnProfile)
                                    $('#staff-kpi-table').DataTable().ajax.reload();
                                    $('#leader-kpi-table').DataTable().ajax.reload();
                                @else
                                    $('#kpi-detail-table').DataTable().ajax.reload();
                                @endif
                            }
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'Something went wrong.',
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
@stop