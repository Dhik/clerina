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

        $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $(this).trigger('change'); 
        });
        filterDate.change(function () {
            netProfitsTable.draw();
            fetchSummary();
            renderWaterfallChart();
            loadNetProfitsChart();
            loadCorrelationChart();
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
            orderCountTable.ajax.reload();
            fetchSummary();
        });

        function fetchSummary() {
            const filterDates = document.getElementById('filterDates').value;
            const filterChannel = document.getElementById('filterChannel').value;
            const url = new URL("{{ route('sales.get_hpp_summary') }}");
            
            if (filterDates) {
                url.searchParams.append('filterDates', filterDates);
            }
            
            if (filterChannel) {
                url.searchParams.append('filterChannel', filterChannel);
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalSales').textContent = 'Rp ' + Math.round(data.total_sales).toLocaleString('id-ID');
                    document.getElementById('totalHpp').textContent = 'Rp ' + Math.round(data.total_hpp).toLocaleString('id-ID');
                    document.getElementById('totalQty').textContent = Math.round(data.total_qty).toLocaleString('id-ID');
                    document.getElementById('orderCount').textContent = Math.round(data.order_count).toLocaleString('id-ID');
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

        function renderWaterfallChart() {
            const filterDates = document.getElementById('filterDates').value;
            
            $.ajax({
                url: "{{ route('sales.waterfall-data-2') }}",
                type: 'GET',
                data: {
                    filterDates: filterDates
                },
                success: function(salesData) {
                    const chartData = salesData.map(day => ({
                        x: day.date,
                        y: day.net,
                        measure: 'relative',
                        text: (day.net >= 0 ? '+' : '') + day.net.toLocaleString(),
                        textposition: 'outside'
                    }));

                    const data = [{
                        type: 'waterfall',
                        orientation: 'v',
                        x: chartData.map(d => d.x),
                        y: chartData.map(d => d.y),
                        measure: chartData.map(d => d.measure),
                        text: chartData.map(d => d.text),
                        textposition: chartData.map(d => d.textposition),
                        connector: { line: { color: 'rgb(63, 63, 63)' } },
                        increasing: { marker: { color: '#2ecc71' } },
                        decreasing: { marker: { color: '#e74c3c' } }
                    }];

                    const layout = {
                        title: 'Daily Net Profit Margin',
                        xaxis: { title: 'Date', tickangle: -45 },
                        yaxis: { title: 'Amount (Rp)', tickformat: ',d' },
                        autosize: true,
                        height: 600,
                        margin: { l: 80, r: 20, t: 40, b: 120 }
                    };

                    Plotly.newPlot('waterfallChart', data, layout, { responsive: true });
                },
                error: function(error) {
                    console.error('Error:', error);
                }
            });
        }

        renderWaterfallChart();

        let netProfitChart = null;

        function loadNetProfitsChart() {
            const existingChart = Chart.getChart('netProfitsChart');
            if (existingChart) {
                existingChart.destroy();
            }
            
            if (netProfitChart) {
                netProfitChart.destroy();
            }
            const filterDates = document.getElementById('filterDates').value;
            fetch(`{{ route('sales.net_sales_line') }}${filterDates ? `?filterDates=${filterDates}` : ''}`)
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('netProfitsChart').getContext('2d');
                    
                    netProfitChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.map(item => item.date),
                            datasets: [{
                                label: 'Sales',
                                data: data.map(item => item.sales),
                                borderColor: '#4CAF50',
                                tension: 0.1,
                                fill: false
                            }, {
                                label: 'Marketing',
                                data: data.map(item => item.marketing),
                                borderColor: '#2196F3',
                                tension: 0.1,
                                fill: false
                            }, {
                                label: 'HPP',
                                data: data.map(item => item.hpp),
                                borderColor: '#FFC107',
                                tension: 0.1,
                                fill: false
                            }, {
                                label: 'Net Profit',
                                data: data.map(item => item.netProfit),
                                borderColor: '#F44336',
                                tension: 0.1,
                                fill: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': Rp ' + Math.round(context.raw).toLocaleString('id-ID');
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    grid: {
                                        zeroLineColor: '#888',
                                        zeroLineWidth: 1
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return 'Rp ' + Math.round(value).toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error:', error));
        }
        function loadCorrelationChart() {
            const filterDates = document.getElementById('filterDates').value;
            const selectedVariable = document.getElementById('correlationVariable').value;
            
            fetch(`{{ route('net-profit.sales-vs-marketing') }}?variable=${selectedVariable}${filterDates ? `&filterDates=${filterDates}` : ''}`)
                .then(response => response.json())
                .then(result => {
                    if (result.data && result.layout) {
                        Plotly.newPlot('correlationChart', result.data, result.layout, {
                            responsive: true,
                            displayModeBar: true
                        });
                    }

                    if (result.statistics) {
                        document.getElementById('correlationCoefficient').textContent = 
                            (result.statistics.correlation || 0).toFixed(4);
                        document.getElementById('rSquared').textContent = 
                            (result.statistics.r_squared || 0).toFixed(4);
                        document.getElementById('dataPoints').textContent = 
                            result.statistics.data_points || 0;
                    } else {
                        document.getElementById('correlationCoefficient').textContent = '0.0000';
                        document.getElementById('rSquared').textContent = '0.0000';
                        document.getElementById('dataPoints').textContent = '0';
                    }
                })
                .catch(error => {
                    console.error('Error fetching correlation data:', error);

                    document.getElementById('correlationCoefficient').textContent = '0.0000';
                    document.getElementById('rSquared').textContent = '0.0000';
                    document.getElementById('dataPoints').textContent = '0';
                    
                    if (document.getElementById('correlationChart')) {
                        Plotly.purge('correlationChart');
                    }
                });
        }
        loadNetProfitsChart();
        loadCorrelationChart();

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.getAttribute('href') === '#recapChartTab') {
                renderWaterfallChart();
            } else if (e.target.getAttribute('href') === '#netProfitsTab') {
                loadNetProfitsChart();
            } else if (e.target.getAttribute('href') === '#correlationTab') {
                loadCorrelationChart();
            }
        });

        document.getElementById('correlationVariable').addEventListener('change', loadCorrelationChart);
    </script>
@stop
