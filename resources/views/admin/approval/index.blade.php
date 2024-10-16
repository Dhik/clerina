@extends('adminlte::page')

@section('title', 'Approvals')

@section('content_header')
    <h1>Approvals</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addApprovalModal">
                    <i class="fas fa-plus"></i> Add Approval
                    </button>
                </div>
                <div class="card-body">
                    <table id="approvalTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Photo</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.approval.modals.add_approval_modal')
    @include('admin.approval.modals.edit_approval_modal')
    @include('admin.approval.modals.view_approval_modal')
@stop

@section('js')
<script>
    $(document).ready(function() {
        var table = $('#approvalTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('approval.data') }}',
            columns: [
                { data: 'id', name: 'id', visible: false },
                { data: 'name', name: 'name' },
                { data: 'photo', name: 'photo', 
                  render: function(data, type, full, meta) {
                    return data ? '<img src="/storage/' + data + '" height="50"/>' : 'No Image';
                  }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']]
        });

        $('#approvalTable').on('click', '.editButton', function() {
            var id = $(this).data('id');

            $.ajax({
                url: '{{ route('approval.edit', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    $('#editApprovalForm').attr('action', '{{ route('approval.update', ':id') }}'.replace(':id', id));
                    
                    $('#edit_name').val(response.name);
                    $('#edit_photo_preview').attr('src', '/storage/' + response.photo);

                    $('#editApprovalModal').modal('show');
                },
                error: function(response) {
                    console.error('Error fetching approval data:', response);
                }
            });
        });

        $('#approvalTable').on('click', '.viewButton', function() {
            var approvalId = $(this).data('id');
            $.ajax({
                url: "{{ route('approval.show', ':id') }}".replace(':id', approvalId),
                method: 'GET',
                success: function(response) {
                    $('#view_name').val(response.name);
                    $('#view_photo').attr('src', '/storage/' + response.photo);
                    $('#view_created_at').val(response.created_at);
                    $('#view_updated_at').val(response.updated_at);

                    $('#viewApprovalModal').modal('show');
                },
                error: function(response) {
                    console.error('Error fetching approval details:', response);
                }
            });
        });

        $('#addApprovalModal, #editApprovalModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $(this).find('input[name="_method"]').remove();
        });

        $('#approvalTable').on('click', '.deleteButton', function() {
            let rowData = table.row($(this).closest('tr')).data();
            let route = '{{ route('approval.destroy', ':id') }}'.replace(':id', rowData.id);

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
                                'Approval has been deleted.',
                                'success'
                            );
                        },
                        error: function(response) {
                            Swal.fire(
                                'Error!',
                                'There was an error deleting the approval.',
                                'error'
                            );
                            console.error('Error deleting approval:', response);
                        }
                    });
                }
            });
        });
    });
</script>
@stop