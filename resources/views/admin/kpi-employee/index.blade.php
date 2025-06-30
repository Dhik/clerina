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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Employee KPI List</h3>
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
                        searchable: true
                    },
                    {
                        data: 'kpi_count',
                        name: 'kpi_count',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'total_weight',
                        name: 'total_weight',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'avg_achievement',
                        name: 'avg_achievement',
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
                order: [[0, 'asc']],
                pageLength: 25,
                responsive: true
            });
        });
    </script>
@stop