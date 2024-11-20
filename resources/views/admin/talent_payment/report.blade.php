@extends('adminlte::page')

@section('title', 'Hutang and Piutang')

@section('content_header')
    <h1>Hutang & Piutang Talents</h1>
@stop

@section('content')
    <div class="row">
        <!-- KPI Cards for Totals -->
        <div class="col-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4 id="totalSpent">Rp. 0</h4>
                    <p>Total Spent</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4 id="totalHutang">Rp. 0</h4>
                    <p>Total Hutang</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill"></i>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4 id="totalPiutang">Rp. 0</h4>
                    <p>Total Piutang</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-4">
            <select id="filterUsername" class="form-control select2" style="width: 100%;">
                <option value="">All Usernames</option>
                @foreach($usernames as $username)
                    <option value="{{ $username }}">{{ $username }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" id="filterDonePaymentDate" class="form-control" placeholder="Select Down Payment Date Range">
        </div>
        <!-- <div class="col-md-2">
            <select id="filterStatusPayment" class="form-control select2" style="width: 100%;">
                <option value="">All Payment Status</option>
                <option value="Full Payment">Full Payment</option>
                <option value="DP 50%">DP 50%</option>
                <option value="Pelunasan 50%">Pelunasan 50%</option>
                <option value="Termin 1">Termin 1</option>
                <option value="Termin 2">Termin 2</option>
                <option value="Termin 3">Termin 3</option>
            </select>
        </div> -->
        <button id="resetFilterButton" class="btn btn-secondary ml-4">Reset Filter</button>
        <!-- <a href="{{ route('talent_payments.export') }}" class="btn btn-success ml-4">
            <i class="fas fa-file-excel"></i> Export to Excel
        </a> -->
    </div>

    <!-- Talent Table for Hutang and Piutang -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Talent Financial Details (Hutang & Piutang)</h3>
                </div>
                <div class="card-body">
                    <table id="hutangPiutangTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Total Spent</th>
                                <th>Talent Should Get</th>
                                <th>Hutang</th>
                                <th>Piutang</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Separate Talent Payments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Talent Payments</h3>
                </div>
                <div class="card-body">
                    <table id="talentPaymentsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Status Payment</th>
                                <th>PIC</th>
                                <th>Done Payment</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Spent</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Separate Talent Content Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Talent Content</h3>
                </div>
                <div class="card-body">
                    <table id="talentContentTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Dealing Upload Date</th>
                                <th>Posting Date</th>
                                <th>Status</th>
                                <th>Talent Should Get</th>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(function () {
            // Initialize Select2 for filters
            $('#filterUsername').select2({
                placeholder: "All Usernames",
                allowClear: true,
                width: '100%',
                theme: 'bootstrap4'
            });
            $('#filterStatusPayment').select2({
                placeholder: "All Payment Status",
                allowClear: true,
                width: '100%',
                theme: 'bootstrap4'
            });

            $('#filterDonePaymentDate').daterangepicker({
                locale: { format: 'YYYY-MM-DD' },
                autoUpdateInput: false
            });

            $('#filterDonePaymentDate').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                reloadDataTables();
            });

            $('#filterDonePaymentDate').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                reloadDataTables();
            });

            // Currency formatter for Indonesian Rupiah (Rp.)
            const rupiahFormatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            });

            function fetchTotals() {
                $.ajax({
                    url: "{{ route('talent_payments.hutangTotals') }}",
                    type: "GET",
                    data: { 
                        username: $('#filterUsername').val(),
                        dateRange: $('#filterDonePaymentDate').val(),
                        statusPayment: $('#filterStatusPayment').val(),
                     },
                    
                    success: function(data) {
                        $('#totalSpent').text(rupiahFormatter.format(data.totals.total_spent));
                        $('#totalHutang').text(rupiahFormatter.format(data.totals.total_hutang));
                        $('#totalPiutang').text(rupiahFormatter.format(data.totals.total_piutang));
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching totals:', error);
                    }
                });
            }
            var tableHutangPiutang = $('#hutangPiutangTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('talent_payments.hutangData') }}",
                    type: 'GET',
                    data: function(d) {
                        d.username = $('#filterUsername').val();
                        d.dateRange = $('#filterDonePaymentDate').val();
                        d.status_payment = $('#filterStatusPayment').val(); 
                    }
                },
                columns: [
                    { data: 'username', name: 'Username' },
                    { 
                        data: 'total_spent', 
                        name: 'Total Spent',
                        render: function(data) {
                            return rupiahFormatter.format(data); 
                        }
                    },
                    { 
                        data: 'talent_should_get', 
                        name: 'Should Get',
                        render: function(data) {
                            return rupiahFormatter.format(data); 
                        }
                    },
                    { 
                        data: 'hutang', 
                        name: 'Hutang',
                        render: function(data) {
                            return rupiahFormatter.format(data); 
                        }
                    },
                    { 
                        data: 'piutang', 
                        name: 'Piutang',
                        render: function(data) {
                            return rupiahFormatter.format(data); 
                        }
                    }
                ],
                order: [[0, 'asc']],
            });

            var tablePayments = $('#talentPaymentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('talent_payments.paymentReport') }}',
                    data: function(d) {
                        d.username = $('#filterUsername').val();
                        d.dateRange = $('#filterDonePaymentDate').val();
                        d.status_payment = $('#filterStatusPayment').val();
                    }
                },
                columns: [
                    { data: 'username', name: 'talents.username' },
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
                    { 
                        data: 'spent', 
                        name: 'spent', 
                        render: function(data, type, row) {
                            return rupiahFormatter.format(data); 
                        }
                    }
                ],
                order: [[0, 'desc']]
            });

            var tableContent = $('#talentContentTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('talent_content.data') }}',
                    data: function(d) {
                        d.username = $('#filterUsername').val();
                        d.dateRange = $('#filterDonePaymentDate').val(); 
                    }
                },
                columns: [
                    { data: 'id', name: 'id', visible: false },
                    { data: 'username', name: 'talents.username' },
                    {
                        data: 'dealing_upload_date', 
                        name: 'dealing_upload_date',
                        render: function(data) {
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
                        data: 'posting_date', 
                        name: 'posting_date',
                        render: function(data) {
                            if (data) {
                                let date = new Date(data);
                                return ('0' + date.getDate()).slice(-2) + '/' + 
                                       ('0' + (date.getMonth() + 1)).slice(-2) + '/' + 
                                       date.getFullYear();
                            }
                            return '';
                        }
                    },
                    { data: 'deadline', name: 'deadline', orderable: false, searchable: false },
                    { 
                        data: 'talent_should_get', 
                        name: 'talent_should_get', 
                        render: function(data, type, row) {
                            return rupiahFormatter.format(data); 
                        }
                    }
                ],
                order: [[0, 'desc']]
            });

            $('#filterUsername').on('change', function () {
                tableHutangPiutang.ajax.reload();
                tablePayments.ajax.reload();
                tableContent.ajax.reload();
                fetchTotals();
            });

            // Reset filter button functionality for Talent Payments
            $('#resetFilterButton').on('click', function() {
                $('#filterDonePaymentDate').val('').trigger('change');
                $('#filterUsername').val('').trigger('change');
                reloadDataTables();
            });

            function reloadDataTables() {
                tableHutangPiutang.ajax.reload();
                tablePayments.ajax.reload();
                tableContent.ajax.reload();
                fetchTotals();
            }
            fetchTotals();
        });
    </script>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop
