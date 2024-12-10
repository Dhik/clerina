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
                        <div class="col-md-3 mb-2">
                            <label>PIC</label>
                            <select id="filterPic" class="form-control">
                                <option value="">Select PIC</option>
                                @foreach($uniquePics as $pic)
                                    <option value="{{ $pic }}">{{ $pic }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>Usernames</label>
                            <select id="filterUsername" class="form-select select2" style="width: 100%;" multiple="multiple">
                                @foreach($uniqueUsernames as $username)
                                    <option value="{{ $username }}">{{ $username }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label>Status Payment</label>
                            <select name="status_payment" id="status_payment" class="form-control" required>
                                <option value="">Select Status</option>
                                <option value="Full Payment">Full Payment</option>
                                <option value="DP 50%">DP 50%</option>
                                <option value="Pelunasan 50%">Pelunasan 50%</option>
                                <option value="Termin 1">Termin 1</option>
                                <option value="Termin 2">Termin 2</option>
                                <option value="Termin 3">Termin 3</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="filterDonePayment">Done Payment</label>
                            <input type="date" id="filterDonePayment" class="form-control">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="filterTanggalPengajuan">Tanggal Pengajuan</label>
                            <input type="date" id="filterTanggalPengajuan" class="form-control">
                        </div>
                        <div class="col-auto mt-4">
                            <button class="btn btn-primary" id="filterButton">
                                <i class="fas fa-search"></i> Apply Filter
                            </button>
                            <button class="btn btn-secondary" id="resetFilterButton">
                                <i class="fas fa-undo"></i> Reset Filter
                            </button>
                        </div>
                        <div class="col-auto mt-4">
                            <button type="button" class="btn btn-outline-primary exportForm" id="exportButton">
                                <i class="fas fa-file-download"></i> {{ trans('labels.export') }} in PDF
                            </button>
                            <button type="button" class="btn btn-outline-success" id="exportExcelButton">
                                <i class="fas fa-file-excel"></i> Export in Excel
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
    @include('admin.talent_payment.modals.view_payment_modal')
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Base Select2 styling */
        .select2-container .select2-selection--multiple {
            min-height: 38px;
        }
        /* Ensure the text is visible */
        .select2-container--default .select2-selection--multiple .select2-search__field {
            color: #495057; /* Text color */
            background-color: #ffffff; /* Background color */
            height: auto; /* Auto-adjust height */
        }


        /* Input text color */
        .select2-container--default .select2-search--inline .select2-search__field {
            color: #495057;
        }

        /* Selected option styling */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border: 1px solid #006fe6;
            color: #fff;
            padding: 2px 8px;
            margin: 4px 4px 0 0;
        }

        /* Remove button styling */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
            margin-right: 5px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #fff;
            opacity: 0.8;
        }

        /* Dropdown styling */
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff;
        }

        /* Input container */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
        }

        /* Focus state */
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('#filterUsername').select2({
            placeholder: "All Usernames",
            allowClear: true,
            multiple: true,
            width: '100%',
        });

        var table = $('#talentPaymentsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('talent_payments.data') }}',
                data: function(d) {
                    d.pic = $('#filterPic').val();
                    d.username = $('#filterUsername').val();
                    d.status_payment = $('#status_payment').val();
                    d.done_payment = $('#filterDonePayment').val();
                    d.tanggal_pengajuan = $('#filterTanggalPengajuan').val();
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

        $('#filterButton').on('click', function() {
            table.ajax.reload();
        });

        $('#resetFilterButton').on('click', function() {
            $('#filterPic').val('').trigger('change');
            $('#filterUsername').val('').trigger('change');
            $('#status_payment').val('').trigger('change');
            $('#filterDonePayment').val('');
            $('#filterTanggalPengajuan').val('');
            table.ajax.reload();
        });

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
                            '_token': '{{ csrf_token() }}'
                        },
                        success: function(response) {
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

        $('#exportButton').on('click', function() {
            var pic = $('#filterPic').val();
            var usernames = $('#filterUsername').val(); 
            var status_payment = $('#status_payment').val();
            var done_payment = $('#filterDonePayment').val();  
            var tanggal_pengajuan = $('#filterTanggalPengajuan').val();

            var queryString = '?pic=' + encodeURIComponent(pic) + 
                            '&status_payment=' + encodeURIComponent(status_payment) +
                            '&done_payment=' + encodeURIComponent(done_payment) + 
                            '&tanggal_pengajuan=' + encodeURIComponent(tanggal_pengajuan);  

            if (usernames && usernames.length > 0) {
                usernames.forEach(function(username) {
                    queryString += '&username[]=' + encodeURIComponent(username);
                });
            }

            window.location.href = '{{ route('talent_payments.pengajuan') }}' + queryString;
        });


        $('#exportExcelButton').on('click', function() {
            var pic = $('#filterPic').val();
            var usernames = $('#filterUsername').val();
            var status_payment = $('#status_payment').val();
            var done_payment = $('#filterDonePayment').val();
            var tanggal_pengajuan = $('#filterTanggalPengajuan').val();
            var queryString = '?pic=' + encodeURIComponent(pic) + 
                      '&status_payment=' + encodeURIComponent(status_payment) +
                      '&done_payment=' + encodeURIComponent(done_payment) + 
                      '&tanggal_pengajuan=' + encodeURIComponent(tanggal_pengajuan);  

            if (usernames && usernames.length > 0) {
                usernames.forEach(function(username) {
                    queryString += '&username[]=' + encodeURIComponent(username);
                });
            }
            window.location.href = '{{ route('talent_payments.export_excel') }}' + queryString;
        });

        $('#talentPaymentsTable').on('click', '.editButton', function() {
            var paymentId = $(this).data('id');
            var row = table.row($(this).closest('tr')).data();
            
            $('#editPaymentId').val(paymentId);
            $('#editUsername').val(row.username);
            $('#editStatusPayment').val(row.status_payment);
            $('#editDonePayment').val(row.done_payment ? moment(row.done_payment).format('YYYY-MM-DD') : '');
            
            $('#editPaymentModal').modal('show');
        });

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

        $('#talentPaymentsTable').on('click', '.viewButton', function() {
            var paymentId = $(this).data('id');
            var row = table.row($(this).closest('tr')).data();
            
            $('#viewId').text(paymentId);
            $('#viewUsername').text(row.username);
            $('#viewTalentName').text(row.talent_name);
            $('#viewNamaRekening').text(row.nama_rekening);
            $('#viewFollowers').text(row.followers);
            $('#viewPic').text(row.pic);
            
            $('#viewStatusPayment').text(row.status_payment);
            $('#viewAmountTf').text('Rp ' + new Intl.NumberFormat('id-ID').format(row.amount_tf)); 
            $('#viewTanggalPengajuan').text(row.tanggal_pengajuan ? 
                moment(row.tanggal_pengajuan).format('DD/MM/YYYY') : '-');
            
            $('#viewDonePayment').text(row.done_payment ? 
                moment(row.done_payment).format('DD/MM/YYYY') : '-');
            
            $('#viewPaymentModal').modal('show');
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
