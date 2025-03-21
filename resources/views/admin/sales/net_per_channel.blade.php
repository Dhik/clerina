@extends('adminlte::page')

@section('title', 'Daily HPP')

@section('content_header')
    <h1>HPP Daily per Channel</h1>
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
                                <div class="col-md-4">
                                    <select class="form-control" id="filterChannel">
                                        <option value="" selected>{{ trans('placeholder.select_sales_channel') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($salesChannels as $salesChannel)
                                            <option value={{ $salesChannel->id }}>{{ $salesChannel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button id="generateHppBtn" class="btn btn-primary">
                                        <i class="fas fa-sync-alt mr-1"></i> Generate HPP Data
                                    </button>
                                </div>
                                <!-- <div class="col-auto">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="row">
                <div class="col-3">
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
                <div class="col-3">
                    <div class="small-box bg-gradient-info">
                        <div class="inner">
                            <h4 id="totalSpent">Rp 0</h4>
                            <p>Total Spent</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill"></i>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="small-box bg-gradient-teal">
                        <div class="inner">
                            <h4 id="totalNetProfit">Rp 0</h4>
                            <p>Total Net Profit</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div> -->

            <div class="row">
                <div class="col-3">
                    <div class="small-box bg-gradient-primary">
                        <div class="inner">
                            <h4 id="dailyHppTotal">Rp 0</h4>
                            <p>Total HPP</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="small-box bg-gradient-success">
                        <div class="inner">
                            <h4 id="dailyQtyTotal">Rp 0</h4>
                            <p>Total Quantity</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Trend Quantity SKU</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="dailyOrdersChart" width="800" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Quantity per SKU</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChannelChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="hppDetailTable" class="table table-bordered table-striped dataTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Channel</th>
                                    <th>SKU</th>
                                    <th>Quantity</th>
                                    <th>HPP</th>
                                    <th>Total HPP</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adSpentDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adSpentDetailModalTitle">Ads Spent Detail</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="adSpentDetailTable" class="table table-bordered table-striped" width="100%">
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="hppDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hppDetailModalTitle">HPP Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h4 id="hppDetailTotal" class="text-primary">Total HPP: Rp 0</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <h5 id="hppDetailChannel">All Channels</h5>
                    </div>
                </div>
                <table id="hppDetailTable" class="table table-bordered table-striped" width="100%">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>HPP Satuan</th>
                            <th>Total HPP</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <script>
        filterDate = $('#filterDates');
        filterChannel = $('#filterChannel');
        $('#resetFilterBtn').click(function () {
            filterDate.val('')
            netProfitsTable.draw()
        })
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

        $('#generateHppBtn').on('click', function() {
            // Show loading state
            Swal.fire({
                title: 'Processing',
                text: 'Generating HPP data...',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('order.generate_hpp') }}",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Optional: Reload the page or refresh the data
                        // window.location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'An error occurred while generating HPP data';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $(this).trigger('change'); 
        });
        filterDate.change(function () {
            hppDetailTable.draw();
            fetchSummary();
            loadDailyOrdersChart();
            loadSalesChannelChart();
        });
        function showAdSpentDetail(date) {
            // Open modal
            $('#adSpentDetailModal').modal('show');
            $('#adSpentDetailModalTitle').text('Ads Spent Detail - ' + date);
            
            // Clear existing data
            if ($.fn.DataTable.isDataTable('#adSpentDetailTable')) {
                $('#adSpentDetailTable').DataTable().destroy();
            }
            
            // Initialize datatable
            $('#adSpentDetailTable').DataTable({
                processing: true,
                serverSide: false, // We'll load all data at once for simplicity
                ajax: {
                    url: "{{ route('net-profit.get_ad_spent_detail') }}",
                    data: { date: date }
                },
                columns: [
                    { data: 'name', title: 'Channel/Platform' },
                    { 
                        data: 'amount', 
                        title: 'Amount',
                        render: function(data) {
                            return 'Rp ' + Math.round(data).toLocaleString('id-ID');
                        }
                    }
                ],
                columnDefs: [
                    { "targets": [1], "className": "text-right" }
                ]
            });
        }

        $('#importDataBtn').on('click', function() {
            Swal.fire({
                title: 'Importing Data',
                html: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('net-profit.import-data') }}",
                method: 'GET',
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'All data has been imported and updated.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    netProfitsTable.draw();
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Import failed!',
                        text: xhr.responseJSON?.message || 'Something went wrong'
                    });
                }
            });
        });

        function loadDailyOrdersChart() {
            const filterChannel = $('#filterChannel').val();
            const filterDates = $('#filterDates').val();

            fetch(`{{ route("orders.daily-by-sku") }}?filterChannel=${filterChannel}&filterDates=${filterDates}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Chart data received:", data);
                    
                    const ctx = document.getElementById('dailyOrdersChart').getContext('2d');
                    
                    if (window.dailyOrdersChart instanceof Chart) {
                        window.dailyOrdersChart.destroy();
                    }
                    
                    // You don't need to process the dates anymore since they're already in ISO format
                    // in your response: "2025-03-01T00:00:00.000Z"
                    
                    window.dailyOrdersChart = new Chart(ctx, {
                        type: 'line',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'nearest',
                                axis: 'x',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    position: 'right',
                                    align: 'center',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: {
                                            size: 12
                                        },
                                        boxWidth: 8
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Daily Quantity Trend',
                                    font: {
                                        size: 14
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        title: function(context) {
                                            return new Date(context[0].raw.x).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'short',
                                                year: 'numeric'
                                            });
                                        },
                                        label: function(context) {
                                            return `${context.dataset.label}: ${context.raw.y.toLocaleString('id-ID')} items`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    type: 'time',
                                    time: {
                                        unit: 'day',
                                        displayFormats: {
                                            day: 'd MMM'
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        drawBorder: true,
                                        drawOnChartArea: true,
                                    },
                                    ticks: {
                                        stepSize: 500,
                                        callback: function(value) {
                                            return value.toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    console.log("Chart created:", window.dailyOrdersChart);
                })
                .catch(error => {
                    console.error('Error loading daily orders chart:', error);
                });
        }

        function loadSalesChannelChart() {
            const filterChannel = $('#filterChannel').val();
            const filterStatus = $('#filterStatus').val();
            const filterDates = $('#filterDates').val();

            fetch(`{{ route("orders.qty-by-sku") }}?filterChannel=${filterChannel}&filterStatus=${filterStatus}&filterDates=${filterDates}`)
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('salesChannelChart').getContext('2d');
                    
                    if (window.salesChannelChart instanceof Chart) {
                        window.salesChannelChart.destroy();
                    }
                    
                    window.salesChannelChart = new Chart(ctx, {
                        type: 'pie',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    align: 'center',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true,
                                        font: {
                                            size: 12
                                        },
                                        boxWidth: 15
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Quantity by SKU (Top 10)',
                                    font: {
                                        size: 14
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.raw || 0;
                                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                            const percentage = ((value * 100) / total).toFixed(1);
                                            return `${label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error loading chart data:', error));
        }

        loadDailyOrdersChart();
        loadSalesChannelChart();

        function refreshData() {
            Swal.fire({
                title: 'Refreshing Data',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            const endpoints = [
                { 
                    name: 'KOL Spending', 
                    url: "{{ route('net-profit.update-spent-kol') }}"
                },
                { 
                    name: 'HPP', 
                    url: "{{ route('net-profit.update-hpp') }}"
                },
                { 
                    name: 'Marketing', 
                    url: "{{ route('net-profit.update-marketing') }}"
                },
                { 
                    name: 'ROAS', 
                    url: "{{ route('net-profit.update-roas') }}"
                },
                { 
                    name: 'Quantity', 
                    url: "{{ route('net-profit.update-qty') }}"
                },
                { 
                    name: 'Order Count', 
                    url: "{{ route('net-profit.update-order-count') }}"
                },
                { 
                    name: 'Sales', 
                    url: "{{ route('net-profit.update-sales') }}"
                }
            ];

            Promise.all(endpoints.map(endpoint => $.get(endpoint.url)))
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Refreshed!',
                        html: '<small>KOL Spending, Marketing, HPP, ROAS, Quantity, Count Orders</small>',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    table.ajax.reload();
                });
        }

        $('#refreshDataBtn').click(refreshData);

        let hppDetailTable = $('#hppDetailTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('order.get_hpp') }}",
                data: function (d) {
                    d.filterDates = filterDate.val();
                    d.filterChannel = $('#filterChannel').val();
                }
            },
            columns: [
                { data: 'date', name: 'date' },
                { data: 'channel_name', name: 'sales_channels.name' },
                { data: 'sku', name: 'daily_hpp.sku' },
                { 
                    data: 'quantity',
                    name: 'daily_hpp.quantity',
                    className: 'text-right'
                },
                { 
                    data: 'HPP', 
                    name: 'daily_hpp.HPP',
                    className: 'text-right'
                },
                { 
                    data: 'total_hpp',
                    name: 'total_hpp',
                    className: 'text-right'
                }
            ],
            order: [[0, 'desc'], [1, 'asc'], [2, 'asc']]
        });

        $('#filterChannel').on('change', function() {
            hppDetailTable.ajax.reload();
            fetchSummary();
            loadDailyOrdersChart();
            loadSalesChannelChart();
        });

        function fetchSummary() {
            const filterDates = document.getElementById('filterDates').value;
            const filterChannel = document.getElementById('filterChannel').value;
            
            // Fetch daily_hpp-based summary
            const dailyHppUrl = new URL("{{ route('order.get_daily_hpp_summary') }}");
            if (filterDates) {
                dailyHppUrl.searchParams.append('filterDates', filterDates);
            }
            if (filterChannel) {
                dailyHppUrl.searchParams.append('filterChannel', filterChannel);
            }
            
            fetch(dailyHppUrl)
                .then(response => response.json())
                .then(data => {
                    // Daily HPP-based summary
                    document.getElementById('dailyHppTotal').textContent = 'Rp ' + Math.round(data.total_hpp).toLocaleString('id-ID');
                    document.getElementById('dailyQtyTotal').textContent = Math.round(data.total_qty).toLocaleString('id-ID');
                    document.getElementById('skuCount').textContent = Math.round(data.sku_count).toLocaleString('id-ID');
                })
                .catch(error => console.error('Error:', error));
        }

        fetchSummary();

        $('#totalSpentCard').click(function() {
            const campaignExpense = $('#newCampaignExpense').text().trim();
            const adsSpentTotal = $('#newAdsSpentTotal').text().trim();
            const totalSpent = $('#newAdSpentCount').text().trim();
            console.log(campaignExpense);
            console.log(adsSpentTotal);
            console.log(totalSpent);

            // Update modal content
            $('#modalCampaignExpense').text('Campaign Expense: ' + campaignExpense);
            $('#modalAdsSpentTotal').text('Total Ads Spent: ' + adsSpentTotal);
            $('#modalTotalSpent').text('Total Spent: ' + totalSpent);

            // Show the modal
            $('#detailSpentModal').modal('show');
        });

        let salesPieChart = null;

        $('#totalSalesCard').click(function() {
            $('#detailSalesModal').modal('show');
        });

        function showHppDetail(date) {
            $('#hppDetailModal').modal('show');
            $.get("{{ route('net-profit.getHppByDate') }}", { date: date }, function(data) {
                let html = '';
                data.forEach(function(item) {
                    let total = item.quantity * item.harga_satuan;
                    html += `<tr>
                        <td>${item.sku}</td>
                        <td>${item.product}</td>
                        <td class="text-right">${item.quantity.toLocaleString('id-ID')}</td>
                        <td class="text-right">Rp ${Math.round(item.harga_satuan).toLocaleString('id-ID')}</td>
                        <td class="text-right">Rp ${Math.round(total).toLocaleString('id-ID')}</td>
                    </tr>`;
                });
                $('#hppDetailContent').html(html);
            });
        }

        function showLoadingSwal(message) {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        function showHppDetail(date) {
            // Open modal
            $('#hppDetailModal').modal('show');
            $('#hppDetailModalTitle').text('HPP Details - ' + date);
            
            // Get selected channel
            const filterChannel = $('#filterChannel').val();
            const channelName = filterChannel ? $('#filterChannel option:selected').text() : 'All Channels';
            
            // Clear existing data if the table was already initialized
            if ($.fn.DataTable.isDataTable('#hppDetailTable')) {
                $('#hppDetailTable').DataTable().destroy();
            }
            
            // Fetch and display total HPP
            $.ajax({
                url: "{{ route('sales.get_hpp_detail_total') }}",
                data: { 
                    date: date,
                    filterChannel: filterChannel
                },
                success: function(response) {
                    $('#hppDetailTotal').text('Total HPP: Rp ' + Math.round(response.total_hpp).toLocaleString('id-ID'));
                    $('#hppDetailChannel').text(channelName);
                }
            });
            
            // Initialize datatable with HPP details
            $('#hppDetailTable').DataTable({
                processing: true,
                serverSide: false, // Load all data at once for simplicity
                ajax: {
                    url: "{{ route('sales.get_hpp_detail') }}",
                    data: { 
                        date: date,
                        filterChannel: filterChannel
                    }
                },
                columns: [
                    { data: 'sku', name: 'sku' },
                    { data: 'product', name: 'product' },
                    { 
                        data: 'qty', 
                        render: function(data) {
                            return Math.round(data).toLocaleString('id-ID');
                        }
                    },
                    { 
                        data: 'harga_satuan', 
                        render: function(data) {
                            return 'Rp ' + Math.round(data).toLocaleString('id-ID');
                        }
                    },
                    { 
                        data: 'total_hpp', 
                        render: function(data) {
                            return 'Rp ' + Math.round(data).toLocaleString('id-ID');
                        }
                    }
                ],
                columnDefs: [
                    { "targets": [2, 3, 4], "className": "text-right" }
                ]
            });
        }
    </script>
@stop
