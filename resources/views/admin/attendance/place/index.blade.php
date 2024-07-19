@extends('adminlte::page')

@section('title', 'Places')

@section('content_header')
    <h1>Places</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('place.create') }}" class="btn btn-primary">Add Place</a>
                </div>
                <div class="card-body">
                    <table id="placeTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Place</th>
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
            var table = $('#placeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('place.data') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'place', name: 'place' },
                    { data: 'employees_count', name: 'employees_count', searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            table.on('draw.dt', function() {
                const tableBodySelector =  $('#placeTable tbody');

                tableBodySelector.on('click', '#placeShow', function(event) {
                    event.preventDefault();
                    let rowData = table.row($(this).closest('tr')).data();
                    showPlace(rowData);
                });

                tableBodySelector.on('click', '#placeEdit', function(event) {
                    event.preventDefault();
                    let rowData = table.row($(this).closest('tr')).data();
                    editPlace(rowData);
                });

                tableBodySelector.on('click', '.deleteButton', function() {
                    let rowData = table.row($(this).closest('tr')).data();
                    let route = '{{ route('place.destroy', ':id') }}'.replace(':id', rowData.id);
                    deleteAjax(route, rowData.id, table);
                });
            });
        });
    </script>
@stop
