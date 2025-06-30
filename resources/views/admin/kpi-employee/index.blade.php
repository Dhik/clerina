@extends('adminlte::page')

@section('title', 'KPI Employee')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>KPI Employee Management</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">KPI Employee</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Dashboard Statistics -->
    <div class="row mb-4">
        <!-- Overall Stats -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $overallStats['employees'] }}</h3>
                    <p>Total Employees</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $overallStats['kpis'] }}</h3>
                    <p>Total KPIs</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $overallStats['avg_weight'] }}%</h3>
                    <p>Avg Weight</p>
                </div>
                <div class="icon">
                    <i class="fas fa-weight"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-{{ $overallStats['avg_achievement'] >= 100 ? 'success' : ($overallStats['avg_achievement'] >= 75 ? 'warning' : 'danger') }}">
                <div class="inner">
                    <h3>{{ $overallStats['avg_achievement'] }}%</h3>
                    <p>Avg Achievement</p>
                </div>
                <div class="icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Department and Position Statistics -->
    <div class="row mb-4">
        <!-- Department Stats -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building mr-2"></i>
                        Department Performance
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Employees</th>
                                <th>KPIs</th>
                                <th>Achievement</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departmentStats as $dept)
                                <tr>
                                    <td>
                                        <i class="fas fa-{{ $dept['department'] == 'Sales' ? 'handshake' : ($dept['department'] == 'Finance' ? 'dollar-sign' : ($dept['department'] == 'Human Capital' ? 'user-tie' : 'bullhorn')) }} mr-2 text-muted"></i>
                                        {{ $dept['department'] }}
                                    </td>
                                    <td><span class="badge badge-primary">{{ $dept['employees'] }}</span></td>
                                    <td><span class="badge badge-info">{{ $dept['kpis'] }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ $dept['achievement'] >= 100 ? 'success' : ($dept['achievement'] >= 75 ? 'warning' : 'danger') }}">
                                            {{ $dept['achievement'] }}%
                                        </span>
                                    </td>
                                    <td>
                                        @if($dept['achievement'] >= 100)
                                            <i class="fas fa-check-circle text-success"></i> Excellent
                                        @elseif($dept['achievement'] >= 75)
                                            <i class="fas fa-exclamation-triangle text-warning"></i> Good
                                        @else
                                            <i class="fas fa-times-circle text-danger"></i> Needs Improvement
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Position Stats -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-cog mr-2"></i>
                        Position Performance
                    </h3>
                </div>
                <div class="card-body">
                    @foreach($positionStats as $pos)
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-{{ $pos['position'] == 'Leader' ? 'primary' : 'info' }}">
                                <i class="fas fa-{{ $pos['position'] == 'Leader' ? 'crown' : 'user' }}"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ $pos['position'] }}</span>
                                <span class="info-box-number">{{ $pos['achievement'] }}%</span>
                                <div class="progress">
                                    <div class="progress-bar bg-{{ $pos['achievement'] >= 100 ? 'success' : ($pos['achievement'] >= 75 ? 'warning' : 'danger') }}" 
                                         style="width: {{ min($pos['achievement'], 100) }}%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ $pos['employees'] }} employees, {{ $pos['kpis'] }} KPIs
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Employee KPI List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        Employee KPI List
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('kPIEmployee.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New KPI
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="kpi-employee-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Employee Information</th>
                                <th>KPI Count</th>
                                <th>Total Weight</th>
                                <th>Avg Achievement</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <style>
        .small-box .icon {
            font-size: 70px;
        }
        .info-box {
            margin-bottom: 1rem;
        }
        .table td {
            vertical-align: middle;
        }
        .img-circle {
            border: 2px solid #ddd;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#kpi-employee-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('kPIEmployee.data') }}",
                columns: [
                    {
                        data: 'employee_info',
                        name: 'full_name',
                        orderable: true,
                        searchable: true,
                        width: '40%'
                    },
                    {
                        data: 'kpi_count',
                        name: 'kpi_count',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '15%'
                    },
                    {
                        data: 'total_weight',
                        name: 'total_weight',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '15%'
                    },
                    {
                        data: 'avg_achievement',
                        name: 'avg_achievement',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '15%'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '15%'
                    }
                ],
                order: [[0, 'asc']],
                pageLength: 25,
                responsive: true,
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
                }
            });
        });
    </script>
@stop