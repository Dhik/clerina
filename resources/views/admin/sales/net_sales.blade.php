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
                                    <input type="month" class="form-control" id="filterMonth" placeholder="{{ trans('placeholder.select_month') }}" autocomplete="off">
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
                    <table id="netProfitsTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Sales</th>
                                <th>Marketing</th>
                                <th>KOL</th>
                                <th>Affiliate</th>
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

    <!-- Omset Modal -->
    <div class="modal fade" id="omsetModal" tabindex="-1" role="dialog" aria-labelledby="omsetModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 80%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="omsetModalLabel">{{ trans('labels.turnover') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('buttons.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="orderTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="order-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.order_id') }}</th>
                                <th>{{ trans('labels.customer_name') }}</th>
                                <th>{{ trans('labels.customer_phone_number') }}</th>
                                <th>{{ trans('labels.product') }}</th>
                                <th>{{ trans('labels.qty') }}</th>
                                <th>{{ trans('labels.amount') }}</th>
                                <th>{{ trans('labels.payment_method') }}</th>
                                <th>{{ trans('labels.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Order data will be dynamically populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Spent Modal -->
    <div class="modal fade" id="detailSpentModal" tabindex="-1" role="dialog" aria-labelledby="detailSpentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 40%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailSpentModalLabel">Detail Spent</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="modalCampaignExpense">Campaign Expense: 0</p>
                    <p id="modalAdsSpentTotal">Total Ads Spent: 0</p>
                    <p id="modalTotalSpent">Total Spent: 0</p>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="detailSalesModal" tabindex="-1" role="dialog" aria-labelledby="detailSalesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 60%;">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold">Sales Status Distribution</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- Pie Chart Section -->
                <div class="row mb-4">
                    <div class="col-lg-7">
                        <div style="width: 100%; height: 400px;">
                            <canvas id="salesPieChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="font-weight-bold">Status</th>
                                        <th class="text-right font-weight-bold">Amount (Rp)</th>
                                        <th class="text-right font-weight-bold">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody id="salesDetailTable">
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td>Total</td>
                                        <td class="text-right" id="totalAmount">0</td>
                                        <td class="text-right">100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Line Chart Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="font-weight-bold mb-3">Daily Status Trend</h6>
                        <div style="width: 100%; height: 400px;">
                            <canvas id="salesTrendChart"></canvas>
                        </div>
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
</style>
@stop

@section('js')
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script>
        filterDate = $('#filterDates');
        filterDate.datepicker({
            format: "mm/yyyy",
            startView: "months", 
            minViewMode: "months",
            autoclose: true
        });
        filterChannel = $('#filterChannel');

        $('#resetFilterBtn').click(function () {
            filterDate.val('')
            filterChannel.val('')
            netProfitsTable.draw()
        })

        filterDate.change(function () {
            netProfitsTable.draw();
            renderWaterfallChart();
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
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('sales.get_net_sales') }}",
                data: function (d) {
                    d.filterDates = filterDate.val()
                }
            },
            columns: [
                {data: 'date', name: 'date'},
                {
                    data: 'sales',
                    render: function(data) {
                        return 'Rp ' + Math.round(data).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'marketing',
                    render: function(data) {
                        return 'Rp ' + Math.round(data).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'spent_kol',
                    render: function(data, type, row) {
                        return '<a href="#" onclick="showKolDetail(\'' + row.date + '\')" class="text-primary">' + 
                            'Rp ' + Math.round(data).toLocaleString('id-ID') + '</a>';
                    }
                },
                {
                    data: 'affiliate',
                    render: function(data) {
                        return 'Rp ' + Math.round(data || 0).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'operasional',
                    render: function(data) {
                        return 'Rp ' + Math.round(data).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'hpp',
                    render: function(data, type, row) {
                        return '<a href="#" onclick="showHppDetail(\'' + row.date + '\')" class="text-primary">' + 
                            'Rp ' + Math.round(data).toLocaleString('id-ID') + '</a>';
                    }
                },
                {
                    data: 'net_profit',
                    render: function(data) {
                        return 'Rp ' + Math.round(data).toLocaleString('id-ID');
                    }
                }
            ],
            columnDefs: [
                { "targets": [1,2,3,4,5,6,7], "className": "text-right" }
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
                data: {
                    filterDates: filterDate.val()
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

            document.addEventListener('DOMContentLoaded', renderWaterfallChart);

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.getAttribute('href') === '#recapChartTab') {
                renderWaterfallChart();
            }
        });
    </script>
@stop
