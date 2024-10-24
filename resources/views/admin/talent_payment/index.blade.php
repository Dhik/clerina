@extends('adminlte::page')

@section('title', 'Talent Payments')

@section('content_header')
    <h1>Talent Payments</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Filter Dropdowns -->
                    <div class="row mb-3">
                        <div class="col-auto">
                            <select id="filterPic" class="form-control">
                                <option value="">Select PIC</option>
                                @foreach($uniquePics as $pic)
                                    <option value="{{ $pic }}">{{ $pic }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <select id="filterUsername" class="form-select select2" style="width: 100%;">
                                <option value="">All Usernames</option>
                                @foreach($uniqueUsernames as $username)
                                    <option value="{{ $username }}">{{ $username }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" id="filterButton">
                                <i class="fas fa-search"></i> Apply Filter
                            </button>
                            <button class="btn btn-secondary" id="resetFilterButton">
                                <i class="fas fa-undo"></i> Reset Filter
                            </button>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-primary exportForm" id="exportButton">
                                <i class="fas fa-file-download"></i> {{ trans('labels.export') }} Form Pengajuan
                            </button>
                        </div>
                    </div>

                    <table id="talentPaymentsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Talent Name</th>
                                <th>Nama Rekening</th>
                                <th>Status Payment</th>
                                <th>PIC</th>
                                <th>Done Payment</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.talent_payment.modals.edit_payment_modal')
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('#filterUsername').select2({
            placeholder: "All Usernames",
            allowClear: true,
            width: '100%',
            theme: 'bootstrap4'
        });

        var table = $('#talentPaymentsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('talent_payments.data') }}',
                data: function(d) {
                    d.pic = $('#filterPic').val();
                    d.username = $('#filterUsername').val();
                }
            },
            columns: [
                { data: 'username', name: 'talents.username' },
                { data: 'talent_name', name: 'talents.talent_name' },
                { data: 'nama_rekening', name: 'talents.nama_rekening' },
                { 
                    data: 'status_payment', 
                    name: 'talent_payments.status_payment',
                    render: function(data, type, row) {
                        if (data === "50%") {
                            return '<span style="color: orange;">' + data + '</span>';
                        } else if (data === "Pelunasan") {
                            return '<span style="color: green;">' + data + '</span>';
                        }
                        return data;
                    }
                },
                { data: 'pic', name: 'talents.pic' },
                {
                    data: 'done_payment', 
                    name: 'talent_payments.done_payment',
                    render: function(data, type, row) {
                        if (data) {
                            let date = new Date(data);
                            return ('0' + date.getDate()).slice(-2) + '/' + 
                                   ('0' + (date.getMonth() + 1)).slice(-2) + '/' + 
                                   date.getFullYear();
                        }
                        return '';
                    }
                },
                {
                    data: 'tanggal_pengajuan', 
                    name: 'talent_payments.tanggal_pengajuan',
                    render: function(data, type, row) {
                        if (data) {
                            let date = new Date(data);
                            return ('0' + date.getDate()).slice(-2) + '/' + 
                                   ('0' + (date.getMonth() + 1)).slice(-2) + '/' + 
                                   date.getFullYear();
                        }
                        return '';
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']]
        });

        // Handle filter button click
        $('#filterButton').on('click', function() {
            table.ajax.reload();
        });

        // Handle reset filter button click
        $('#resetFilterButton').on('click', function() {
            $('#filterPic').val('').trigger('change');
            $('#filterUsername').val('').trigger('change');
            table.ajax.reload();
        });

        // Handle delete button click
        $('#talentPaymentsTable').on('click', '.deleteButton', function() {
            var paymentId = $(this).data('id');
            var route = '{{ route('talent_payments.destroy', ':id') }}'.replace(':id', paymentId);

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
                            '_token': '{{ csrf_token() }}' // Include CSRF token for security
                        },
                        success: function(response) {
                            // Reload the DataTable to reflect the changes
                            table.ajax.reload();
                            Swal.fire(
                                'Deleted!',
                                'Payment has been deleted.',
                                'success'
                            );
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'There was an error deleting the payment.',
                                'error'
                            );
                            console.error('Error deleting payment:', xhr);
                        }
                    });
                }
            });
        });

        // Handle export button click
        $('#exportButton').on('click', function() {
            var pic = $('#filterPic').val();
            var username = $('#filterUsername').val();
            window.location.href = '{{ route('talent_payments.pengajuan') }}?pic=' + pic + '&username=' + username;
        });

        // Handle edit button click
        $('#talentPaymentsTable').on('click', '.editButton', function() {
            var paymentId = $(this).data('id');
            var row = table.row($(this).closest('tr')).data();
            
            $('#editPaymentId').val(paymentId);
            $('#editUsername').val(row.username);
            $('#editStatusPayment').val(row.status_payment);
            $('#editDonePayment').val(row.done_payment ? moment(row.done_payment).format('YYYY-MM-DD') : '');
            
            $('#editPaymentModal').modal('show');
        });

        // Handle edit form submission
        $('#editPaymentForm').on('submit', function(e) {
            e.preventDefault();
            var paymentId = $('#editPaymentId').val();
            var formData = $(this).serialize();
            
            $.ajax({
                url: '{{ route('talent_payments.update', ':id') }}'.replace(':id', paymentId),
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#editPaymentModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire('Success', 'Payment updated successfully', 'success');
                },
                error: function(xhr) {
                    Swal.fire('Error', 'There was an error updating the payment', 'error');
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px;
            line-height: 38px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
    </style>
@stop
