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
                    <!-- <button type="button" class="btn btn-success" data-toggle="modal" data-target="#importTalentModal">
                        <i class="fas fa-file-download"></i> Import Talent
                    </button> -->
                </div>
                <div class="card-body">
                    <table id="talentTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Talent Name</th>
                                <th>Paid DP</th>
                                <th>Slot Final</th>
                                <th>Rate Final</th>
                                <th>Payment Action</th>
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
    @include('admin.talent.modals.add_payment_modal')
    @include('admin.talent.modals.choose_approval_modal')
@stop

@section('css')
<style>
    .invalid-feedback {
        display: none;
        color: #dc3545;
        font-size: 80%;
    }
</style>
@endsection

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
                {
                    data: 'dp_amount',
                    name: 'dp_amount',
                    render: function(data, type, row) {
                        if (data == null) {
                            return '';
                        }
                        return 'Rp ' + parseFloat(data).toLocaleString('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        });
                    }
                },
                { 
                    data: 'remaining', 
                    name: 'remaining', 
                    orderable: false,
                    render: function(data, type, row) {
                        let values = data.split(" / ");
                        if (values[0] === values[1]) {
                            return '<span style="color: green;">'+data+'</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 'rate_final',
                    name: 'rate_final',
                    render: function(data, type, row) {
                        if (data == null) {
                            return '';
                        }
                        return 'Rp ' + parseFloat(data).toLocaleString('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        });
                    }
                },
                { data: 'payment_action', name: 'payment_action', orderable: false, searchable: false },
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
                    
                    // Populate all fields
                    $('#edit_username').val(response.username);
                    $('#edit_talent_name').val(response.talent_name);
                    $('#edit_video_slot').val(response.video_slot);
                    $('#edit_content_type').val(response.content_type);
                    $('#edit_produk').val(response.produk);
                    $('#edit_pic').val(response.pic);
                    $('#edit_bulan_running').val(response.bulan_running);
                    $('#edit_platform').val(response.platform);
                    $('#edit_niche').val(response.niche);
                    $('#edit_followers').val(response.followers);
                    $('#edit_address').val(response.address);
                    $('#edit_phone_number').val(response.phone_number);
                    $('#edit_bank').val(response.bank);
                    $('#edit_no_rekening').val(response.no_rekening);
                    $('#edit_nama_rekening').val(response.nama_rekening);
                    $('#edit_no_npwp').val(response.no_npwp);
                    $('#edit_tax_percentage').val(response.tax_percentage);
                    $('#edit_pengajuan_transfer_date').val(response.pengajuan_transfer_date);
                    $('#edit_nik').val(response.nik);
                    
                    // Format currency fields
                    $('#edit_price_rate').val(response.price_rate);
                    $('#edit_rate_final').val(response.rate_final);
                    $('#edit_scope_of_work').val(response.scope_of_work);
                    $('#edit_masa_kerjasama').val(response.masa_kerjasama);
                    $('#edit_slot_final').val(response.slot_final);

                    // Show the modal
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
                    // Function to format number as IDR currency
                    function formatRupiah(number) {
                        return new Intl.NumberFormat('id-ID', { 
                            style: 'currency', 
                            currency: 'IDR',
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        }).format(number);
                    }

                    // Directly access the properties from the response
                    $('#view_username').val(response.talent.username);
                    $('#view_talent_name').val(response.talent.talent_name);
                    $('#view_video_slot').val(response.talent.video_slot);
                    $('#view_content_type').val(response.talent.content_type);
                    $('#view_produk').val(response.talent.produk);
                    $('#view_rate_final').val(formatRupiah(response.talent.rate_final));
                    $('#view_pic').val(response.talent.pic);
                    $('#view_bulan_running').val(response.talent.bulan_running);
                    $('#view_platform').val(response.talent.platform);
                    $('#view_niche').val(response.talent.niche);
                    $('#view_followers').val(response.talent.followers);
                    $('#view_address').val(response.talent.address);
                    $('#view_phone_number').val(response.talent.phone_number);
                    $('#view_bank').val(response.talent.bank);
                    $('#view_no_rekening').val(response.talent.no_rekening);
                    $('#view_nama_rekening').val(response.talent.nama_rekening);
                    $('#view_no_npwp').val(response.talent.no_npwp);
                    $('#view_pengajuan_transfer_date').val(response.talent.pengajuan_transfer_date);
                    $('#view_gdrive_ttd_kol_accepting').val(response.talent.gdrive_ttd_kol_accepting);
                    $('#view_nik').val(response.talent.nik);
                    $('#view_price_rate').val(formatRupiah(response.talent.price_rate));
                    $('#view_first_rate_card').val(formatRupiah(response.talent.first_rate_card));
                    $('#view_discount').val(formatRupiah(response.discount)); // Access discount directly
                    $('#view_slot_final').val(response.talent.slot_final);
                    $('#view_tax_deduction').val(formatRupiah(response.talent.tax_deduction));
                    $('#view_created_at').val(response.talent.created_at);
                    $('#view_updated_at').val(response.talent.updated_at);
                    $('#view_scope_of_work').val(response.talent.scope_of_work);
                    $('#view_masa_kerjasama').val(response.talent.masa_kerjasama);
                    $('#view_tax_percentage').val(response.talent.tax_percentage);

                    // Show the modal
                    $('#viewTalentModal').modal('show');
                },
                error: function(response) {
                    console.error('Error fetching talent details:', response);
                }
            });
        });

        $('#talentTable').on('click', '.addPaymentButton', function() {
            var talentId = $(this).data('id');
            $('#paymentTalentId').val(talentId);
            $('#addPaymentModal').modal('show');
        });

        $('#talentTable').on('click', '.exportSPK', function() {
            var id = $(this).data('id');
            window.location.href = '{{ route('talent.spk', ':id') }}'.replace(':id', id);
            window.location.href = exportUrl;
        });

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { 
                style: 'currency', 
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }

        $('#addTalentForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');

            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(),
                success: function(response) {
                    $('#addTalentModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire('Success', 'Talent added successfully', 'success');
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        if (errors.username) {
                            $('#username').addClass('is-invalid');
                            $('#username-error').text(errors.username[0]).show();
                        }
                    } else {
                        Swal.fire('Error', 'Failed to add talent', 'error');
                    }
                }
            });
        });

        // Clear error messages when modal is hidden
        $('#addTalentModal').on('hidden.bs.modal', function () {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').hide();
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

    // Check if there's a success message and show SweetAlert for success after editing talent
        @if(session('success'))
            Swal.fire({
                title: 'Success!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @endif

        // Check if there's an error message and show SweetAlert for error after editing talent
        @if(session('error'))
            Swal.fire({
                title: 'Error!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        @endif
    });

    $(document).ready(function() {
        $('#talentTable').on('click', '.exportData', function() {
            var id = $(this).data('id');
            $('#exportInvoiceId').val(id);
            
            // Load approvals dynamically
            $.ajax({
                url: '{{ route('approval.data') }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">Select an approval</option>';
                    $.each(response.data, function(index, approval) {
                        options += '<option value="' + approval.id + '">' + approval.name + '</option>';
                    });
                    $('#approvalSelect').html(options);
                    $('#chooseApprovalModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error loading approvals:', error);
                    alert('Error loading approvals. Please try again.');
                }
            });
        });

        $('#confirmExport').on('click', function() {
            var id = $('#exportInvoiceId').val();
            var approvalId = $('#approvalSelect').val();
            
            if (!approvalId) {
                alert('Please select an approval.');
                return;
            }
            
            var exportUrl = '{{ route('talent.exportInvoice', ':id') }}'
                .replace(':id', id);
            exportUrl += '?approval=' + approvalId;
            
            window.location.href = exportUrl;
            $('#chooseApprovalModal').modal('hide');
        });
    });

    // Helper function to format currency
    
</script>
@stop
