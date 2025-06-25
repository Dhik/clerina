@extends('adminlte::page')

@section('title', 'BCG Metrics Analysis')

@section('content_header')
    <h1>BCG Traffic-Conversion Analysis</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Quadrant Summary Cards -->
    <div class="row mb-4">
        @foreach($quadrantSummary as $summary)
        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid {{ $summary['quadrant'] === 'Stars' ? '#28a745' : ($summary['quadrant'] === 'Cash Cows' ? '#ffc107' : ($summary['quadrant'] === 'Question Marks' ? '#17a2b8' : '#dc3545')) }};">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">{{ $summary['quadrant'] }}</h5>
                            <p class="text-muted mb-0">
                                @if($summary['quadrant'] === 'Stars')
                                    High Traffic, High Conversion
                                @elseif($summary['quadrant'] === 'Cash Cows')
                                    Low Traffic, High Conversion
                                @elseif($summary['quadrant'] === 'Question Marks')
                                    High Traffic, Low Conversion
                                @else
                                    Low Traffic, Low Conversion
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <h3 class="mb-0">{{ $summary['count'] }}</h3>
                            <small class="text-muted">Products</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <strong>Revenue</strong><br>
                            <span class="text-success">Rp {{ number_format($summary['total_revenue']) }}</span>
                        </div>
                        <div class="col-6">
                            <strong>Avg Conv.</strong><br>
                            <span class="text-primary">{{ $summary['avg_conversion'] }}%</span>
                        </div>
                    </div>
                    <div class="row text-center mt-2">
                        <div class="col-6">
                            <strong>Ads Cost</strong><br>
                            <span class="text-danger">Rp {{ number_format($summary['total_ads_cost']) }}</span>
                        </div>
                        <div class="col-6">
                            <strong>ROAS</strong><br>
                            <span class="text-info">{{ $summary['avg_roas'] }}x</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- BCG Matrix Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">BCG Matrix - Traffic vs Conversion Rate</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" onclick="refreshChart()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 500px;">
                        <canvas id="bcgChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <span class="badge" style="background-color: #28a745; color: white; padding: 8px 12px;">
                                    <i class="fas fa-star"></i> Stars ({{ $quadrantSummary->get('Stars')['count'] ?? 0 }})
                                </span>
                                <small class="d-block text-muted">High Traffic, High Conversion</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="badge" style="background-color: #ffc107; color: black; padding: 8px 12px;">
                                    <i class="fas fa-coins"></i> Cash Cows ({{ $quadrantSummary->get('Cash Cows')['count'] ?? 0 }})
                                </span>
                                <small class="d-block text-muted">Low Traffic, High Conversion</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="badge" style="background-color: #17a2b8; color: white; padding: 8px 12px;">
                                    <i class="fas fa-question"></i> Question Marks ({{ $quadrantSummary->get('Question Marks')['count'] ?? 0 }})
                                </span>
                                <small class="d-block text-muted">High Traffic, Low Conversion</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="badge" style="background-color: #dc3545; color: white; padding: 8px 12px;">
                                    <i class="fas fa-times"></i> Dogs ({{ $quadrantSummary->get('Dogs')['count'] ?? 0 }})
                                </span>
                                <small class="d-block text-muted">Low Traffic, Low Conversion</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Product Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Product Details</h3>
                    <div class="card-tools">
                        <select id="quadrantFilter" class="form-control form-control-sm" style="width: 200px;">
                            <option value="">All Quadrants</option>
                            <option value="Stars">Stars</option>
                            <option value="Cash Cows">Cash Cows</option>
                            <option value="Question Marks">Question Marks</option>
                            <option value="Dogs">Dogs</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="productsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Quadrant</th>
                                    <th>Product Code</th>
                                    <th>SKU</th>
                                    <th>Traffic</th>
                                    <th>Buyers</th>
                                    <th>Conv. Rate</th>
                                    <th>Benchmark</th>
                                    <th>Price</th>
                                    <th>Revenue</th>
                                    <th>ROAS</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($processedProducts as $product)
                                <tr data-quadrant="{{ $product['quadrant'] }}">
                                    <td>
                                        <span class="badge" style="background-color: {{ $product['quadrant_color'] }}; color: {{ $product['quadrant'] === 'Cash Cows' ? 'black' : 'white' }};">
                                            {{ $product['quadrant'] }}
                                        </span>
                                    </td>
                                    <td>{{ $product['kode_produk'] }}</td>
                                    <td>{{ $product['sku'] ?: '-' }}</td>
                                    <td>{{ number_format($product['visitor']) }}</td>
                                    <td>{{ number_format($product['jumlah_pembeli']) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $product['conversion_rate'] >= $product['benchmark_conversion'] ? 'success' : 'warning' }}">
                                            {{ $product['conversion_rate'] }}%
                                        </span>
                                    </td>
                                    <td>{{ $product['benchmark_conversion'] }}%</td>
                                    <td>Rp {{ number_format($product['harga']) }}</td>
                                    <td>Rp {{ number_format($product['sales']) }}</td>
                                    <td>
                                        @if($product['roas'] > 0)
                                            <span class="badge badge-{{ $product['roas'] >= 3 ? 'success' : ($product['roas'] >= 1 ? 'warning' : 'danger') }}">
                                                {{ $product['roas'] }}x
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($product['stock']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.chart-container {
    position: relative;
    height: 500px;
}
.badge {
    font-size: 0.9em;
}
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>

<script>
let bcgChart;

$(document).ready(function() {
    // Initialize DataTable
    const table = $('#productsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[8, 'desc']], // Order by revenue
        columnDefs: [
            { targets: [3, 4, 7, 8, 10], className: 'text-right' }
        ]
    });

    // Quadrant filter
    $('#quadrantFilter').on('change', function() {
        const selectedQuadrant = $(this).val();
        if (selectedQuadrant === '') {
            table.column(0).search('').draw();
        } else {
            table.column(0).search(selectedQuadrant).draw();
        }
    });

    // Initialize chart
    initializeChart();
});

function initializeChart() {
    fetch('{{ route("bcg_metrics.get_chart_data") }}')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('bcgChart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (bcgChart) {
                bcgChart.destroy();
            }
            
            // Group data by quadrant for different datasets
            const stars = data.data.filter(item => item.quadrant === 'Stars');
            const cashCows = data.data.filter(item => item.quadrant === 'Cash Cows');
            const questionMarks = data.data.filter(item => item.quadrant === 'Question Marks');
            const dogs = data.data.filter(item => item.quadrant === 'Dogs');
            
            bcgChart = new Chart(ctx, {
                type: 'bubble',
                data: {
                    datasets: [
                        {
                            label: 'Stars',
                            data: stars,
                            backgroundColor: 'rgba(40, 167, 69, 0.6)',
                            borderColor: '#28a745',
                            borderWidth: 2
                        },
                        {
                            label: 'Cash Cows',
                            data: cashCows,
                            backgroundColor: 'rgba(255, 193, 7, 0.6)',
                            borderColor: '#ffc107',
                            borderWidth: 2
                        },
                        {
                            label: 'Question Marks',
                            data: questionMarks,
                            backgroundColor: 'rgba(23, 162, 184, 0.6)',
                            borderColor: '#17a2b8',
                            borderWidth: 2
                        },
                        {
                            label: 'Dogs',
                            data: dogs,
                            backgroundColor: 'rgba(220, 53, 69, 0.6)',
                            borderColor: '#dc3545',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'logarithmic',
                            title: {
                                display: true,
                                text: 'Traffic (Visitors) - Log Scale'
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Conversion Rate (%)'
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const point = context.raw;
                                    return [
                                        `Product: ${point.label}`,
                                        `Traffic: ${point.x.toLocaleString()}`,
                                        `Conversion: ${point.y}%`,
                                        `Revenue: Rp ${point.revenue.toLocaleString()}`,
                                        `Quadrant: ${point.quadrant}`
                                    ];
                                }
                            }
                        },
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'point'
                    }
                }
            });

            // Add reference lines for median traffic and benchmark conversion
            addReferenceLines(data.medianTraffic);
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
        });
}

function addReferenceLines(medianTraffic) {
    // This would require Chart.js annotation plugin for reference lines
    // For now, we'll add them programmatically
    const chart = bcgChart;
    const ctx = chart.ctx;
    
    chart.options.plugins.afterDraw = function() {
        const xAxis = chart.scales.x;
        const yAxis = chart.scales.y;
        
        ctx.save();
        
        // Vertical line for median traffic
        ctx.strokeStyle = 'rgba(255, 99, 132, 0.5)';
        ctx.lineWidth = 2;
        ctx.setLineDash([5, 5]);
        
        const x = xAxis.getPixelForValue(medianTraffic);
        ctx.beginPath();
        ctx.moveTo(x, yAxis.top);
        ctx.lineTo(x, yAxis.bottom);
        ctx.stroke();
        
        // Horizontal line for average benchmark (1.0%)
        const y = yAxis.getPixelForValue(1.0);
        ctx.beginPath();
        ctx.moveTo(xAxis.left, y);
        ctx.lineTo(xAxis.right, y);
        ctx.stroke();
        
        ctx.restore();
    };
}

function refreshChart() {
    initializeChart();
}
</script>
@stop