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
                                <div class="col-auto">
                                    <button class="btn btn-success text-white" id="importAllOrdersBtn">
                                        <i class="fas fa-download"></i> Import All Marketplace Orders
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
#funnelMetrics {
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 4px;
}
.text-muted {
    color: #6c757d;
}
.font-weight-bold {
    font-weight: 600;
}
.ml-2 {
    margin-left: 0.5rem;
}
.mb-2 {
    margin-bottom: 0.5rem;
}
</style>
@stop

@section('js')
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        salesTableSelector = $('#salesTable');
        filterDate = $('#filterDates');
        filterChannel = $('#filterChannel');
        let funnelChart = null;
        let impressionChart = null;

        $('#importAllOrdersBtn').on('click', function() {
                importAllOrders();
            });
            function importAllOrders() {
            Swal.fire({
                title: 'Importing Orders',
                html: 'Starting import process...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const endpoints = [
                { 
                    name: 'Tokopedia', 
                    url: "{{ route('order.import_tokped') }}"
                },
                { 
                    name: 'Shopee (Cleora)', 
                    url: "{{ route('order.cleora_shopee') }}"
                },
                { 
                    name: 'TikTok (Cleora)', 
                    url: "{{ route('order.cleora_tiktok') }}"
                },
                { 
                    name: 'Lazada (Cleora)', 
                    url: "{{ route('order.cleora_lazada') }}"
                },
                { 
                    name: 'Shopee (2)', 
                    url: "{{ route('order.import_shopee2') }}"
                },
                { 
                    name: 'Shopee (3)', 
                    url: "{{ route('order.import_shopee3') }}"
                },
                { 
                    name: 'Shopee (Azrina)', 
                    url: "{{ route('order.azrina_shopee') }}"
                },
                { 
                    name: 'TikTok (Azrina)', 
                    url: "{{ route('order.azrina_tiktok') }}"
                },
                { 
                    name: 'Lazada (Azrina)', 
                    url: "{{ route('order.azrina_lazada') }}"
                },
                { 
                    name: 'Tokopedia (Azrina)', 
                    url: "{{ route('order.azrina_tokped') }}"
                }
            ];

            let completedEndpoints = 0;
            let failedEndpoints = [];
            let currentIndex = 0;

            function processNextEndpoint() {
                if (currentIndex >= endpoints.length) {
                    // All endpoints processed
                    if (failedEndpoints.length > 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Import Completed with Warnings',
                            html: `Completed: ${completedEndpoints}/${endpoints.length}<br>Failed: ${failedEndpoints.join(', ')}`,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Import Completed Successfully!',
                            html: `All ${endpoints.length} marketplaces imported successfully.`,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                    
                    // If you have a table that needs refreshing
                    if (typeof ordersTable !== 'undefined') {
                        ordersTable.draw();
                    }
                    
                    return;
                }

                const endpoint = endpoints[currentIndex];
                Swal.update({
                    html: `Importing from ${endpoint.name}... (${currentIndex + 1}/${endpoints.length})`
                });

                $.ajax({
                    url: endpoint.url,
                    method: 'GET',
                    success: function(response) {
                        completedEndpoints++;
                        currentIndex++;
                        processNextEndpoint();
                    },
                    error: function(xhr, status, error) {
                        failedEndpoints.push(endpoint.name);
                        currentIndex++;
                        console.error(`Failed to import from ${endpoint.name}:`, error);
                        processNextEndpoint();
                    }
                });
            }
            processNextEndpoint();
        }

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
            initFunnelChart()
            fetchImpressionData()
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
        function createLineChart(ctx, label, dates, data) {
            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    tooltips: {
                        enabled: true,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                let label = data.datasets[tooltipItem.datasetIndex].label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                return label;
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(value, index, values) {
                                    if (parseInt(value) >= 1000) {
                                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                    } else {
                                        return value;
                                    }
                                }
                            }
                        }]
                    }
                }
            });
        }
        function initFunnelChart() {
            const filterValue = filterDate.val();
            const url = new URL('{{ route("adSpentSocialMedia.funnel-data") }}');
            if (filterValue) {
                url.searchParams.append('filterDates', filterValue);
            }

            // Destroy existing ApexCharts instance if it exists
            if (funnelChart) {
                funnelChart.destroy();
                funnelChart = null;
            }

            fetch(url)
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        const data = result.data;
                        
                        const options = {
                            chart: {
                                type: 'bar',
                                height: 350,
                                toolbar: {
                                    show: false
                                }
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 4,
                                    horizontal: true,
                                    distributed: true,
                                    dataLabels: {
                                        position: 'bottom'
                                    },
                                }
                            },
                            colors: ['#60A5FA', '#3B82F6', '#2563EB', '#1D4ED8'],
                            dataLabels: {
                                enabled: true,
                                formatter: function(val) {
                                    return val.toLocaleString();
                                },
                                style: {
                                    fontSize: '12px',
                                }
                            },
                            xaxis: {
                                categories: data.map(item => item.name),
                                labels: {
                                    show: true,
                                    style: {
                                        fontSize: '12px'
                                    }
                                }
                            },
                            yaxis: {
                                labels: {
                                    show: true,
                                    style: {
                                        fontSize: '12px'
                                    }
                                }
                            },
                            grid: {
                                yaxis: {
                                    lines: {
                                        show: false
                                    }
                                }
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val.toLocaleString();
                                    }
                                }
                            }
                        };

                        const series = [{
                            name: 'Total',
                            data: data.map(item => item.value)
                        }];

                        // Create new ApexCharts instance
                        funnelChart = new ApexCharts(document.querySelector("#funnelChart"), {
                            ...options,
                            series: series
                        });
                        funnelChart.render();

                        const metricsHtml = data.map((item, index) => `
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>${item.name}</span>
                                <span class="font-weight-bold">
                                    ${item.value.toLocaleString()}
                                    ${index > 0 ? `
                                        <span class="text-muted ml-2">
                                            (${((item.value / data[0].value) * 100).toFixed(2)}%)
                                        </span>
                                    ` : ''}
                                </span>
                            </div>
                        `).join('');

                        document.querySelector('#funnelMetrics').innerHTML = metricsHtml;
                    }
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        }
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

        function fetchImpressionData() {
            const filterValue = filterDate.val();
            const url = new URL('{{ route("adSpentSocialMedia.line-data") }}');
            if (filterValue) {
                url.searchParams.append('filterDates', filterValue);
            }

            // Destroy existing Chart.js instance if it exists
            if (impressionChart) {
                impressionChart.destroy();
                impressionChart = null;
            }

            fetch(url)
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        const impressionData = result.impressions;
                        const impressionDates = impressionData.map(data => data.date);
                        const impressions = impressionData.map(data => data.impressions);

                        const ctxImpression = document.getElementById('impressionChart').getContext('2d');
                        impressionChart = createLineChart(ctxImpression, 'Impressions', impressionDates, impressions);
                    }
                })
                .catch(error => {
                    console.error('Error fetching impression data:', error);
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
            refreshAllData();
        });

        function refreshAllData() {
            Swal.fire({
                title: 'Refreshing Data',
                html: 'Starting refresh process...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const endpoints = [
                { 
                    name: 'Sales Turnover', 
                    url: "{{ route('order.update_turnover') }}"
                },
                { 
                    name: 'Import Ads', 
                    url: "{{ route('sales.import_ads') }}"
                },
                { 
                    name: 'Import Azrina Ads', 
                    url: "{{ route('sales.import_ads_azrina') }}"
                },
                { 
                    name: 'Update Ads', 
                    url: "{{ route('sales.update_ads') }}"
                },
                { 
                    name: 'Update Ads Azrina', 
                    url: "{{ route('sales.update_ads_azrina') }}"
                },
                { 
                    name: 'Import Cleora Visits', 
                    url: "{{ route('visit.import_cleora') }}"
                },
                { 
                    name: 'Import Azrina Visits', 
                    url: "{{ route('visit.import_azrina') }}"
                },
                { 
                    name: 'Update Visits', 
                    url: "{{ route('visit.update') }}"
                },
                { 
                    name: 'Sales Turnover Azrina', 
                    url: "{{ route('order.update_turnover_azrina') }}"
                },
                { 
                    name: 'Import Order B2B Cleora', 
                    url: "{{ route('order.import_cleora_b2b') }}"
                },
                { 
                    name: 'Import Order B2B Azrina', 
                    url: "{{ route('order.import_azrina_b2b') }}"
                },
                { 
                    name: 'Import Cleora CRM 1', 
                    url: "{{ route('order.import_cleora_crm') }}"
                },
                { 
                    name: 'Import Cleora CRM 2', 
                    url: "{{ route('order.import_cleora_crm2') }}"
                },
                { 
                    name: 'Import Cleora CRM 3', 
                    url: "{{ route('order.import_cleora_crm3') }}"
                },
                { 
                    name: 'Import Cleora CRM 4', 
                    url: "{{ route('order.import_cleora_crm4') }}"
                },
            ];

            let completedEndpoints = 0;
            let failedEndpoints = [];
            let currentIndex = 0;

            function processNextEndpoint() {
                if (currentIndex >= endpoints.length) {
                    // All endpoints processed
                    if (failedEndpoints.length > 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Refresh Completed with Warnings',
                            html: `Completed: ${completedEndpoints}/${endpoints.length}<br>Failed: ${failedEndpoints.join(', ')}`,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Refreshed Successfully!',
                            html: `All data has been imported and updated.`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                    
                    // Update UI elements
                    updateRecapCount();
                    salesTable.draw();
                    
                    return;
                }

                const endpoint = endpoints[currentIndex];
                Swal.update({
                    html: `${endpoint.name}... (${currentIndex + 1}/${endpoints.length})`
                });

                $.ajax({
                    url: endpoint.url,
                    method: 'GET',
                    success: function(response) {
                        completedEndpoints++;
                        currentIndex++;
                        processNextEndpoint();
                    },
                    error: function(xhr, status, error) {
                        failedEndpoints.push(endpoint.name);
                        currentIndex++;
                        console.error(`Failed at ${endpoint.name}:`, error);
                        processNextEndpoint();
                    }
                });
            }
            
            processNextEndpoint();
        }

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.getAttribute('href') === '#funnelChartTab') {
                initFunnelChart();
            }
        });

        
    </script>

    @include('admin.visit.script')
    @include('admin.adSpentSocialMedia.script')
    @include('admin.adSpentMarketPlace.script')
    @include('admin.sales.script-chart')
@stop
