@extends('adminlte::page')

@section('title', 'Locations')

@section('content_header')
    <h1>Locations</h1>
@stop

@section('content')
    <a href="{{ route('locations.create') }}" class="btn btn-success mb-3">Create Location</a>
    <table class="table table-bordered table-striped" id="locationsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Setting Name</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>
@stop

@section('js')
    <script>
        $(function() {
            $('#locationsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("locations.index") }}',
                columns: [
                    { data: 'id' },
                    { data: 'setting_name' },
                    { data: 'lat' },
                    { data: 'long' },
                    { data: 'actions', orderable: false, searchable: false }
                ]
            });

            $('body').on('click', '.deleteLocation', function() {
                var id = $(this).data('id');
                if (confirm("Are you sure you want to delete this location?")) {
                    $.ajax({
                        type: "DELETE",
                        url: '{{ url("locations") }}/' + id,
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $('#locationsTable').DataTable().ajax.reload();
                            alert(response.success);
                        }
                    });
                }
            });
        });
    </script>
@stop
