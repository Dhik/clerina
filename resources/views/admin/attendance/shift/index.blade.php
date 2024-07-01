@extends('adminlte::page')

@section('title', 'Shifts')

@section('content_header')
    <h1>Shifts</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('shift.create') }}" class="btn btn-primary">Add Shift</a>
                </div>
                <div class="card-body">
                    <table id="shiftTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Shift Name</th>
                                <th>Schedule In</th>
                                <th>Schedule Out</th>
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
            var table = $('#shiftTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('shift.data') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'shift_name', name: 'shift_name' },
                    { data: 'schedule_in', name: 'schedule_in' },
                    { data: 'schedule_out', name: 'schedule_out' },
                    { data: 'employees_count', name: 'employees_count', searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            table.on('draw.dt', function() {
                const tableBodySelector =  $('#shiftTable tbody');

                tableBodySelector.on('click', '#shiftShow', function(event) {
                    event.preventDefault();
                    let rowData = table.row($(this).closest('tr')).data();
                    showShift(rowData);
                });

                tableBodySelector.on('click', '#shiftEdit', function(event) {
                    event.preventDefault();
                    let rowData = table.row($(this).closest('tr')).data();
                    editShift(rowData);
                });

                tableBodySelector.on('click', '.deleteButton', function() {
                    let rowData = table.row($(this).closest('tr')).data();
                    let route = '{{ route('shift.destroy', ':id') }}'.replace(':id', rowData.id);
                    deleteAjax(route, rowData.id, table);
                });
            });
        });
    </script>
@stop
