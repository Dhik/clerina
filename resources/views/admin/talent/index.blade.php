@extends('adminlte::page')

@section('title', 'Talents')

@section('content_header')
    <h1>Talents</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTalentModal">
                        Add Talent
                    </button>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#importTalentModal">
                        Import Talent
                    </button>
                </div>
                <div class="card-body">
                    <table id="talentTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Talent Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.talent.modals.add_talent_modal')
    @include('admin.talent.modals.edit_talent_modal')
    @include('admin.talent.modals.view_talent_modal')
    @include('admin.talent.modals.import_talent_modal')
@stop

@section('js')
<script>
    $(document).ready(function() {
        var table = $('#talentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('talent.data') }}',
            columns: [
                { data: 'id', name: 'id', visible: false },
                { data: 'username', name: 'username' },
                { data: 'talent_name', name: 'talent_name' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']]
        });

        $('#talentTable').on('click', '.editButton', function() {
            var id = $(this).data('id');

            $.ajax({
                url: '{{ route('talent.edit', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    $('#editTalentForm').attr('action', '{{ route('talent.update', ':id') }}'.replace(':id', id));
                    $('#edit_username').val(response.talent.username);
                    $('#edit_talent_name').val(response.talent.talent_name);
                    $('#editTalentModal').modal('show');
                },
                error: function(response) {
                    console.error('Error fetching talent data:', response);
                }
            });
        });

        $('#talentTable').on('click', '.viewButton', function() {
            var talentId = $(this).data('id');
            $.ajax({
                url: "{{ route('talent.show', ':id') }}".replace(':id', talentId),
                method: 'GET',
                success: function(response) {
                    $('#view_username').val(response.talent.username);
                    $('#view_talent_name').val(response.talent.talent_name);

                    $('#viewTalentModal').modal('show');
                },
                error: function(response) {
                    console.error('Error fetching talent details:', response);
                }
            });
        });

        $('#addTalentModal, #editTalentModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $(this).find('input[name="_method"]').remove();
        });

        $('#talentTable').on('click', '.deleteButton', function() {
        let rowData = table.row($(this).closest('tr')).data();
        let route = '{{ route('talent.destroy', ':id') }}'.replace(':id', rowData.id);

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
                    url: route,
                    type: 'DELETE',
                    data: {
                        '_token': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        table.ajax.reload();
                        Swal.fire(
                            'Deleted!',
                            'Talent has been deleted.',
                            'success'
                        );
                    },
                    error: function(response) {
                        Swal.fire(
                            'Error!',
                            'There was an error deleting the talent.',
                            'error'
                        );
                        console.error('Error deleting talent:', response);
                    }
                });
            }
        });
    });
    });
</script>
@stop
