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
            
            <!-- Channel Summary Cards -->
            <div class="row" id="channelSummaryCards">
                <!-- Cards will be dynamically added here -->
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="laporanKeuanganTable" class="table table-bordered table-striped dataTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    @foreach($salesChannels as $channel)
                                    <th>{{ $channel->name }}</th>
                                    @endforeach
                                    <th>Total Gross Revenue</th>
                                    <th>Total HPP</th>
                                    <th>Total Fee Admin</th>
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
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<style>
    #salesPieChart {
        height: 400px !important;
        width: 100% !important;
    }
    .modal-content {
        border-radius: 8px;
    }

    .modal-header {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        border-bottom: 1px solid #dee2e6;
    }

    .table th, .table td {
        padding: 12px;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    #salesDetailTable td {
        border-top: 1px solid #dee2e6;
    }

    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }
    .dataTables_wrapper {
        overflow-x: auto;
        width: 100%;
    }

    #netProfitsTable {
        width: 100% !important;
        white-space: nowrap;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .dt-button-collection {
        padding: 8px !important;
    }
    
    .dt-button-collection .dt-button {
        margin: 2px !important;
    }
    
    .dt-button.buttons-columnVisibility {
        display: block;
        padding: 8px;
        margin: 2px;
        text-align: left;
    }
    
    .dt-button.buttons-columnVisibility.active {
        background: #e9ecef;
    }
</style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <script>
        let filterDate = $('#filterDates');
        let salesChannels = @json($salesChannels);
        
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
            laporanKeuanganTable.ajax.reload();
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

        // Define columns dynamically based on sales channels
        let columns = [
            {
                data: 'date',
                name: 'date'
            }
        ];
        
        // Add a column for each sales channel
        salesChannels.forEach(function(channel) {
            columns.push({
                data: 'channel_' + channel.id,
                name: 'channel_' + channel.id,
                render: function(data) {
                    return '<span class="text-primary">Rp ' + formatNumber(data || 0) + '</span>';
                }
            });
        });
        
        // Add total columns
        columns.push(
            {
                data: 'total_gross_revenue',
                name: 'total_gross_revenue',
                render: function(data) {
                    return '<span class="text-success">Rp ' + formatNumber(data || 0) + '</span>';
                }
            },
            {
                data: 'total_hpp',
                name: 'total_hpp',
                render: function(data) {
                    return 'Rp ' + formatNumber(data || 0);
                }
            },
            {
                data: 'total_fee_admin',
                name: 'total_fee_admin',
                render: function(data) {
                    return 'Rp ' + formatNumber(data || 0);
                }
            }
        );

        function formatNumber(num) {
            return Math.round(num).toLocaleString('id-ID');
        }

        let laporanKeuanganTable = $('#laporanKeuanganTable').DataTable({
            scrollX: true,
            responsive: false,
            processing: true,
            serverSide: true,
            pageLength: 25,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'colvis',
                    text: 'Show/Hide Columns',
                    className: 'btn btn-secondary'
                }
            ],
            ajax: {
                url: "{{ route('lk.get') }}",
                data: function (d) {
                    d.filterDates = filterDate.val()
                }
            },
            columns: columns,
            columnDefs: [
                { 
                    "targets": Array.from({length: columns.length - 1}, (_, i) => i + 1), 
                    "className": "text-right" 
                }
            ],
            order: [[0, 'asc']]
        });

        function fetchSummary() {
            const filterDates = document.getElementById('filterDates').value;
            const url = new URL("{{ route('lk.summary') }}");
            if (filterDates) {
                url.searchParams.append('filterDates', filterDates);
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalGrossRevenue').textContent = 'Rp ' + formatNumber(data.total_gross_revenue || 0);
                    document.getElementById('totalHpp').textContent = 'Rp ' + formatNumber(data.total_hpp || 0);
                    document.getElementById('totalFeeAdmin').textContent = 'Rp ' + formatNumber(data.total_fee_admin || 0);
                    
                    // Update channel summary cards
                    updateChannelSummaryCards(data.channel_summary);
                })
                .catch(error => console.error('Error:', error));
        }
        
        function updateChannelSummaryCards(channelSummary) {
            const container = document.getElementById('channelSummaryCards');
            container.innerHTML = ''; // Clear previous cards
            
            // Create a card for each channel
            channelSummary.forEach((channel, index) => {
                // Calculate background color based on index (rotating through a few colors)
                const colorClasses = ['bg-info', 'bg-success', 'bg-warning', 'bg-primary', 'bg-danger'];
                const colorClass = colorClasses[index % colorClasses.length];
                
                const card = `
                <div class="col-md-3 col-sm-6">
                    <div class="small-box ${colorClass} channel-card">
                        <div class="inner">
                            <h5>Rp ${formatNumber(channel.channel_gross_revenue)}</h5>
                            <p>${channel.channel_name}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                </div>
                `;
                
                container.innerHTML += card;
            });
        }
        
        // Initial load
        fetchSummary();
    </script>
@stop