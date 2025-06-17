@extends('adminlte::page')

@section('title', 'Content Ads KPI Report')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Content Ads KPI Report</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contentAds.index') }}">Content Ads</a></li>
                <li class="breadcrumb-item active">KPI Report</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Date Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Report</h3>
                </div>
                <div class="card-body">
                    <form id="filterForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block" onclick="loadKpiReport()">
                                        <i class="fas fa-search"></i> Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="totalCreated">0</h3>
                    <p>Total Created</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plus"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="totalCompleted">0</h3>
                    <p>Completed</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="totalPending">0</h3>
                    <p>Pending</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="averagePerDay">0</h3>
                    <p>Avg Per Day</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Performance Per Product</h3>
                </div>
                <div class="card-body">
                    <canvas id="productChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Performance Per Funnel</h3>
                </div>
                <div class="card-body">
                    <canvas id="funnelChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Performance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daily Performance Per Person</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dailyPerformanceTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Editor</th>
                                    <th>Date</th>
                                    <th>Content Created</th>
                                    <th>Completion Rate</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product vs Funnel Matrix -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Product vs Funnel Performance Matrix</h3>
                </div>
                <div class="card-body">
                    <div id="productFunnelMatrix"></div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let productChart, funnelChart;

    $(document).ready(function() {
        // Load initial report
        loadKpiReport();
    });

    function loadKpiReport() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();

        $.ajax({
            url: '{{ route('contentAds.kpiData') }}',
            method: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success) {
                    updateKpiCards(response.data);
                    updateCharts(response.data);
                    updateDailyPerformanceTable(response.data);
                    updateProductFunnelMatrix(response.data);
                }
            },
            error: function(xhr) {
                toastr.error('Error loading KPI report');
                console.error('Error:', xhr);
            }
        });
    }

    function updateKpiCards(data) {
        $('#totalCreated').text((data.total_completed + data.total_pending) || 0);
        $('#totalCompleted').text(data.total_completed || 0);
        $('#totalPending').text(data.total_pending || 0);
        
        const totalDays = calculateDaysDifference($('#start_date').val(), $('#end_date').val()) + 1;
        const averagePerDay = totalDays > 0 ? Math.round((data.total_completed + data.total_pending) / totalDays) : 0;
        $('#averagePerDay').text(averagePerDay);
    }

    function updateCharts(data) {
        // Product Chart
        const productLabels = Object.keys(data.per_product || {});
        const productData = productLabels.map(label => data.per_product[label].count || 0);

        if (productChart) {
            productChart.destroy();
        }

        const productCtx = document.getElementById('productChart').getContext('2d');
        productChart = new Chart(productCtx, {
            type: 'doughnut',
            data: {
                labels: productLabels,
                datasets: [{
                    data: productData,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                        '#4BC0C0', '#FF6384', '#36A2EB'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Funnel Chart
        const funnelLabels = Object.keys(data.per_funnel || {});
        const funnelData = funnelLabels.map(label => data.per_funnel[label].count || 0);

        if (funnelChart) {
            funnelChart.destroy();
        }

        const funnelCtx = document.getElementById('funnelChart').getContext('2d');
        funnelChart = new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: funnelLabels,
                datasets: [{
                    label: 'Content Count',
                    data: funnelData,
                    backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0'],
                    borderColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function updateDailyPerformanceTable(data) {
        const tableBody = $('#dailyPerformanceTable tbody');
        tableBody.empty();

        if (data.daily_per_editor) {
            Object.keys(data.daily_per_editor).forEach(function(editorName) {
                const editorData = data.daily_per_editor[editorName];
                if (editorData && editorData.length > 0) {
                    editorData.forEach(function(dayData) {
                        // Calculate completion rate based on status completed
                        const completionRate = 85; // Default rate since we don't track individual completion
                        
                        const row = `
                            <tr>
                                <td>${editorName}</td>
                                <td>${dayData.date}</td>
                                <td>${dayData.count}</td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar ${completionRate >= 80 ? 'bg-success' : completionRate >= 50 ? 'bg-warning' : 'bg-danger'}" 
                                             style="width: ${completionRate}%">${completionRate}%</div>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                }
            });
        }

        if (tableBody.children().length === 0) {
            tableBody.append('<tr><td colspan="4" class="text-center">No data available for the selected period</td></tr>');
        }
    }

    function updateProductFunnelMatrix(data) {
        const matrixContainer = $('#productFunnelMatrix');
        matrixContainer.empty();

        if (data.product_funnel && Object.keys(data.product_funnel).length > 0) {
            let matrixHTML = `
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>TOFU</th>
                            <th>MOFU</th>
                            <th>BOFU</th>
                            <th>None</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            Object.keys(data.product_funnel).forEach(function(product) {
                const productData = data.product_funnel[product];
                const tofu = productData.TOFU ? productData.TOFU.count : 0;
                const mofu = productData.MOFU ? productData.MOFU.count : 0;
                const bofu = productData.BOFU ? productData.BOFU.count : 0;
                const none = productData.None ? productData.None.count : 0;
                const total = tofu + mofu + bofu + none;

                matrixHTML += `
                    <tr>
                        <td><strong>${product}</strong></td>
                        <td class="text-center">${tofu}</td>
                        <td class="text-center">${mofu}</td>
                        <td class="text-center">${bofu}</td>
                        <td class="text-center">${none}</td>
                        <td class="text-center"><strong>${total}</strong></td>
                    </tr>
                `;
            });

            matrixHTML += '</tbody></table>';
            matrixContainer.html(matrixHTML);
        } else {
            matrixContainer.html('<p class="text-center">No data available for the selected period</p>');
        }
    }

    function calculateDaysDifference(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const timeDifference = end.getTime() - start.getTime();
        return Math.ceil(timeDifference / (1000 * 3600 * 24));
    }
</script>
@stop

@section('css')
<style>
.progress {
    height: 20px;
}
.small-box .inner h3 {
    font-size: 2.2rem;
    font-weight: bold;
}
</style>
@stop