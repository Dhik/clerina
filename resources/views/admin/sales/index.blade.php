@extends('adminlte::page')

@section('title', trans('labels.sales'))

@section('content_header')
    <h1>{{ trans('labels.sales') }}</h1>
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
                                <div class="col-md-4">
                                    <select class="form-control" id="filterChannel">
                                        <option value="" selected>{{ trans('placeholder.select_sales_channel') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($salesChannels as $salesChannel)
                                            <option value={{ $salesChannel->id }}>{{ $salesChannel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
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
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info" id="totalSalesCard" style="cursor: pointer;">
                        <div class="inner">
                            <h4 id="newSalesCount">0</h4>
                            <p>Total Sales</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h4 id="newVisitCount">0</h4>
                            <p>Total Visit</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4 id="newOrderCount">0</h4>
                            <p>Total Order</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger" id="totalSpentCard" style="cursor: pointer;">
                        <div class="inner">
                            <h4 id="newAdSpentCount">0</h4>
                            <p>Total Spent</p>
                            <p id="newCampaignExpense" style="display: none;">Campaign Expense: 0</p>
                            <p id="newAdsSpentTotal" style="display: none;">Total Ads Spent: 0</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-teal">
                        <div class="inner">
                            <h4 id="newRoasCount">0</h4>
                            <p>{{ trans('labels.roas') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-area"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-pink">
                        <div class="inner">
                            <h4 id="newClosingRateCount">0</h4>
                            <p>{{ trans('labels.closing_rate') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-maroon">
                        <div class="inner">
                            <h4 id="newQtyCount">0</h4>
                            <p>Qty</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-orange">
                        <div class="inner">
                            <h4 id="newCPACount">0</h4>
                            <p>CPA</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-area"></i>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.sales.recap-card')
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <div class="btn-group">
                                <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#visitModal" id="btnAddVisit">
                                    <i class="fas fa-plus"></i> {{ trans('labels.visit') }}
                                </button>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#adSpentSocialMediaModal" id="btnAddAdSpentSM">
                                    <i class="fas fa-plus"></i> {{ trans('labels.ad_spent_social_media') }}
                                </button>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#adSpentMarketPlaceModal" id="btnAddAdSpentMP">
                                    <i class="fas fa-plus"></i> {{ trans('labels.ad_spent_market_place') }}
                                </button> -->
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importMetaAdsSpentModal" id="btnImportMetaAdsSpent">
                                    <i class="fas fa-file-upload"></i> Import Meta Ads Spent (.csv)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="salesTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="order-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.date') }}</th>
                            <th>{{ trans('labels.visit') }}</th>
                            <th>{{ trans('labels.qty') }}</th>
                            <th>{{ trans('labels.order') }}</th>
                            <th>{{ trans('labels.closing_rate') }}</th>
                            <th>{{ trans('labels.ad_spent_social_media') }}</th>
                            <th>{{ trans('labels.ad_spent_market_place') }}</th>
                            <th>{{ trans('labels.spend_total') }}</th>
                            <th>{{ trans('labels.roas') }}</th>
                            <th>{{ trans('labels.turnover') }} ({{ trans('labels.rp') }})</th>
                            <th>{{ trans('labels.action') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.visit.modal')
    @include('admin.adSpentSocialMedia.modal')
    @include('admin.adSpentMarketPlace.modal')
    @include('admin.adSpentMarketPlace.adds_meta')
    @include('admin.sales.modal-visitor')
    @include('admin.sales.modal-omset')

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
        salesTableSelector = $('#salesTable');
        filterDate = $('#filterDates');
        filterChannel = $('#filterChannel');

        $('#btnAddVisit').click(function() {
            $('#dateVisit').val(moment().format("DD/MM/YYYY"));
        });

        $('#btnAddAdSpentSM').click(function() {
            $('#dateAdSpentSocialMedia').val(moment().format("DD/MM/YYYY"));
        });

        $('#btnAddAdSpentMP').click(function() {
            $('#dateAdSpentMarketPlace').val(moment().format("DD/MM/YYYY"));
        });

        $('#resetFilterBtn').click(function () {
            filterDate.val('')
            filterChannel.val('')
            updateRecapCount()
            salesTable.draw()
        });

        $('#metaAdsCsvFile').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });

        $('#importMetaAdsSpentForm').on('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            $.ajax({
                url: "{{ route('adSpentSocialMedia.import') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#importMetaAdsSpentModal').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    $('#errorImportMetaAdsSpent').addClass('d-none');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON.message,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        filterDate.change(function () {
            salesTable.draw()
            updateRecapCount()
        });

        filterChannel.change(function () {
            salesTable.draw()
            updateRecapCount()
        });

        function updateRecapCount() {
            $.ajax({
                url: '{{ route('sales.get-sales-recap') }}?filterDates=' + filterDate.val() + '&filterChannel=' + filterChannel.val(),
                method: 'GET',
                success: function(response) {
                    $('#newSalesCount').text(response.total_sales);
                    $('#newVisitCount').text(response.total_visit);
                    $('#newOrderCount').text(response.total_order);
                    $('#newAdSpentCount').text(response.total_ad_spent);
                    $('#newQtyCount').text(response.total_qty);
                    $('#newRoasCount').text(response.total_roas);
                    $('#newClosingRateCount').text(response.closing_rate);
                    $('#newCPACount').text(response.cpa);
                    $('#newCampaignExpense').text(response.campaign_expense);
                    $('#newAdsSpentTotal').text(response.total_ads_spent);
                    generateChart(response);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching new orders count:', error);
                }
            });
        }

        let salesTable = salesTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('sales.get') }}",
                data: function (d) {
                    d.filterDates = filterDate.val()
                }
            },
            columns: [
                {data: 'date', name: 'date'},
                {data: 'visitFormatted', name: 'visit', sortable: false},
                {data: 'qtyFormatted', name: 'qty', sortable: false},
                {data: 'orderFormatted', name: 'order', sortable: false},
                {data: 'closingRateFormatted', name: 'closing_rate', sortable: false},
                {data: 'adSpentSocialMediaFormatted', name: 'ad_spent_social_media', sortable: false},
                {data: 'adSpentMarketPlaceFormatted', name: 'ad_spent_market_place', sortable: false},
                {data: 'totalFormatted', name: 'total_spend', sortable: false},
                {data: 'roasFormatted', name: 'roas', sortable: false},
                {data: 'adSpentTotalFormatted', name: 'total_spend', sortable: false},
                {data: 'actions', sortable: false}
            ],
            columnDefs: [
                { "targets": [1], "className": "text-right" },
                { "targets": [2], "className": "text-right" },
                { "targets": [3], "className": "text-right" },
                { "targets": [4], "className": "text-right" },
                { "targets": [5], "className": "text-right" },
                { "targets": [6], "className": "text-right" },
                { "targets": [7], "className": "text-right" },
                { "targets": [8], "className": "text-center" }
            ],
            order: [[0, 'desc']]
        });

        salesTable.on('draw.dt', function() {
            const tableBodySelector =  $('#salesTable tbody');

            tableBodySelector.on('click', '.visitButtonDetail', function(event) {
                event.preventDefault();
                let rowData = salesTable.row($(this).closest('tr')).data();
                showVisitorDetail(rowData);
            });

            tableBodySelector.on('click', '.omsetButtonDetail', function(event) {
                event.preventDefault();
                let rowData = salesTable.row($(this).closest('tr')).data();
                showOmsetDetail(rowData);
            });

            tableBodySelector.on('click', '.omset-link', function(event) {
                event.preventDefault();
                let date = $(this).data('date');
                showOmsetDetail(date);
            });
        });

        function showVisitorDetail(data) {
            $.ajax({
                url: "{{ route('visit.getByDate') }}?date=" + data.date,
                type: 'GET',
                success: function(response) {
                    let visitTableBody = $("#visit-table-body");
                    visitTableBody.empty();

                    if (response.length > 0) {
                        response.forEach(function(item) {
                            let row = `<tr>
                            <td>${item.sales_channel.name ?? ''}</td>
                            <td>${item.visit_amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}</td>
                        </tr>`;
                            visitTableBody.append(row);
                        });
                    } else {
                        let row = `<tr><td colspan="2" class="text-center">{{ trans('messages.no_data') }}</td></tr>`;
                        visitTableBody.append(row);
                    }

                    $('#showVisitorModal').modal('show');
                },
                error: function(error) {
                    console.log(error);
                    alert("An error occurred");
                }
            });
        }

        function showOmsetDetail(data) {
            $.ajax({
                url: "{{ route('order.getOrdersByDate') }}?date=" + data.date,
                type: 'GET',
                success: function(response) {
                    let omsetTableBody = $("#omset-table-body");
                    omsetTableBody.empty();

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

        $('#totalSpentCard').click(function() {
            const campaignExpense = $('#newCampaignExpense').text().trim();
            const adsSpentTotal = $('#newAdsSpentTotal').text().trim();
            const totalSpent = $('#newAdSpentCount').text().trim();
            console.log(campaignExpense);
            console.log(adsSpentTotal);
            console.log(totalSpent);

            $('#modalCampaignExpense').text('Campaign Expense: ' + campaignExpense);
            $('#modalAdsSpentTotal').text('Total Ads Spent: ' + adsSpentTotal);
            $('#modalTotalSpent').text('Total Spent: ' + totalSpent);

            $('#detailSpentModal').modal('show');
        });

        let salesPieChart = null;

        $('#totalSalesCard').click(function() {
            $('#detailSalesModal').modal('show');
            
            loadPieChart();
            loadTrendChart();
        });

        function loadTrendChart() {
            fetch('{{ route("order.daily-trend") }}')
                .then(response => response.json())
                .then(chartData => {
                    const ctx = document.getElementById('salesTrendChart').getContext('2d');
                    
                    if (salesTrendChart instanceof Chart) {
                        salesTrendChart.destroy();
                    }

                    const processedDatasets = chartData.datasets.map(dataset => ({
                        ...dataset,
                        data: dataset.data.map(point => ({
                            x: new Date(point.x.split(' ').join(' ')),
                            y: parseInt(point.y)
                        })),
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                        fill: true
                    }));
                    
                    salesTrendChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            datasets: processedDatasets
                        },
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
                                    position: 'top',
                                    align: 'start',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: {
                                            size: 11
                                        },
                                        boxWidth: 8
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        title: function(context) {
                                            return new Date(context[0].parsed.x).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'short',
                                                year: 'numeric'
                                            });
                                        },
                                        label: function(context) {
                                            const value = context.parsed.y;
                                            return ` ${context.dataset.label}: Rp ${value.toLocaleString('id-ID')}`;
                                        }
                                    },
                                    padding: 10
                                }
                            },
                            scales: {
                                x: {
                                    type: 'time',
                                    time: {
                                        unit: 'day',
                                        displayFormats: {
                                            day: 'dd MMM'
                                        }
                                    },
                                    ticks: {
                                        source: 'auto',
                                        autoSkip: true,
                                        maxRotation: 0
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        drawBorder: true,
                                        drawOnChartArea: true,
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        },
                                        padding: 10
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading trend chart data:', error);
                });
        }
        function loadPieChart() {
            fetch('{{ route("order.pie-status") }}')
                .then(response => response.json())
                .then(chartData => {
                    const ctx = document.getElementById('salesPieChart').getContext('2d');
                    
                    if (salesPieChart instanceof Chart) {
                        salesPieChart.destroy();
                    }
                    
                    salesPieChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: chartData.data.labels,
                            datasets: [{
                                data: chartData.data.datasets[0].data,
                                backgroundColor: chartData.data.datasets[0].backgroundColor,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'center',
                                    labels: {
                                        padding: 15,
                                        usePointStyle: true,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const value = parseInt(context.raw);
                                            return ` ${context.label}: Rp ${value.toLocaleString('id-ID')}`;
                                        }
                                    }
                                }
                            }
                        }
                    });

                    updateTable(chartData);
                })
                .catch(error => {
                    console.error('Error loading pie chart data:', error);
                });
        }
        function updateTable(chartData) {
            const tableBody = $('#salesDetailTable');
            tableBody.empty();

            const { labels, values, percentages } = chartData.rawData;
            
            labels.forEach((label, index) => {
                const amount = parseInt(values[index]);
                const percentage = percentages[index];
                const row = `
                    <tr>
                        <td>${label}</td>
                        <td class="text-right">${amount ? amount.toLocaleString('id-ID') : '0'}</td>
                        <td class="text-right">${percentage.toFixed(2)}%</td>
                    </tr>
                `;
                tableBody.append(row);
            });

            $('#totalAmount').text(parseInt(chartData.rawData.totalAmount).toLocaleString('id-ID'));
        }        
        
        $('#detailSalesModal').on('hidden.bs.modal', function () {
            if (salesPieChart instanceof Chart) {
                salesPieChart.destroy();
                salesPieChart = null;
            }
            if (salesTrendChart instanceof Chart) {
                salesTrendChart.destroy();
                salesTrendChart = null;
            }
        });

        $(function () {
            salesTable.draw();
            updateRecapCount();
            $('[data-toggle="tooltip"]').tooltip();
        });

        function showLoadingSwal(message) {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        $('#refreshDataBtn').click(function () {
            showLoadingSwal('Refreshing data, please wait...');

            $.ajax({
                url: "{{ route('order.fetch-all') }}",
                method: 'GET',
                success: function(response) {
                    console.log('Orders fetched and saved successfully');

                    $.ajax({
                        url: "{{ route('order.update_turnover') }}", 
                        method: 'GET',
                        success: function(response) {
                            console.log('Sales turnover updated successfully');

                            $.ajax({
                                url: "{{ route('sales.import_ads') }}",
                                method: 'GET',
                                success: function(response) {
                                    $.ajax({
                                        url: "{{ route('sales.update_ads') }}",
                                        method: 'GET',
                                        success: function(response) {
                                            $.ajax({
                                                url: "{{ route('visit.import_cleora') }}",
                                                method: 'GET',
                                                success: function(response) {
                                                    $.ajax({
                                                        url: "{{ route('visit.import_azrina') }}",
                                                        method: 'GET',
                                                        success: function(response) {
                                                            $.ajax({
                                                                url: "{{ route('visit.update') }}",
                                                                method: 'GET',
                                                                success: function(response) {
                                                                    Swal.fire({
                                                                        icon: 'success',
                                                                        title: 'Data refreshed successfully!',
                                                                        text: 'All data has been imported and updated.',
                                                                        timer: 2000,
                                                                        showConfirmButton: false
                                                                    });
                                                                    updateRecapCount();
                                                                    salesTable.draw();
                                                                },
                                                                error: function(xhr, status, error) {
                                                                    Swal.fire({
                                                                        icon: 'error',
                                                                        title: 'Error updating monthly visit data!',
                                                                        text: xhr.responseJSON?.message || 'An error occurred.',
                                                                    });
                                                                }
                                                            });
                                                        },
                                                        error: function(xhr, status, error) {
                                                            Swal.fire({
                                                                icon: 'error',
                                                                title: 'Error importing Azrina data!',
                                                                text: xhr.responseJSON?.message || 'An error occurred.',
                                                            });
                                                        }
                                                    });
                                                },
                                                error: function(xhr, status, error) {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Error importing Cleora data!',
                                                        text: xhr.responseJSON?.message || 'An error occurred.',
                                                    });
                                                }
                                            });
                                        },
                                        error: function(xhr, status, error) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error updating monthly ad spent data!',
                                                text: xhr.responseJSON?.message || 'An error occurred.',
                                            });
                                        }
                                    });
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error importing data from Google Sheets!',
                                        text: xhr.responseJSON?.message || 'An error occurred.',
                                    });
                                }
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error updating sales turnover data!',
                                text: xhr.responseJSON?.message || 'An error occurred.',
                            });
                        }
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error fetching orders!',
                        text: xhr.responseJSON?.message || 'An error occurred.',
                    });
                }
            });
        });
        function renderWaterfallChart() {
            fetch('{{ route('sales.waterfall-data') }}')
                .then(response => response.json())
                .then(salesData => {
                    const chartData = salesData.map(day => ({
                        x: day.date,
                        y: day.is_weekly_total ? day.net : (day.turnover - day.ad_spent),
                        measure: day.is_weekly_total ? 'total' : 'relative',
                        text: ((day.is_weekly_total ? day.net : (day.turnover - day.ad_spent)) >= 0 ? '+' : '') + 
                            (day.is_weekly_total ? day.net : (day.turnover - day.ad_spent)).toLocaleString(),
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
                        connector: { 
                            line: { color: 'rgb(63, 63, 63)' } 
                        },
                        increasing: { 
                            marker: { color: '#2ecc71' }  // Green for positive net
                        },
                        decreasing: { 
                            marker: { color: '#e74c3c' }  // Red for negative net
                        },
                        totals: { 
                            marker: { color: '#3498db' }  // Blue for weekly totals
                        }
                    }];

                    const layout = {
                        title: 'Daily Net Revenue (Omset - Ads Spent)',
                        xaxis: {
                            title: 'Date',
                            tickangle: -45
                        },
                        yaxis: {
                            title: 'Amount (Rp)',
                            tickformat: ',d'
                        },
                        autosize: true,
                        height: 600,
                        margin: { 
                            l: 80, 
                            r: 20, 
                            t: 40, 
                            b: 120 
                        }
                    };

                    Plotly.newPlot('waterfallChart', data, layout, { responsive: true });
                })
                .catch(error => console.error('Error fetching waterfall data:', error));
        }

        document.addEventListener('DOMContentLoaded', function() {
            renderWaterfallChart();
        });

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.getAttribute('href') === '#recapChartTab') {
                renderWaterfallChart();
            }
        });
    </script>

    @include('admin.visit.script')
    @include('admin.adSpentSocialMedia.script')
    @include('admin.adSpentMarketPlace.script')
    @include('admin.sales.script-chart')
@stop
