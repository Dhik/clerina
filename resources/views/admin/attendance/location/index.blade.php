@extends('adminlte::page')

@section('title', 'Locations')

@section('content_header')
    <h1>Locations</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('location.create') }}" class="btn btn-primary">Add Location</a>
                </div>
                <div class="card-body">
                    <table id="locationTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Setting Name</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Employees Assigned</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            var table = $('#locationTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('location.data') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'setting_name', name: 'setting_name' },
                    { data: 'lat', name: 'lat' },
                    { data: 'long', name: 'long' },
                    { data: 'employees_count', name: 'employees_count', searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            table.on('draw.dt', function() {
                const tableBodySelector =  $('#locationTable tbody');

                tableBodySelector.on('click', '#locationShow', function(event) {
                    event.preventDefault();
                    let rowData = table.row($(this).closest('tr')).data();
                    showLocation(rowData);
                });

                tableBodySelector.on('click', '#locationEdit', function(event) {
                    event.preventDefault();
                    let rowData = table.row($(this).closest('tr')).data();
                    editLocation(rowData);
                });

                tableBodySelector.on('click', '.deleteButton', function() {
                    let rowData = table.row($(this).closest('tr')).data();
                    let route = '{{ route('location.destroy', ':id') }}'.replace(':id', rowData.id);
                    deleteAjax(route, rowData.id, table);
                });
            });
        });
    </script>
@stop
