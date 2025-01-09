@extends('adminlte::page')

@section('title', 'Main Reports')

@section('content_header')
    <h1>Reports</h1>
@stop

@section('content')
    <div class="mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-2">
                        <div class="card">
                            <div class="card-body">
                                <p>Completed</p>
                                <h3 id="completed">Loading...</h3>
                            </div>
                        </div>
                    </div>

                    <!-- KPI card 2 - Sent -->
                    <div class="col-md-2">
                        <div class="card">
                            <div class="card-body">
                                <p>Sent</p>
                                <h3 id="sent">Loading...</h3>
                            </div>
                        </div>
                    </div>

                    <!-- KPI card 3 - Cancelled -->
                    <div class="col-md-2">
                        <div class="card">
                            <div class="card-body">
                                <p>Cancelled</p>
                                <h3 id="cancelled">Loading...</h3>
                            </div>
                        </div>
                    </div>

                    <!-- KPI card 4 - Pending -->
                    <div class="col-md-2">
                        <div class="card">
                            <div class="card-body">
                                <p>Pending</p>
                                <h3 id="pending">Loading...</h3>
                            </div>
                        </div>
                    </div>

                    <!-- KPI card 5 - Sent Booking -->
                    <div class="col-md-2">
                        <div class="card">
                            <div class="card-body">
                                <p>Sent Booking</p>
                                <h3 id="sent_booking">Loading...</h3>
                            </div>
                        </div>
                    </div>

                    <!-- KPI card 6 - Process -->
                    <div class="col-md-2">
                        <div class="card">
                            <div class="card-body">
                                <p>Process</p>
                                <h3 id="process">Loading...</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Revenue per Sales Channel</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="donutChart1"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Revenue per Sales per Month</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="lineChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Donut Chart Card 1 -->
            

            <!-- Donut Chart Card 2 -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Ads Spent per Channel</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="donutChart2"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Ads Spent per Month</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="lineChart2" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
                    <!-- KPI card 1 -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h4>Value 1</h4>
                                <p>Description of KPI 1</p>
                                <p class="text-danger" style="font-size: 17px;">17%</p>
                            </div>
                        </div>
                    </div>

                    <!-- KPI card 2 -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h4>Value 2</h4>
                                <p>Description of KPI 2</p>
                                <p class="text-danger" style="font-size: 17px;">17%</p>
                            </div>
                        </div>
                    </div>

                    <!-- KPI card 3 -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h4>Value 3</h4>
                                <p>Description of KPI 3</p>
                                <p class="text-danger" style="font-size: 17px;">17%</p>
                            </div>
                        </div>
                    </div>

                    <!-- KPI card 4 -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h4>Value 4</h4>
                                <p>Description of KPI 4</p>
                                <p class="text-danger" style="font-size: 17px;">17%</p>
                            </div>
                        </div>
                    </div>
                </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Heatmap Chart (ApexCharts)</h5>
                    </div>
                    <div class="card-body">
                        <!-- ApexCharts Heatmap -->
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
        </div>
    </div>
@stop

@section('css')
    <style>
        #donutChart1, #donutChart2 {
            height: 300px !important; /* Force the height to 150px */
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
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

        function renderAdSpentLineChart(chartElementId) {
            fetch('{{ route('report.ads-spent-monthly') }}')
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
                                            return 'Rp ' + value.toLocaleString();  // Formatting y-axis as currency
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


        function updateKpiCardValues() {
            fetch('{{ route('report.kpi-status') }}') // Replace with your actual route
                .then(response => response.json())
                .then(data => {
                    // Initialize an object to hold the values for each status
                    const statusData = {
                        'cancelled': 0,
                        'completed': 0,
                        'process': 0,
                        'sent': 0,
                        'sent_booking': 0,
                        'pending': 0, // Assuming this status will not be present in the response but might be needed
                    };

                    // Loop through the response data and assign total_amount to the respective status
                    data.forEach(item => {
                        if (statusData.hasOwnProperty(item.status)) {
                            statusData[item.status] = item.total_amount;
                        }
                    });

                    // Update the values in each card
                    document.getElementById('completed').textContent = formatNumber(statusData.completed);
                    document.getElementById('sent').textContent = formatNumber(statusData.sent);
                    document.getElementById('cancelled').textContent = formatNumber(statusData.cancelled);
                    document.getElementById('pending').textContent = formatNumber(statusData.pending);
                    document.getElementById('sent_booking').textContent = formatNumber(statusData.sent_booking);
                    document.getElementById('process').textContent = formatNumber(statusData.process);
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


        
        function renderTotalAdSpentDonutChart(chartElementId) {
            fetch('{{ route('report.donut2') }}')
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
                .catch(error => console.error('Error fetching ad spend data:', error));
        }
        renderTotalAdSpentDonutChart('donutChart2');
    </script>
@stop
