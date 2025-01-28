@extends('adminlte::page')

@section('title', trans('labels.sales'))

@section('content_header')
    <h1>Net Profit</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-auto">
                                    <input type="text" class="form-control rangeDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary" id="refreshDataBtn">
                                        <i class="fas fa-sync-alt"></i> Refresh Data
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <a href="{{ route('operational-spent.index') }}" class="btn btn-success">
                                        <i class="fas fa-cog"></i> Set Operational Spent
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <button class="btn bg-info" id="importDataBtn">
                                        <i class="fas fa-sync-alt"></i> Import Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="small-box bg-gradient-success">
                        <div class="inner">
                            <h4 id="totalSales">Rp 0</h4>
                            <p>Total Sales</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
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
            </div>

            @include('admin.sales.net-recap-card')

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="netProfitsTable" class="table table-bordered table-striped dataTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Visit</th>
                                    <th>Qty</th>
                                    <th>Order</th>
                                    <th>CR</th>
                                    <th>ROAS</th>
                                    <th>Sales</th>
                                    <th>Marketing</th>
                                    <th>KOL</th>
                                    <th>Affiliate</th>
                                    <th>Social Media Ads</th>
                                    <th>Marketplace Ads</th>
                                    <th>Operational</th>
                                    <th>HPP</th>
                                    <th>Net Profit</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="kolDetailModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">KOL Detail</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Talent</th>
                            <th>Username</th>
                            <th>Platform</th>
                            <th>Followers</th>
                            <th>Product</th>
                            <th>Rate</th>
                            <th>Link</th>
                        </tr>
                    </thead>
                    <tbody id="kolDetailContent"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="hppDetailModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">HPP Detail</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>HPP/Unit</th>
                            <th>Total HPP</th>
                        </tr>
                    </thead>
                    <tbody id="hppDetailContent"></tbody>
                </table>
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
            filterChannel.val('')
            netProfitsTable.draw()
        })

        filterDate.change(function () {
            netProfitsTable.draw();
        });

        function showKolDetail(date) {
            $('#kolDetailModal').modal('show');
            $.get("{{ route('talent-content.getByDate') }}", { date: date }, function(data) {
                let html = '';
                data.forEach(function(item) {
                    html += `<tr>
                        <td>${item.talent_name || '-'}</td>
                        <td>${item.username || '-'}</td>
                        <td>${item.platform || '-'}</td>
                        <td>${item.followers ? item.followers.toLocaleString('id-ID') : '-'}</td>
                        <td>${item.product || '-'}</td>
                        <td>Rp ${Math.round(item.rate).toLocaleString('id-ID')}</td>
                        <td><a href="${item.upload_link}" target="_blank">View</a></td>
                    </tr>`;
                });
                $('#kolDetailContent').html(html);
            });
        }

        filterChannel.change(function () {
            netProfitsTable.draw()
        });

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
                    // Refresh table if needed
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

            Promise.all([
                $.get("{{ route('net-profit.update-spent-kol') }}"),
                $.get("{{ route('net-profit.update-hpp') }}")
            ])
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Data Refreshed!',
                    showConfirmButton: false,
                    timer: 1500
                });
                table.ajax.reload();
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Refresh Failed',
                    text: 'Please try again'
                });
            });
            }

            $('#refreshDataBtn').click(refreshData);

            let netProfitsTable = $('#netProfitsTable').DataTable({
                scrollX: true,
                responsive: false,
                processing: true,
                serverSide: true,
                pageLength: 25,
                dom: 'Bfrtip', // Add buttons to the DOM
                buttons: [
                    {
                        extend: 'colvis',
                        text: 'Show/Hide Columns',
                        className: 'btn btn-secondary',
                        columns: ':not(.noVis)' // Exclude columns with noVis class
                    }
                ],
                ajax: {
                    url: "{{ route('sales.get_net_sales') }}",
                    data: function (d) {
                        d.filterDates = filterDate.val()
                    }
                },
                columns: [
                    {data: 'date', name: 'date', className: 'noVis'}, // Cannot be hidden
                    {
                        data: 'visit',
                        render: function(data) {
                            return Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false
                    },
                    {
                        data: 'qty',
                        render: function(data) {
                            return Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false
                    },
                    {
                        data: 'order_count',
                        render: function(data) {
                            return Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false
                    },
                    {
                        data: 'closing_rate',
                        render: function(data) {
                            const value = parseFloat(data) || 0;
                            return value.toFixed(2) + '%';
                        },
                        visible: false
                    },
                    {
                        data: 'roas',
                        render: function(data) {
                            const value = parseFloat(data) || 0;
                            return value.toFixed(2);
                        },
                        visible: false
                    },
                    {
                        data: 'sales',
                        render: function(data) {
                            return '<span class="text-success">Rp ' + Math.round(data).toLocaleString('id-ID') + '</span>';
                        },
                        className: 'noVis'
                    },
                    {
                        data: 'marketing',
                        render: function(data) {
                            return 'Rp ' + Math.round(data).toLocaleString('id-ID');
                        },
                        className: 'noVis'
                    },
                    {
                        data: 'spent_kol',
                        render: function(data, type, row) {
                            return '<a href="#" onclick="showKolDetail(\'' + row.date + '\')" class="text-primary">' + 
                                'Rp ' + Math.round(data).toLocaleString('id-ID') + '</a>';
                        },
                        className: 'noVis'
                    },
                    {
                        data: 'affiliate',
                        render: function(data) {
                            return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                        },
                        className: 'noVis'
                    },
                    {
                        data: 'ad_spent_social_media',
                        render: function(data) {
                            return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false
                    },
                    {
                        data: 'ad_spent_market_place',
                        render: function(data) {
                            return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                        },
                        visible: false
                    },
                    {
                        data: 'operasional',
                        render: function(data) {
                            return 'Rp ' + Math.round(data).toLocaleString('id-ID');
                        },
                        className: 'noVis'
                    },
                    {
                        data: 'hpp',
                        render: function(data, type, row) {
                            return '<a href="#" onclick="showHppDetail(\'' + row.date + '\')" class="text-primary">' + 
                                'Rp ' + Math.round(data).toLocaleString('id-ID') + '</a>';
                        },
                        className: 'noVis'
                    },
                    {
                        data: 'net_profit',
                        render: function(data) {
                            const isPositive = data >= 0;
                            const arrowIcon = isPositive ? '↑' : '↓';
                            const colorClass = isPositive ? 'text-success' : 'text-danger';
                            return `<div class="${colorClass}">
                                ${arrowIcon} Rp ${Math.round(data).toLocaleString('id-ID')}
                            </div>`;
                        },
                        className: 'noVis'
                    }
                ],
                columnDefs: [
                    { "targets": [1,2,3,4,5,6,7,8,9,10,11,12,13,14], "className": "text-right" }
                ],
                order: [[0, 'desc']]
            });

        function fetchSummary() {
            fetch("{{ route('sales.get_net_sales_summary') }}")
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalSales').textContent = 'Rp ' + Math.round(data.total_sales).toLocaleString('id-ID');
                    document.getElementById('totalHpp').textContent = 'Rp ' + Math.round(data.total_hpp).toLocaleString('id-ID');
                    document.getElementById('totalSpent').textContent = 'Rp ' + Math.round(data.total_spent).toLocaleString('id-ID');
                    document.getElementById('totalNetProfit').textContent = 'Rp ' + Math.round(data.total_net_profit).toLocaleString('id-ID');
                })
                .catch(error => console.error('Error:', error));
        }
        fetchSummary();

        function showOmsetDetail(data) {
            $.ajax({
                url: "{{ route('order.getOrdersByDate') }}?date=" + data.date,
                type: 'GET',
                success: function(response) {
                    let omsetTableBody = $("#omset-table-body");
                    omsetTableBody.empty(); // Clear existing rows

                    if (response.length > 0) {
                        response.forEach(function(item) {
                            let row = `<tr>
                            <td>${item.sales_channel ?? ''}</td>
                            <td>${item.total_amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}</td>
                        </tr>`;
                            omsetTableBody.append(row);
                        });
                    } else {
                        let row = `<tr><td colspan="2" class="text-center">{{ trans('messages.no_data') }}</td></tr>`;
                        omsetTableBody.append(row);
                    }
                    $('#showOmsetModal').modal('show');
                },
                error: function(error) {
                    console.log(error);
                    alert("An error occurred");
                }
            });
        }

        // Click event for the Total Spent card
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

        function renderWaterfallChart() {
            $.ajax({
                url: "{{ route('sales.waterfall-data-2') }}",
                type: 'GET',
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

            document.addEventListener('DOMContentLoaded', renderWaterfallChart);

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.getAttribute('href') === '#recapChartTab') {
                renderWaterfallChart();
            }
        });
    </script>
@stop
