@extends('adminlte::page')

@section('title', 'Analytics Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Analytics Dashboard</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary">Daily</button>
            <button type="button" class="btn btn-primary">Monthly</button>
            <button type="button" class="btn btn-outline-primary">Yearly</button>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Summary Cards Row -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body p-0">
                        <div class="row m-0">
                            <!-- Status Cards -->
                            <div class="col-md-2 p-3 border-right">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="text-muted mb-0">Completed</p>
                                        <h3 id="completed" class="mb-0">Loading...</h3>
                                    </div>
                                    <i data-lucide="check-circle" class="text-success"></i>
                                </div>
                                <p id="completed_count" class="text-primary mb-0 small">here</p>
                            </div>

                            <div class="col-md-2 p-3 border-right">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="text-muted mb-0">Sent</p>
                                        <h3 id="sent" class="mb-0">Loading...</h3>
                                    </div>
                                    <i data-lucide="send" class="text-info"></i>
                                </div>
                                <p id="sent_count" class="text-primary mb-0 small">here</p>
                            </div>

                            <div class="col-md-2 p-3 border-right">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="text-muted mb-0">Cancelled</p>
                                        <h3 id="cancelled" class="mb-0">Loading...</h3>
                                    </div>
                                    <i data-lucide="x-circle" class="text-danger"></i>
                                </div>
                                <p id="cancelled_count" class="text-primary mb-0 small">here</p>
                            </div>

                            <div class="col-md-2 p-3 border-right">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="text-muted mb-0">Pending</p>
                                        <h3 id="pending" class="mb-0">Loading...</h3>
                                    </div>
                                    <i data-lucide="clock" class="text-warning"></i>
                                </div>
                                <p id="pending_count" class="text-primary mb-0 small">here</p>
                            </div>

                            <div class="col-md-2 p-3 border-right">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="text-muted mb-0">Sent Booking</p>
                                        <h3 id="sent_booking" class="mb-0">Loading...</h3>
                                    </div>
                                    <i data-lucide="book-open" class="text-primary"></i>
                                </div>
                                <p id="sent_booking_count" class="text-primary mb-0 small">here</p>
                            </div>

                            <div class="col-md-2 p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="text-muted mb-0">Process</p>
                                        <h3 id="process" class="mb-0">Loading...</h3>
                                    </div>
                                    <i data-lucide="activity" class="text-info"></i>
                                </div>
                                <p id="process_count" class="text-primary mb-0 small">here</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Charts Row -->
        <div class="row mb-4">
            <!-- Revenue per Sales Channel -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Revenue by Channel</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="donutChart1"></canvas>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue Trend -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Monthly Revenue Trend</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="lineChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ad Spent Charts Row -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <!-- Date Range Filter -->
                                    <div class="col-auto">
                                        <input type="text" class="form-control rangeDate" id="filterDates" 
                                            placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                                    </div>
                                    
                                    <!-- Multiple Select Filters -->
                                    <div class="col-md-3">
                                        <select class="form-select select2-multiple" id="socialMediaFilter" multiple="multiple">
                                            <option value="">{{ trans('labels.all') }}</option>
                                            @foreach($socialMedia as $platform)
                                                <option value="{{ $platform->id }}">{{ $platform->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <select class="form-select select2-multiple" id="marketplaceFilter" multiple="multiple">
                                            <option value="">{{ trans('labels.all') }}</option>
                                            @foreach($salesChannels as $salesChannel)
                                                <option value="{{ $salesChannel->id }}">{{ $salesChannel->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Reset Button -->
                                    <div class="col-auto">
                                        <button class="btn btn-secondary" id="resetFilterBtn">
                                            {{ trans('buttons.reset_filter') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Ad Spent per Channel -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Ad Spend by Channel</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="donutChart2"></canvas>
                    </div>
                </div>
            </div>

            <!-- Monthly Ad Spent Trend -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Monthly Ad Spend Trend</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="lineChart2" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h4>Value 1</h4>
                                <p>Description of KPI 1</p>
                                <p class="text-danger" style="font-size: 17px;">17%</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h4>Value 2</h4>
                                <p>Description of KPI 2</p>
                                <p class="text-danger" style="font-size: 17px;">17%</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h4>Value 3</h4>
                                <p>Description of KPI 3</p>
                                <p class="text-danger" style="font-size: 17px;">17%</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h4>Value 4</h4>
                                <p>Description of KPI 4</p>
                                <p class="text-danger" style="font-size: 17px;">17%</p>
                            </div>
                        </div>
                    </div>
                </div> -->

        <!-- <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Heatmap Chart (ApexCharts)</h5>
                    </div>
                    <div class="card-body">
                        <div id="heatmapApexChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Bar Chart</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="barChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
@stop

@section('css')
    
    <style>
        .card {
            border: none;
            border-radius: 0.5rem;
        }
        
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.05);
            padding: 1rem;
        }

        .border-right {
            border-right: 1px solid rgba(0,0,0,.05) !important;
        }

        h3 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .shadow-sm {
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
        }

        [data-lucide] {
            width: 24px;
            height: 24px;
        }

        #donutChart1, #donutChart2 {
            height: 300px !important;
        }

        .btn-group .btn {
            border-radius: 0.25rem;
            padding: 0.375rem 1rem;
        }

        .container-fluid {
            padding: 0 0.5rem;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        lucide.createIcons();
    </script>
    <script>
        const filterDate = $('#filterDates');
        const socialMediaFilter = $('#socialMediaFilter');
        const marketplaceFilter = $('#marketplaceFilter');
        let donutChart = null;
        let lineChart = null;

        // Initialize daterangepicker
        filterDate.daterangepicker({
            autoUpdateInput: false,
            alwaysShowCalendars: true,
            startDate: false,
            endDate: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'DD/MM/YYYY'
            },
            ranges: {
                'All Time': [moment('2000-01-01'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            }
        });

        filterDate.on('apply.daterangepicker', function(ev, picker) {
            if (picker.chosenLabel === 'All Time') {
                $(this).val('');
            } else {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            }
            $(this).trigger('change');
        });

        filterDate.on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $(this).trigger('change');
        });

        $(document).ready(function() {
            // Initialize Select2
            socialMediaFilter.select2({
                placeholder: "Select Social Media",
                allowClear: true,
                closeOnSelect: false,
                width: '100%'
            });

            marketplaceFilter.select2({
                placeholder: "Select Marketplaces",
                allowClear: true,
                closeOnSelect: false,
                width: '100%'
            });
            filterDate.val('');
            filterDate.trigger('change');
            // Filter change handlers
            filterDate.change(function() {
                renderTotalAdSpentDonutChart('donutChart2');
                renderAdSpentLineChart('lineChart2');
            });

            socialMediaFilter.change(function() {
                renderTotalAdSpentDonutChart('donutChart2');
                renderAdSpentLineChart('lineChart2');
            });

            marketplaceFilter.change(function() {
                renderTotalAdSpentDonutChart('donutChart2');
                renderAdSpentLineChart('lineChart2');
            });

            // Reset filter handler
            $('#resetFilterBtn').click(function() {
                filterDate.val('');
                socialMediaFilter.val(null).trigger('change');
                marketplaceFilter.val(null).trigger('change');
                renderTotalAdSpentDonutChart('donutChart2');
                renderAdSpentLineChart('lineChart2');
            });

            function renderTotalAdSpentDonutChart(chartElementId) {
                const params = new URLSearchParams();
                
                // Add date filter if exists
                if (filterDate.val()) {
                    params.append('filterDates', filterDate.val());
                }
                
                // Add social media filter if exists
                const socialMediaIds = socialMediaFilter.val();
                if (socialMediaIds && socialMediaIds.length) {
                    socialMediaIds.forEach(id => params.append('social_media_ids[]', id));
                }

                // Add marketplace filter if exists
                const marketplaceIds = marketplaceFilter.val();
                if (marketplaceIds && marketplaceIds.length) {
                    marketplaceIds.forEach(id => params.append('marketplace_ids[]', id));
                }

                // Fetch filtered data
                fetch(`{{ route('report.donut2') }}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        const donutChartData = {
                            labels: data.labels,
                            datasets: [{
                                label: 'Total Ad Spend',
                                data: data.datasets[0].data,
                                backgroundColor: data.datasets[0].backgroundColor,
                            }]
                        };

                        // Destroy existing chart if it exists
                        if (donutChart) {
                            donutChart.destroy();
                        }

                        // Create new chart
                        const ctx = document.getElementById(chartElementId).getContext('2d');
                        donutChart = new Chart(ctx, {
                            type: 'doughnut',
                            data: donutChartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'right'
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Error fetching ad spend data:', error));
            }

            // Initial render
            renderTotalAdSpentDonutChart('donutChart2');

            function renderAdSpentLineChart(chartElementId) {
                const params = new URLSearchParams();
                
                // Add date filter if exists
                if (filterDate.val()) {
                    params.append('filterDates', filterDate.val());
                }
                
                // Add social media filter if exists
                const socialMediaIds = socialMediaFilter.val();
                if (socialMediaIds && socialMediaIds.length) {
                    socialMediaIds.forEach(id => params.append('social_media_ids[]', id));
                }

                // Add marketplace filter if exists
                const marketplaceIds = marketplaceFilter.val();
                if (marketplaceIds && marketplaceIds.length) {
                    marketplaceIds.forEach(id => params.append('marketplace_ids[]', id));
                }

                // Fetch filtered data
                fetch(`{{ route('report.ads-spent-monthly') }}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        const lineChartData = {
                            labels: data.labels,
                            datasets: data.datasets.map(dataset => ({
                                label: dataset.label,
                                data: dataset.data,
                                borderColor: dataset.borderColor,
                                backgroundColor: dataset.backgroundColor,
                                borderWidth: 2,
                                fill: true,
                                tension: dataset.tension
                            }))
                        };

                        // Destroy existing chart if it exists
                        if (lineChart) {
                            lineChart.destroy();
                        }

                        // Create new chart
                        const ctx = document.getElementById(chartElementId).getContext('2d');
                        lineChart = new Chart(ctx, {
                            type: 'line',
                            data: lineChartData,
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'Rp ' + value.toLocaleString();
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'top'
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Error fetching ad spend data:', error));
            }

            renderAdSpentLineChart('lineChart2');
        });
        // Data for the heatmap
        var options = {
            chart: {
                height: 350,
                type: 'heatmap'
            },
            series: [
                {
                    name: 'TIKTOK',
                    data: [
                        { x: 'INV', y: 10 },
                        { x: 'DLVD', y: 20 },
                        { x: 'RTS', y: 30 },
                        { x: 'DLV', y: 40 },
                        { x: '% SCS', y: 50 }
                    ]
                },
                {
                    name: 'SHOPEE',
                    data: [
                        { x: 'INV', y: 15 },
                        { x: 'DLVD', y: 25 },
                        { x: 'RTS', y: 35 },
                        { x: 'DLV', y: 45 },
                        { x: '% SCS', y: 55 }
                    ]
                },
                {
                    name: 'J&T-REG',
                    data: [
                        { x: 'INV', y: 20 },
                        { x: 'DLVD', y: 30 },
                        { x: 'RTS', y: 40 },
                        { x: 'DLV', y: 50 },
                        { x: '% SCS', y: 60 }
                    ]
                },
                {
                    name: 'NINJA',
                    data: [
                        { x: 'INV', y: 20 },
                        { x: 'DLVD', y: 30 },
                        { x: 'RTS', y: 40 },
                        { x: 'DLV', y: 50 },
                        { x: '% SCS', y: 60 }
                    ]
                },
                {
                    name: 'LAZADA',
                    data: [
                        { x: 'INV', y: 15 },
                        { x: 'DLVD', y: 25 },
                        { x: 'RTS', y: 35 },
                        { x: 'DLV', y: 45 },
                        { x: '% SCS', y: 55 }
                    ]
                },
            ],
            dataLabels: {
                enabled: true,  // Enable data labels inside each box
                style: {
                    fontSize: '12px',
                    colors: ['#fff']
                }
            },
            stroke: {
                width: 0
            },
            title: {
                text: 'Product Performance'
            },
            xaxis: {
                type: 'category'
            },
            yaxis: {
                min: 0
            },
            colors: ['#008FFB', '#00E396', '#FEB019'],  // Custom colors for different products
            tooltip: {
                enabled: true,
                shared: true,
                x: {
                    show: true
                },
                y: {
                    formatter: function(value) {
                        return value + ' units';
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#heatmapApexChart"), options);
        chart.render();

        function renderSalesChannelLineChart(chartElementId) {
            fetch('{{ route('report.sales-channel-monthly') }}')
                .then(response => response.json())
                .then(data => {
                    const lineChartData = {
                        labels: data.labels,
                        datasets: data.datasets.map(dataset => ({
                            label: dataset.label,
                            data: dataset.data,
                            borderColor: dataset.borderColor,
                            backgroundColor: dataset.backgroundColor,
                            borderWidth: 2,
                            fill: true,
                            tension: dataset.tension
                        }))
                    };
                    const lineChart = document.getElementById(chartElementId).getContext('2d');
                    new Chart(lineChart, {
                        type: 'line',
                        data: lineChartData,
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'Rp ' + value.toLocaleString();
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching sales channel data:', error));
        }
        renderSalesChannelLineChart('lineChart');


        function updateKpiCardValues() {
            fetch('{{ route('report.kpi-status') }}') // Replace with your actual route
                .then(response => response.json())
                .then(data => {
                    // Initialize an object to hold the values for each status
                    const statusData = {
                        'cancelled': { amount: 0, count: 0 },
                        'completed': { amount: 0, count: 0 },
                        'process': { amount: 0, count: 0 },
                        'sent': { amount: 0, count: 0 },
                        'sent_booking': { amount: 0, count: 0 },
                        'pending': { amount: 0, count: 0 }, // Assuming this status will not be present in the response but might be needed
                    };

                    // Loop through the response data and assign total_amount and total_count to the respective status
                    data.forEach(item => {
                        if (statusData.hasOwnProperty(item.status)) {
                            statusData[item.status].amount = item.total_amount;
                            statusData[item.status].count = item.total_count;
                        }
                    });

                    // Update the values in each card for both amount and count
                    document.getElementById('completed').textContent = formatNumber(statusData.completed.amount);
                    document.getElementById('completed_count').textContent = `Order Count: ${statusData.completed.count}`;
                    
                    document.getElementById('sent').textContent = formatNumber(statusData.sent.amount);
                    document.getElementById('sent_count').textContent = `Order Count: ${statusData.sent.count}`;
                    
                    document.getElementById('cancelled').textContent = formatNumber(statusData.cancelled.amount);
                    document.getElementById('cancelled_count').textContent = `Order Count: ${statusData.cancelled.count}`;
                    
                    document.getElementById('pending').textContent = formatNumber(statusData.pending.amount);
                    document.getElementById('pending_count').textContent = `Order Count: ${statusData.pending.count}`;
                    
                    document.getElementById('sent_booking').textContent = formatNumber(statusData.sent_booking.amount);
                    document.getElementById('sent_booking_count').textContent = `Order Count: ${statusData.sent_booking.count}`;
                    
                    document.getElementById('process').textContent = formatNumber(statusData.process.amount);
                    document.getElementById('process_count').textContent = `Order Count: ${statusData.process.count}`;
                })
                .catch(error => console.error('Error fetching order data:', error));
        }

        // Format number with commas for better readability
        function formatNumber(number) {
            return Number(number).toLocaleString(); // Ensure the number is correctly formatted (e.g., 27,635)
        }

        // Call the function to load data
        updateKpiCardValues();


        // Bar Chart
        const barChartData = {
            labels: ['January', 'February', 'March', 'April', 'May', 'June'],
            datasets: [
                {
                    label: 'Talent Growth',
                    data: [65, 59, 80, 81, 56, 55],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ]
        };

        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: barChartData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function renderSalesChannelDonutChart(chartElementId) {
            fetch('{{ route('report.donut1') }}')
                .then(response => response.json())
                .then(data => {
                    const donutChartData = {
                        labels: data.labels,
                        datasets: [{
                            label: 'Sales Channel Revenue',
                            data: data.datasets[0].data,
                            backgroundColor: data.datasets[0].backgroundColor,  // Using the passed color mapping
                            hoverBackgroundColor: data.datasets[0].backgroundColor,  // Optional: color on hover
                            borderWidth: 0  // Optional: remove border between segments
                        }]
                    };

                    const donutChart = document.getElementById(chartElementId).getContext('2d');
                    
                    new Chart(donutChart, {
                        type: 'doughnut',
                        data: donutChartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right'
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching sales channel data:', error));
        }

        renderSalesChannelDonutChart('donutChart1');


        
    </script>
@stop
