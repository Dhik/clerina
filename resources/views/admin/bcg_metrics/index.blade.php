@extends('adminlte::page')

@section('title', 'BCG Metrics Analysis')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>BCG Traffic-Conversion Analysis</h1>
        <div class="btn-group">
            <button class="btn btn-info" onclick="showRecommendations()">
                <i class="fas fa-lightbulb"></i> View Recommendations
            </button>
            <button class="btn btn-secondary" data-toggle="modal" data-target="#advancedFilterModal">
                <i class="fas fa-filter"></i> Advanced Filters
            </button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('bcg_metrics.export', ['format' => 'csv']) }}">
                        <i class="fas fa-file-csv"></i> CSV Data
                    </a>
                    <a class="dropdown-item" href="{{ route('bcg_metrics.export', ['format' => 'json']) }}">
                        <i class="fas fa-file-code"></i> JSON Data
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Key Metrics Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $processedProducts->count() }}</h3>
                    <p>Total Products</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>Rp {{ number_format($processedProducts->sum('sales') / 1000000, 1) }}M</h3>
                    <p>Total Revenue</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ round($processedProducts->avg('conversion_rate'), 2) }}%</h3>
                    <p>Avg Conversion Rate</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ round($processedProducts->where('biaya_ads', '>', 0)->avg('roas'), 1) }}x</h3>
                    <p>Avg ROAS</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ad"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quadrant Summary Cards -->
    <div class="row mb-4">
        @foreach($quadrantSummary as $summary)
        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid {{ $summary['quadrant'] === 'Stars' ? '#28a745' : ($summary['quadrant'] === 'Cash Cows' ? '#ffc107' : ($summary['quadrant'] === 'Question Marks' ? '#17a2b8' : '#dc3545')) }};">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">
                                @if($summary['quadrant'] === 'Stars')
                                    <i class="fas fa-star text-success"></i>
                                @elseif($summary['quadrant'] === 'Cash Cows')
                                    <i class="fas fa-coins text-warning"></i>
                                @elseif($summary['quadrant'] === 'Question Marks')
                                    <i class="fas fa-question text-info"></i>
                                @else
                                    <i class="fas fa-times text-danger"></i>
                                @endif
                                {{ $summary['quadrant'] }}
                            </h5>
                            <p class="text-muted mb-0 small">
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
                    <div class="row text-center small">
                        <div class="col-6">
                            <strong>Revenue</strong><br>
                            <span class="text-success">Rp {{ number_format($summary['total_revenue'] / 1000000, 1) }}M</span>
                        </div>
                        <div class="col-6">
                            <strong>Avg Conv.</strong><br>
                            <span class="text-primary">{{ $summary['avg_conversion'] }}%</span>
                        </div>
                    </div>
                    <div class="row text-center mt-2 small">
                        <div class="col-6">
                            <strong>Ads Cost</strong><br>
                            <span class="text-danger">Rp {{ number_format($summary['total_ads_cost'] / 1000000, 1) }}M</span>
                        </div>
                        <div class="col-6">
                            <strong>ROAS</strong><br>
                            <span class="text-info">{{ $summary['avg_roas'] }}x</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="progress" style="height: 5px;">
                            @php
                                $totalRevenue = $quadrantSummary->sum('total_revenue');
                                $percentage = $totalRevenue > 0 ? ($summary['total_revenue'] / $totalRevenue) * 100 : 0;
                            @endphp
                            <div class="progress-bar" style="width: {{ $percentage }}%; background-color: {{ $summary['quadrant'] === 'Stars' ? '#28a745' : ($summary['quadrant'] === 'Cash Cows' ? '#ffc107' : ($summary['quadrant'] === 'Question Marks' ? '#17a2b8' : '#dc3545')) }}"></div>
                        </div>
                        <small class="text-muted">{{ round($percentage, 1) }}% of total revenue</small>
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
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-default" onclick="toggleChartScale()">
                                <i class="fas fa-expand-arrows-alt"></i> Toggle Scale
                            </button>
                            <button type="button" class="btn btn-primary" onclick="refreshChart()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 500px;">
                        <canvas id="bcgChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <span class="badge badge-lg" style="background-color: #28a745; color: white; padding: 8px 12px;">
                                    <i class="fas fa-star"></i> Stars ({{ $quadrantSummary->get('Stars')['count'] ?? 0 }})
                                </span>
                                <small class="d-block text-muted">High Traffic, High Conversion</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="badge badge-lg" style="background-color: #ffc107; color: black; padding: 8px 12px;">
                                    <i class="fas fa-coins"></i> Cash Cows ({{ $quadrantSummary->get('Cash Cows')['count'] ?? 0 }})
                                </span>
                                <small class="d-block text-muted">Low Traffic, High Conversion</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="badge badge-lg" style="background-color: #17a2b8; color: white; padding: 8px 12px;">
                                    <i class="fas fa-question"></i> Question Marks ({{ $quadrantSummary->get('Question Marks')['count'] ?? 0 }})
                                </span>
                                <small class="d-block text-muted">High Traffic, Low Conversion</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="badge badge-lg" style="background-color: #dc3545; color: white; padding: 8px 12px;">
                                    <i class="fas fa-times"></i> Dogs ({{ $quadrantSummary->get('Dogs')['count'] ?? 0 }})
                                </span>
                                <small class="d-block text-muted">Low Traffic, Low Conversion</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart Info Panel -->
                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Median Traffic Threshold:</strong> {{ number_format($medianTraffic) }} visitors
                            </div>
                            <div class="col-md-4">
                                <strong>Conversion Benchmarks:</strong> 0.6% - 2.0% (price-based)
                            </div>
                            <div class="col-md-4">
                                <strong>Bubble Size:</strong> Represents total revenue
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Product Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Product Details</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <select id="quickFilter" class="form-control">
                                <option value="">All Quadrants</option>
                                <option value="Stars">⭐ Stars</option>
                                <option value="Cash Cows">💰 Cash Cows</option>
                                <option value="Question Marks">❓ Question Marks</option>
                                <option value="Dogs">❌ Dogs</option>
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#advancedFilterModal">
                                    <i class="fas fa-cog"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="productsTable" class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Quadrant</th>
                                    <th>Product Code</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Traffic</th>
                                    <th>Buyers</th>
                                    <th>Conv. Rate</th>
                                    <th>Benchmark</th>
                                    <th>Price</th>
                                    <th>Revenue</th>
                                    <th>ROAS</th>
                                    <th>Stock</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($processedProducts as $product)
                                <tr data-quadrant="{{ $product['quadrant'] }}" class="clickable-row" data-product="{{ $product['kode_produk'] }}">
                                    <td>
                                        <span class="badge" style="background-color: {{ $product['quadrant_color'] }}; color: {{ $product['quadrant'] === 'Cash Cows' ? 'black' : 'white' }};">
                                            @if($product['quadrant'] === 'Stars')⭐@elseif($product['quadrant'] === 'Cash Cows')💰@elseif($product['quadrant'] === 'Question Marks')❓@else❌@endif
                                            {{ $product['quadrant'] }}
                                        </span>
                                    </td>
                                    <td class="product-code-cell">{{ $product['kode_produk'] }}</td>
                                    <td>
                                        <div class="product-name">{{ Str::limit($product['nama_produk'], 30) }}</div>
                                        @if(strlen($product['nama_produk']) > 30)
                                            <small class="text-muted" title="{{ $product['nama_produk'] }}">...</small>
                                        @endif
                                    </td>
                                    <td>{{ $product['sku'] ?: '-' }}</td>
                                    <td class="text-right">{{ number_format($product['visitor']) }}</td>
                                    <td class="text-right">{{ number_format($product['jumlah_pembeli']) }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $product['conversion_rate'] >= $product['benchmark_conversion'] ? 'success' : 'warning' }}">
                                            {{ $product['conversion_rate'] }}%
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $product['benchmark_conversion'] }}%</td>
                                    <td class="text-right">Rp {{ number_format($product['harga']) }}</td>
                                    <td class="text-right">
                                        <strong>Rp {{ number_format($product['sales']) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @if($product['roas'] > 0)
                                            <span class="badge badge-{{ $product['roas'] >= 3 ? 'success' : ($product['roas'] >= 1 ? 'warning' : 'danger') }}">
                                                {{ $product['roas'] }}x
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($product['stock']) }}</td>
                                    <td class="text-center">
                                        @php
                                            $performance = (($product['conversion_rate'] >= $product['benchmark_conversion'] ? 25 : 0) + 
                                                          ($product['roas'] >= 3 ? 25 : ($product['roas'] >= 1 ? 15 : 0)) + 
                                                          ($product['visitor'] >= $medianTraffic ? 25 : 10) + 
                                                          (($product['stock'] > 0 && $product['qty_sold']/$product['stock'] >= 1) ? 25 : 15));
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $performance >= 75 ? 'success' : ($performance >= 50 ? 'warning' : 'danger') }}" 
                                                 role="progressbar" style="width: {{ $performance }}%">
                                                {{ $performance }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions & Insights</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-trophy"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Top Performer</span>
                                    @php $topProduct = $processedProducts->sortByDesc('sales')->first(); @endphp
                                    <span class="info-box-number">{{ Str::limit($topProduct['nama_produk'], 20) }}</span>
                                    <span class="progress-description">Rp {{ number_format($topProduct['sales']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Needs Attention</span>
                                    @php $lowRoas = $processedProducts->where('biaya_ads', '>', 1000000)->where('roas', '<', 1)->count(); @endphp
                                    <span class="info-box-number">{{ $lowRoas }}</span>
                                    <span class="progress-description">High spend, low ROAS</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-rocket"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Growth Opportunities</span>
                                    @php $opportunities = $processedProducts->where('quadrant', 'Question Marks')->filter(function($p) { return $p['conversion_rate'] >= $p['benchmark_conversion'] * 0.8; })->count(); @endphp
                                    <span class="info-box-number">{{ $opportunities }}</span>
                                    <span class="progress-description">Question Marks near conversion</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-warehouse"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Overstocked</span>
                                    @php $overstocked = $processedProducts->filter(function($p) { return $p['stock'] > 1000 && ($p['qty_sold']/$p['stock']) < 0.3; })->count(); @endphp
                                    <span class="info-box-number">{{ $overstocked }}</span>
                                    <span class="progress-description">Slow moving inventory</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('admin.bcg_metrics.modals')

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
.clickable-row {
    cursor: pointer;
}
.clickable-row:hover {
    background-color: #f8f9fa;
}
.product-code-cell {
    color: #007bff;
    font-weight: bold;
}
.product-name {
    font-weight: 500;
}
.info-box {
    min-height: 90px;
}
.badge-lg {
    font-size: 1em;
    padding: 8px 12px;
}
.progress {
    background-color: #e9ecef;
}
.small-box .icon > i {
    font-size: 70px;
}
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>

<script>
let bcgChart;
let isLogScale = true;

$(document).ready(function() {
    // Initialize DataTable
    const table = $('#productsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[9, 'desc']], // Order by revenue
        columnDefs: [
            { targets: [4, 5, 8, 9, 11], className: 'text-right' },
            { targets: [6, 7, 10, 12], className: 'text-center' },
            { targets: [1], className: 'product-code-cell' }
        ]
    });

    // Quick filter
    $('#quickFilter').on('change', function() {
        const selectedQuadrant = $(this).val();
        if (selectedQuadrant === '') {
            table.column(0).search('').draw();
        } else {
            table.column(0).search(selectedQuadrant).draw();
        }
    });

    // Row click handler
    $('#productsTable').on('click', '.clickable-row', function() {
        const kode_produk = $(this).data('product');
        showProductDetails(kode_produk);
    });

    // Initialize chart
    initializeChart();
});

function initializeChart() {
    fetch('{{ route("bcg_metrics.get_chart_data") }}')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('bcgChart').getContext('2d');
            
            if (bcgChart) {
                bcgChart.destroy();
            }
            
            // Group data by quadrant
            const stars = data.data.filter(item => item.quadrant === 'Stars');
            const cashCows = data.data.filter(item => item.quadrant === 'Cash Cows');
            const questionMarks = data.data.filter(item => item.quadrant === 'Question Marks');
            const dogs = data.data.filter(item => item.quadrant === 'Dogs');
            
            bcgChart = new Chart(ctx, {
                type: 'bubble',
                data: {
                    datasets: [
                        {
                            label: 'Stars ⭐',
                            data: stars,
                            backgroundColor: 'rgba(40, 167, 69, 0.6)',
                            borderColor: '#28a745',
                            borderWidth: 2
                        },
                        {
                            label: 'Cash Cows 💰',
                            data: cashCows,
                            backgroundColor: 'rgba(255, 193, 7, 0.6)',
                            borderColor: '#ffc107',
                            borderWidth: 2
                        },
                        {
                            label: 'Question Marks ❓',
                            data: questionMarks,
                            backgroundColor: 'rgba(23, 162, 184, 0.6)',
                            borderColor: '#17a2b8',
                            borderWidth: 2
                        },
                        {
                            label: 'Dogs ❌',
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
                            type: isLogScale ? 'logarithmic' : 'linear',
                            title: {
                                display: true,
                                text: 'Traffic (Visitors)' + (isLogScale ? ' - Log Scale' : '')
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
                            },
                            min: 0
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
                                        `Benchmark: ${point.benchmark}%`,
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
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
            // Add reference lines
            addReferenceLines(data.medianTraffic);
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
        });
    }
    function addReferenceLines(medianTraffic) {
const chart = bcgChart;
chart.options.plugins.afterDraw = function() {
    const ctx = chart.ctx;
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
    
    // Horizontal line for benchmark conversion
    const y = yAxis.getPixelForValue(1.0);
    ctx.beginPath();
    ctx.moveTo(xAxis.left, y);
    ctx.lineTo(xAxis.right, y);
    ctx.stroke();
    
    ctx.restore();
};
}
function toggleChartScale() {
isLogScale = !isLogScale;
initializeChart();
}
function refreshChart() {
initializeChart();
}
function showRecommendations() {
$('#recommendationsModal').modal('show');
fetch('{{ route("bcg_metrics.get_recommendations") }}')
    .then(response => response.json())
    .then(data => {
        let html = generateRecommendationsHTML(data);
        $('#recommendationsContent').html(html);
    })
    .catch(error => {
        $('#recommendationsContent').html('<div class="alert alert-danger">Error loading recommendations</div>');
    });
}
function showProductDetails(kode_produk) {
$('#productDetailsModal').modal('show');
fetch(`{{ route('bcg_metrics.product_details', '') }}/${kode_produk}`)
    .then(response => response.json())
    .then(data => {
        let html = generateProductDetailsHTML(data);
        $('#productDetailsContent').html(html);
    })
    .catch(error => {
        $('#productDetailsContent').html('<div class="alert alert-danger">Error loading product details</div>');
    });
}
function applyAdvancedFilter() {
const formData = new FormData(document.getElementById('advancedFilterForm'));
const params = new URLSearchParams(formData);
fetch(`{{ route('bcg_metrics.advanced_filter') }}?${params}`)
    .then(response => response.json())
    .then(data => {
        updateProductTable(data.products);
        $('#advancedFilterModal').modal('hide');
        showFilterSummary(data);
    })
    .catch(error => {
        console.error('Error applying filters:', error);
    });
}
function updateProductTable(products) {
const table = $('#productsTable').DataTable();
table.clear();
products.forEach(product => {
    const rowData = [
        `<span class="badge" style="background-color: ${getQuadrantColor(product.quadrant)};">${product.quadrant}</span>`,
        product.kode_produk,
        product.nama_produk,
        product.sku || '-',
        product.visitor.toLocaleString(),
        product.jumlah_pembeli.toLocaleString(),
        `<span class="badge badge-${product.conversion_rate >= getBenchmarkConversion(product.harga) ? 'success' : 'warning'}">${product.conversion_rate}%</span>`,
        `${getBenchmarkConversion(product.harga)}%`,
        `Rp ${product.harga.toLocaleString()}`,
        `Rp ${product.sales.toLocaleString()}`,
        product.roas > 0 ? `<span class="badge badge-${product.roas >= 3 ? 'success' : (product.roas >= 1 ? 'warning' : 'danger')}">${product.roas}x</span>` : 'N/A',
        product.stock.toLocaleString()
    ];
    table.row.add(rowData);
});

table.draw();
}
function getQuadrantColor(quadrant) {
const colors = {
'Stars': '#28a745',
'Cash Cows': '#ffc107',
'Question Marks': '#17a2b8',
'Dogs': '#dc3545'
};
return colors[quadrant] || '#6c757d';
}
function getBenchmarkConversion(price) {
if (price < 75000) return 2.0;
if (price < 100000) return 1.5;
if (price < 125000) return 1.0;
if (price < 150000) return 0.8;
return 0.6;
}
// Additional helper functions would be included here...

</script>

@stop