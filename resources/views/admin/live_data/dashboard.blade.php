@extends('adminlte::page')

@section('title', 'Live Data Dashboard')

@section('content_header')
    <h1>Live Data Dashboard</h1>
@stop

@section('css')
<style>
    .kpi-card {
        text-align: center;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }
    .kpi-card h5 {
        font-size: 14px;
        margin-bottom: 5px;
        color: #666;
    }
    .kpi-card h3 {
        font-size: 18px;
        margin: 0;
        font-weight: bold;
    }
    .card-primary {
        border-top: 3px solid #007bff;
    }
    .card-success {
        border-top: 3px solid #28a745;
    }
    .card-info {
        border-top: 3px solid #17a2b8;
    }
    .card-warning {
        border-top: 3px solid #ffc107;
    }
    .filter-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
</style>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('live_data.dashboard') }}" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="employee_id" class="form-label">Filter by Employee:</label>
                        <select name="employee_id" id="employee_id" class="form-control">
                            <option value="all" {{ !$selectedEmployeeId || $selectedEmployeeId == 'all' ? 'selected' : '' }}>
                                All Employees
                            </option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->employee_id }}" 
                                    {{ $selectedEmployeeId == $employee->employee_id ? 'selected' : '' }}>
                                    {{ $employee->name }} ({{ $employee->employee_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Performance Dashboard
                    @if($selectedEmployeeId && $selectedEmployeeId !== 'all')
                        @php
                            $selectedEmployee = $employees->where('employee_id', $selectedEmployeeId)->first();
                        @endphp
                        <small class="text-muted">- {{ $selectedEmployee->name ?? $selectedEmployeeId }}</small>
                    @endif
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Line Chart -->
                    <div class="col-md-6">
                        <div class="chart-container" style="position: relative; height:400px;">
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Funnel Chart and KPI Cards -->
                    <div class="col-md-6">
                        <div class="chart-container" style="position: relative; height:300px;">
                            <canvas id="funnelChart"></canvas>
                        </div>
                        
                        <!-- KPI Cards -->
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="kpi-card card-primary">
                                    <h5>Total Views</h5>
                                    <h3>{{ number_format($totalViews) }}</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="kpi-card card-info">
                                    <h5>Total Orders</h5>
                                    <h3>{{ number_format($totalOrders) }}</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="kpi-card card-success">
                                    <h5>Total Sales</h5>
                                    <h3>Rp{{ number_format($totalSales, 0) }}</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="kpi-card card-warning">
                                    <h5>Conversion</h5>
                                    <h3>{{ number_format($averageConversionRate, 1) }}%</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <a href="{{ route('live_data.index') }}" class="btn btn-secondary">Back to Live Data</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(function() {
        // Get current filter values
        const employeeId = $('#employee_id').val();
        
        // Fetch chart data from the server with filters
        const params = new URLSearchParams();
        if (employeeId && employeeId !== 'all') {
            params.append('employee_id', employeeId);
        }
        
        const url = '{{ route('live_data.chart-data') }}' + (params.toString() ? '?' + params.toString() : '');
        
        $.get(url, function(response) {
            // Initialize Line Chart
            initLineChart(response.lineChartData);
            
            // Initialize Funnel Chart
            initFunnelChart(response.funnelData);
        });
        
        // Auto-submit form when dropdown changes
        $('#employee_id').on('change', function() {
            $('#filterForm').submit();
        });
        
        function initLineChart(data) {
            const ctx = document.getElementById('lineChart').getContext('2d');
            const labels = data.map(item => item.label);
            
            const lineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Dilihat',
                            data: data.map(item => item.dilihat),
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.3,
                            borderWidth: 2
                        },
                        {
                            label: 'Penonton Tertinggi',
                            data: data.map(item => item.penonton_tertinggi),
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            tension: 0.3,
                            borderWidth: 2
                        },
                        {
                            label: 'Komentar',
                            data: data.map(item => item.komentar),
                            borderColor: 'rgba(255, 159, 64, 1)',
                            backgroundColor: 'rgba(255, 159, 64, 0.2)',
                            tension: 0.3,
                            borderWidth: 2
                        },
                        {
                            label: 'Pesanan',
                            data: data.map(item => item.pesanan),
                            borderColor: 'rgba(153, 102, 255, 1)',
                            backgroundColor: 'rgba(153, 102, 255, 0.2)',
                            tension: 0.3,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Live Data Performance Trends',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        function initFunnelChart(data) {
            const ctx = document.getElementById('funnelChart').getContext('2d');
            
            const funnelData = {
                labels: data.map(item => item.stage),
                datasets: [
                    {
                        data: data.map(item => item.value),
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(75, 192, 192, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }
                ]
            };
            
            const funnelChart = new Chart(ctx, {
                type: 'bar',
                data: funnelData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        title: {
                            display: true,
                            text: 'Conversion Funnel',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
</script>
@stop