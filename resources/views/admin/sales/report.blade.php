@extends('adminlte::page')

@section('title', 'Talents')

@section('content_header')
    <h1>Reports</h1>
@stop

@section('content')
    <div class="mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <!-- KPI card 1 -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <p>INVOICE</p>
                                <h3>27,635</h3>
                            </div>
                            <div class="card-footer">
                                <!-- Progress Bar -->
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI card 2 -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <p>DITERIMA</p>
                                <h3>27,635</h3>
                            </div>
                            <div class="card-footer">
                                <!-- Progress Bar -->
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI card 3 -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <p>ON PROCESS</p>
                                <h3>27,635</h3>
                            </div>
                            <div class="card-footer">
                                <!-- Progress Bar -->
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI card 4 -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <p>RETURN</p>
                                <h3>27,635</h3>
                            </div>
                            <div class="card-footer">
                                <!-- Progress Bar -->
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Line Chart</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="lineChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Donut Chart Card 1 -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Donut Chart 1</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="donutChart1" width="400"></canvas>
                    </div>
                </div>
            </div>

            <!-- Donut Chart Card 2 -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Donut Chart 2</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="donutChart2" width="400"></canvas>
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
            height: 400px !important; /* Force the height to 150px */
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

        // Render the heatmap chart
        var chart = new ApexCharts(document.querySelector("#heatmapApexChart"), options);
        chart.render();
        const lineChartData = {
            labels: ['January', 'February', 'March', 'April', 'May', 'June'],
            datasets: [
                {
                    label: 'Talent Growth',
                    data: [65, 59, 80, 81, 56, 55],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false
                },
                {
                    label: 'Talent Engagement',
                    data: [45, 75, 65, 55, 60, 85],
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1,
                    fill: false
                },
                {
                    label: 'Talent Retention',
                    data: [45, 60, 50, 70, 80, 65],
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                    fill: false
                }
            ]
        };

        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: lineChartData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

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

        // Donut Chart 1 Data
        const donutChartData1 = {
            labels: ['Active', 'Inactive', 'Pending'],
            datasets: [{
                data: [300, 50, 100],
                backgroundColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 159, 64, 1)', 'rgba(153, 102, 255, 1)']
            }]
        };

        // Donut Chart 2 Data
        const donutChartData2 = {
            labels: ['Completed', 'In Progress', 'Not Started'],
            datasets: [{
                data: [200, 150, 50],
                backgroundColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 159, 64, 1)']
            }]
        };
        // Donut Chart 1
        const donutChart1 = document.getElementById('donutChart1').getContext('2d');
        new Chart(donutChart1, {
            type: 'doughnut',
            data: {
                labels: ['Red', 'Blue', 'Yellow'],
                datasets: [{
                    label: 'Donut Chart 1',
                    data: [300, 50, 100],
                    backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)'],
                    borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'  
                    }
                }
            }
        });

        // Donut Chart 2
        const donutChart2 = document.getElementById('donutChart2').getContext('2d');
        new Chart(donutChart2, {
            type: 'doughnut',
            data: {
                labels: ['Green', 'Purple', 'Orange'],
                datasets: [{
                    label: 'Donut Chart 2',
                    data: [200, 150, 50],
                    backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)'],
                    borderColor: ['rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right' 
                    }
                }
            }
        });
    </script>
@stop
