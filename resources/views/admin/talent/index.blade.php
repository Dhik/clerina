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
                    <i class="fas fa-plus"></i> Add Talent
                    </button>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#importTalentModal">
                    <i class="fas fa-file-download"></i> Import Talent
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
                    // Directly access the properties from the response
                    $('#view_username').val(response.username);
                    $('#view_talent_name').val(response.talent_name);
                    $('#view_video_slot').val(response.video_slot);
                    $('#view_content_type').val(response.content_type);
                    $('#view_produk').val(response.produk);
                    $('#view_rate_final').val(response.rate_final);
                    $('#view_pic').val(response.pic);
                    $('#view_bulan_running').val(response.bulan_running);
                    $('#view_niche').val(response.niche);
                    $('#view_followers').val(response.followers);
                    $('#view_address').val(response.address);
                    $('#view_phone_number').val(response.phone_number);
                    $('#view_bank').val(response.bank);
                    $('#view_no_rekening').val(response.no_rekening);
                    $('#view_nama_rekening').val(response.nama_rekening);
                    $('#view_no_npwp').val(response.no_npwp);
                    $('#view_pengajuan_transfer_date').val(response.pengajuan_transfer_date);
                    $('#view_gdrive_ttd_kol_accepting').val(response.gdrive_ttd_kol_accepting);
                    $('#view_nik').val(response.nik);
                    $('#view_price_rate').val(response.price_rate);
                    $('#view_first_rate_card').val(response.first_rate_card);
                    $('#view_discount').val(response.discount);
                    $('#view_slot_final').val(response.slot_final);
                    $('#view_tax_deduction').val(response.tax_deduction);
                    $('#view_created_at').val(response.created_at);
                    $('#view_updated_at').val(response.updated_at);

                    // Show the modal
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
