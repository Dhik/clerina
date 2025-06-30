@extends('adminlte::page')

@section('title', 'My KPI Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-user-chart mr-2"></i>
                My KPI Dashboard
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kPIEmployee.index') }}">KPI Employee</a></li>
                <li class="breadcrumb-item active">My KPI</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Personal KPI Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $personalStats['total_kpis'] }}</h3>
                    <p>My KPIs</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-{{ $personalStats['total_weight'] == 100 ? 'success' : ($personalStats['total_weight'] > 100 ? 'warning' : 'info') }}">
                <div class="inner">
                    <h3>{{ $personalStats['total_weight'] }}%</h3>
                    <p>Total Weight</p>
                </div>
                <div class="icon">
                    <i class="fas fa-weight"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-{{ $personalStats['avg_achievement'] >= 100 ? 'success' : ($personalStats['avg_achievement'] >= 75 ? 'warning' : 'danger') }}">
                <div class="inner">
                    <h3>{{ $personalStats['avg_achievement'] }}%</h3>
                    <p>Achievement</p>
                </div>
                <div class="icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $personalStats['achieved_kpis'] }}</h3>
                    <p>Achieved KPIs</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Personal Information -->
        <div class="col-md-4">
            <div class="card card-success card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ $currentEmployee->profile_picture ? asset('storage/' . $currentEmployee->profile_picture) : asset('vendor/adminlte/dist/img/user4-128x128.jpg') }}"
                             alt="User profile picture">
                    </div>

                    <h3 class="profile-username text-center">{{ $currentEmployee->full_name }}</h3>

                    <p class="text-muted text-center">{{ $currentEmployee->job_position }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Employee ID</b> <a class="float-right">{{ $currentEmployee->employee_id }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Organization</b> <a class="float-right">{{ $currentEmployee->organization }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Job Level</b> <a class="float-right">{{ $currentEmployee->job_level }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Join Date</b> <a class="float-right">{{ \Carbon\Carbon::parse($currentEmployee->join_date)->format('d M Y') }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Status</b> <a class="float-right">
                                <span class="badge badge-{{ $currentEmployee->status_employee == 'Active' ? 'success' : 'danger' }}">
                                    {{ $currentEmployee->status_employee }}
                                </span>
                            </a>
                        </li>
                        <li class="list-group-item">
                            <b>Role</b> <a class="float-right">
                                @if($personalStats['is_leader'])
                                    <span class="badge badge-primary">
                                        <i class="fas fa-crown mr-1"></i>Leader
                                    </span>
                                @else
                                    <span class="badge badge-info">
                                        <i class="fas fa-user mr-1"></i>Staff
                                    </span>
                                @endif
                            </a>
                        </li>
                    </ul>

                    <!-- KPI Progress by Perspective -->
                    @if($kpisByPerspective->count() > 0)
                        <div class="mt-4">
                            <h5>KPI Distribution</h5>
                            @foreach($kpisByPerspective as $perspective => $perspectiveKpis)
                                <div class="mb-2">
                                    <small>{{ $perspective }}</small>
                                    <div class="progress progress-sm">
                                        @php
                                            $perspectiveAchievement = 0;
                                            $validKpis = 0;
                                            foreach($perspectiveKpis as $kpi) {
                                                if($kpi->target > 0) {
                                                    $perspectiveAchievement += ($kpi->actual / $kpi->target) * 100;
                                                    $validKpis++;
                                                }
                                            }
                                            $avgPerspectiveAchievement = $validKpis > 0 ? $perspectiveAchievement / $validKpis : 0;
                                        @endphp
                                        <div class="progress-bar bg-{{ $avgPerspectiveAchievement >= 100 ? 'success' : ($avgPerspectiveAchievement >= 75 ? 'warning' : 'danger') }}" 
                                             style="width: {{ min($avgPerspectiveAchievement, 100) }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ number_format($avgPerspectiveAchievement, 1) }}% ({{ $perspectiveKpis->count() }} KPIs)</small>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- My KPIs -->
        <div class="col-md-8">
            @if($personalStats['is_leader'])
                <!-- Leader: Show Staff KPIs first, then own KPIs -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-2"></i>
                            My Team's KPIs
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            As a department leader, you can monitor and input actual values for your team's KPIs.
                        </div>
                        <table id="staff-kpi-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Employee Name</th>
                                    <th>Perspective</th>
                                    <th>KPI</th>
                                    <th>Target</th>
                                    <th>Actual</th>
                                    <th>Achievement</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <!-- Leader's Own KPIs -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-circle mr-2"></i>
                            My Personal KPIs
                        </h3>
                    </div>
                    <div class="card-body">
                        <table id="leader-kpi-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Perspective</th>
                                    <th>KPI</th>
                                    <th>Target</th>
                                    <th>Actual</th>
                                    <th>Weight %</th>
                                    <th>Achievement</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            @else
                <!-- Staff: Show only own KPIs -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>
                            My KPI Performance
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($personalStats['total_kpis'] == 0)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                You don't have any KPIs assigned yet. Please contact your supervisor or HR department.
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-info-circle"></i>
                                Track your performance and achievement progress below. Contact your supervisor for actual value updates.
                            </div>
                        @endif
                        
                        <table id="my-kpi-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Perspective</th>
                                    <th>KPI</th>
                                    <th>Target</th>
                                    <th>Actual</th>
                                    <th>Weight %</th>
                                    <th>Achievement</th>
                                    <th>Status</th>
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
    <style>
        .small-box .icon {
            font-size: 70px;
        }
        .progress {
            height: 10px;
        }
        .card-success {
            border-top: 3px solid #28a745;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            @if($personalStats['is_leader'])
                // Leader: Staff KPIs table
                $('#staff-kpi-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('kPIEmployee.staffKpiData') }}",
                        data: {
                            employee_id: "{{ $currentEmployee->id }}"
                        }
                    },
                    columns: [
                        {
                            data: null,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {data: 'employee_name', name: 'employee.full_name'},
                        {data: 'perspective', name: 'perspective'},
                        {data: 'kpi', name: 'kpi'},
                        {
                            data: 'target',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {
                            data: 'actual',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {data: 'achievement', orderable: false, searchable: false},
                        {data: 'action', orderable: false, searchable: false}
                    ],
                    order: [[1, 'asc']],
                    pageLength: 5,
                    responsive: true
                });

                // Leader's own KPIs
                $('#leader-kpi-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('kPIEmployee.kpiData', $currentEmployee->id) }}",
                    columns: [
                        {
                            data: null,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {data: 'perspective', name: 'perspective'},
                        {data: 'kpi', name: 'kpi'},
                        {
                            data: 'target',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {
                            data: 'actual',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {data: 'bobot', name: 'bobot'},
                        {data: 'achievement', orderable: false, searchable: false},
                        {data: 'action', orderable: false, searchable: false}
                    ],
                    order: [[1, 'asc']],
                    pageLength: 5,
                    responsive: true
                });
            @else
                // Staff: Own KPIs only
                $('#my-kpi-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('kPIEmployee.kpiData', $currentEmployee->id) }}",
                    columns: [
                        {
                            data: null,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {data: 'perspective', name: 'perspective'},
                        {data: 'kpi', name: 'kpi'},
                        {
                            data: 'target',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {
                            data: 'actual',
                            render: function(data) {
                                return new Intl.NumberFormat('id-ID').format(data);
                            }
                        },
                        {data: 'bobot', name: 'bobot'},
                        {data: 'achievement', orderable: false, searchable: false},
                        {
                            data: null,
                            render: function(data, type, row) {
                                var achievement = 0;
                                if (row.target > 0) {
                                    achievement = (row.actual / row.target) * 100;
                                }
                                if (achievement >= 100) {
                                    return '<span class="badge badge-success"><i class="fas fa-check"></i> Achieved</span>';
                                } else if (achievement >= 75) {
                                    return '<span class="badge badge-warning"><i class="fas fa-clock"></i> In Progress</span>';
                                } else {
                                    return '<span class="badge badge-danger"><i class="fas fa-exclamation"></i> Below Target</span>';
                                }
                            },
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [[1, 'asc']],
                    pageLength: 10,
                    responsive: true
                });
            @endif
        });
    </script>
@stop