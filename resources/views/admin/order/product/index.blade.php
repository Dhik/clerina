@extends('adminlte::page')

@section('title', trans('labels.products'))

@section('content_header')
    <h1>{{ trans('labels.analysis') }} {{ trans('labels.products') }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h4 id="totalSkuCount">0</h4>
                <p>Total SKUs</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-maroon">
            <div class="inner">
                <h4 id="totalSkuCount">0</h4>
                <p>Total Bundling</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">SKU Counts Analysis</h3>
                    </div>
                    <div class="card-body">
                        <div style="width: 100%; height: 400px;">
                            <canvas id="skuChart" width="400" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Last Three Numbers Analysis</h3>
                    </div>
                    <div class="card-body">
                        <div style="width: 100%; height: 400px;">
                            <canvas id="lastThreeNumbersChart" width="400" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Revenue Analysis</h3>
                    </div>
                    <div class="card-body">
                        <div style="width: 100%; height: 400px;">
                            <canvas id="revenueChart" width="400" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $.ajax({
            url: '{{ route("order.getProduct") }}',
            method: 'GET',
            success: function(response) {
                var counts = response.counts;
                var lastThreeNumbersCounts = response.lastThreeNumbersCounts;

                // Update SKU count
                var totalSkuCount = Object.values(counts).reduce((a, b) => a + b, 0);
                $('#totalSkuCount').text(totalSkuCount);

                // Data for SKU chart
                var skuData = {
                    labels: ['XFO', 'RS', 'CLNDLA', 'HYLU'],
                    datasets: [{
                        label: 'SKU Counts',
                        data: [counts.XFO, counts.RS, counts.CLNDLA, counts.HYLU],
                        backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(255, 205, 86, 0.2)', 'rgba(153, 102, 255, 0.2)'],
                        borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)', 'rgba(255, 205, 86, 1)', 'rgba(153, 102, 255, 1)'],
                        borderWidth: 1
                    }]
                };

                // Options for SKU chart
                var skuOptions = {
                    responsive: false,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                };

                // Create SKU chart
                var skuChart = new Chart(document.getElementById('skuChart'), {
                    type: 'bar',
                    data: skuData,
                    options: skuOptions
                });

                // Data for last three numbers chart
                var lastThreeNumbersLabels = Object.keys(lastThreeNumbersCounts);
                var lastThreeNumbersData = {
                    labels: lastThreeNumbersLabels,
                    datasets: [{
                        label: 'Last Three Numbers Counts',
                        data: Object.values(lastThreeNumbersCounts),
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                };

                // Options for last three numbers chart
                var lastThreeNumbersOptions = {
                    responsive: false,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                };

                // Create last three numbers chart
                var lastThreeNumbersChart = new Chart(document.getElementById('lastThreeNumbersChart'), {
                    type: 'bar',
                    data: lastThreeNumbersData,
                    options: lastThreeNumbersOptions
                });

                // Sample data for revenue chart
                var revenueData = {
                    labels: ['Category 1', 'Category 2', 'Category 3'],
                    datasets: [{
                        label: 'Actual Revenue',
                        data: [75, 50, 90],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Target Revenue',
                        data: [100, 100, 100],
                        backgroundColor: 'rgba(255, 205, 86, 0.2)',
                        borderColor: 'rgba(255, 205, 86, 1)',
                        borderWidth: 1
                    }]
                };

                // Options for revenue chart
                var revenueOptions = {
                    responsive: false,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                };

                // Create revenue chart
                var revenueChart = new Chart(document.getElementById('revenueChart'), {
                    type: 'radar',
                    data: revenueData,
                    options: revenueOptions
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }
        });
    });
</script>
@stop
