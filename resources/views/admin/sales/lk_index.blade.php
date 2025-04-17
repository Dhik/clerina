@extends('adminlte::page')

@section('title', "Financial Report")

@section('content_header')
    <h1>Financial Report</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <input type="text" id="filterDates" class="form-control daterange" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary" id="refreshDataBtn">
                                        <i class="fas fa-sync-alt"></i> Refresh Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-4">
                    <div class="small-box bg-gradient-success">
                        <div class="inner">
                            <h4 id="totalGrossRevenue">Rp 0</h4>
                            <p>Total Gross Revenue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="small-box bg-gradient-primary">
                        <div class="inner">
                            <h4 id="totalHpp">Rp 0</h4>
                            <p>Total HPP</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="small-box bg-gradient-info">
                        <div class="inner">
                            <h4 id="totalFeeAdmin">Rp 0</h4>
                            <p>Total Fee Admin</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="laporanKeuanganTable" class="table table-bordered table-striped dataTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Gross Revenue</th>
                                    <th>HPP</th>
                                    <th>Fee Admin</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .dataTables_wrapper {
        overflow-x: auto;
        width: 100%;
    }

    #laporanKeuanganTable {
        width: 100% !important;
        white-space: nowrap;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>
@stop

@section('js')
    <script>
        filterDate = $('#filterDates');
        
        $('.daterange').daterangepicker({
            autoUpdateInput: false,
            autoApply: true,
            alwaysShowCalendars: true,
            locale: {
                cancelLabel: 'Clear',
                format: 'DD/MM/YYYY'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        $('.daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            $(this).trigger('change'); 
        });

        $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $(this).trigger('change'); 
        });
        
        filterDate.change(function () {
            laporanKeuanganTable.draw();
            fetchSummary();
        });

        function refreshData() {
            Swal.fire({
                title: 'Refreshing Data',
                html: 'Starting refresh process...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('lk.refresh') }}", // You need to create this route
                method: 'GET',
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Refreshed Successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Reload the table
                    laporanKeuanganTable.ajax.reload();
                    fetchSummary();
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Refreshing Data',
                        text: 'Please try again later.'
                    });
                    console.error('Error refreshing data:', error);
                }
            });
        }

        $('#refreshDataBtn').click(refreshData);

        let laporanKeuanganTable = $('#laporanKeuanganTable').DataTable({
            scrollX: true,
            responsive: false,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('lk.get') }}",
                data: function (d) {
                    d.filterDates = filterDate.val()
                }
            },
            columns: [
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'gross_revenue',
                    render: function(data) {
                        return '<span class="text-success">Rp ' + Math.round(data || 0).toLocaleString('id-ID') + '</span>';
                    }
                },
                {
                    data: 'hpp',
                    render: function(data) {
                        return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'fee_admin',
                    render: function(data) {
                        return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                    }
                }
            ],
            columnDefs: [
                { "targets": [1,2,3], "className": "text-right" }
            ],
            order: [[0, 'asc']]
        });

        function fetchSummary() {
            const filterDates = document.getElementById('filterDates').value;
            const url = new URL("{{ route('lk.summary') }}"); // You need to create this route
            if (filterDates) {
                url.searchParams.append('filterDates', filterDates);
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalGrossRevenue').textContent = 'Rp ' + Math.round(data.total_gross_revenue || 0).toLocaleString('id-ID');
                    document.getElementById('totalHpp').textContent = 'Rp ' + Math.round(data.total_hpp || 0).toLocaleString('id-ID');
                    document.getElementById('totalFeeAdmin').textContent = 'Rp ' + Math.round(data.total_fee_admin || 0).toLocaleString('id-ID');
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Initial load
        fetchSummary();
    </script>
@stop