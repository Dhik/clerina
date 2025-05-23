@extends('adminlte::page')

@section('title', trans('labels.sales'))

@section('content_header')
    <h1>Ads Relation</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <input type="text" id="filterDates" class="form-control daterange" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            @include('admin.sales.ads-recap-card')

        </div>
    </div>
</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
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
    .dataTables_wrapper {
        overflow-x: auto;
        width: 100%;
    }

    #netProfitsTable {
        width: 100% !important;
        white-space: nowrap;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .dt-button-collection {
        padding: 8px !important;
    }
    
    .dt-button-collection .dt-button {
        margin: 2px !important;
    }
    
    .dt-button.buttons-columnVisibility {
        display: block;
        padding: 8px;
        margin: 2px;
        text-align: left;
    }
    
    .dt-button.buttons-columnVisibility.active {
        background: #e9ecef;
    }
    /* Add these styles to your existing CSS section */

/* Optimization Tab Specific Styles */
.recommendations-list {
    max-height: 350px;
    overflow-y: auto;
}

.recommendations-list .alert {
    font-size: 0.9em;
    padding: 10px 15px;
}

.recommendations-list .alert strong {
    font-size: 1em;
    margin-bottom: 5px;
    display: block;
}

/* Small box styling for optimization metrics */
.small-box .inner h4 {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0;
}

.small-box .inner p {
    font-size: 0.85rem;
    margin: 5px 0 0 0;
    color: rgba(255, 255, 255, 0.8);
}

/* Optimization table styling */
#optimizationTable {
    font-size: 0.9rem;
}

#optimizationTable th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-top: none;
}

#optimizationTable td {
    vertical-align: middle;
}

/* Badge styling for recommendations */
.badge-success {
    background-color: #28a745;
}

.badge-primary {
    background-color: #007bff;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-danger {
    background-color: #dc3545;
}

.badge-secondary {
    background-color: #6c757d;
}

/* Chart container improvements */
.card .card-body {
    padding: 1rem;
}

.card-header .card-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .small-box .inner h4 {
        font-size: 1.2rem;
    }
    
    .small-box .inner p {
        font-size: 0.8rem;
    }
    
    #optimizationTable {
        font-size: 0.8rem;
    }
    
    .recommendations-list {
        max-height: 250px;
    }
}

/* Loading states */
.chart-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 400px;
    color: #6c757d;
    font-size: 1.1rem;
}

.chart-loading i {
    margin-right: 10px;
}

/* Hover effects for interactive elements */
.nav-pills .nav-link:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Custom scrollbar for recommendations */
.recommendations-list::-webkit-scrollbar {
    width: 6px;
}

.recommendations-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.recommendations-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.recommendations-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@stop

@section('js')
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <script>
        filterDate = $('#filterDates');
        filterChannel = $('#filterChannel');
        $('#resetFilterBtn').click(function () {
            filterDate.val('')
            netProfitsTable.draw()
        })
        $('.daterange').daterangepicker({
            autoUpdateInput: false,
            autoApply: true,
            alwaysShowCalendars: true,
            locale: {
                cancelLabel: 'Clear',
                format: 'DD/MM/YYYY'
            },
            ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        $('.daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            $(this).trigger('change'); 
        });

        $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $(this).trigger('change'); 
        });
        filterDate.change(function () {
            netProfitsTable.draw();
            fetchSummary();
            loadNetProfitsChart();
            loadCorrelationChart();
            loadDetailCorrelationChart();
            if ($('.nav-link[href="#optimizationTab"]').hasClass('active')) {
                loadOptimizationData();
            }
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
        function loadCorrelationChart() {
            const filterDates = document.getElementById('filterDates').value;
            const selectedVariable = document.getElementById('correlationVariable').value;
            
            fetch(`{{ route('net-profit.sales-vs-marketing') }}?variable=${selectedVariable}${filterDates ? `&filterDates=${filterDates}` : ''}`)
                .then(response => response.json())
                .then(result => {
                    if (result.data && result.layout) {
                        Plotly.newPlot('correlationChart', result.data, result.layout, {
                            responsive: true,
                            displayModeBar: true
                        });
                    }

                    if (result.statistics) {
                        document.getElementById('correlationCoefficient').textContent = 
                            (result.statistics.correlation || 0).toFixed(4);
                        document.getElementById('rSquared').textContent = 
                            (result.statistics.r_squared || 0).toFixed(4);
                        document.getElementById('dataPoints').textContent = 
                            result.statistics.data_points || 0;
                    } else {
                        document.getElementById('correlationCoefficient').textContent = '0.0000';
                        document.getElementById('rSquared').textContent = '0.0000';
                        document.getElementById('dataPoints').textContent = '0';
                    }
                })
                .catch(error => {
                    console.error('Error fetching correlation data:', error);

                    document.getElementById('correlationCoefficient').textContent = '0.0000';
                    document.getElementById('rSquared').textContent = '0.0000';
                    document.getElementById('dataPoints').textContent = '0';
                    
                    if (document.getElementById('correlationChart')) {
                        Plotly.purge('correlationChart');
                    }
                });
        }

        function loadDetailCorrelationChart() {
            const filterDates = document.getElementById('filterDates').value;
            const selectedSku = document.getElementById('skuFilter').value;
            const selectedPlatform = document.getElementById('platformFilter').value;
            
            let url = `{{ route('net-profit.detail-sales-vs-marketing') }}?sku=${selectedSku}&platform=${selectedPlatform}`;
            if (filterDates) {
                url += `&filterDates=${filterDates}`;
            }
            
            fetch(url)
                .then(response => response.json())
                .then(result => {
                    if (result.data && result.layout) {
                        Plotly.newPlot('detailCorrelationChart', result.data, result.layout, {
                            responsive: true,
                            displayModeBar: true
                        });
                    }

                    if (result.statistics) {
                        document.getElementById('detailCorrelationCoefficient').textContent = 
                            (result.statistics.correlation || 0).toFixed(4);
                        document.getElementById('detailRSquared').textContent = 
                            (result.statistics.r_squared || 0).toFixed(4);
                        document.getElementById('detailDataPoints').textContent = 
                            result.statistics.data_points || 0;
                    } else {
                        document.getElementById('detailCorrelationCoefficient').textContent = '0.0000';
                        document.getElementById('detailRSquared').textContent = '0.0000';
                        document.getElementById('detailDataPoints').textContent = '0';
                    }
                })
                .catch(error => {
                    console.error('Error fetching detail correlation data:', error);

                    document.getElementById('detailCorrelationCoefficient').textContent = '0.0000';
                    document.getElementById('detailRSquared').textContent = '0.0000';
                    document.getElementById('detailDataPoints').textContent = '0';
                    
                    if (document.getElementById('detailCorrelationChart')) {
                        Plotly.purge('detailCorrelationChart');
                    }
                });
        }
        loadDetailCorrelationChart();

        function loadOptimizationData() {
            const selectedSku = document.getElementById('optimizationSku').value;
            const filterDates = document.getElementById('filterDates').value;
            
            showLoadingSwal('Loading optimization analysis...');
            
            let url = `{{ route('net-profit.sales-optimization') }}?sku=${selectedSku}`;
            if (filterDates) {
                url += `&filterDates=${filterDates}`;
            }
            
            fetch(url)
                .then(response => response.json())
                .then(result => {
                    Swal.close();
                    
                    if (result.success) {
                        updateOptimizationSummary(result.summary);
                        renderHistoricalTrendChart(result.historical);
                        renderPlatformComparisonChart(result.platforms);
                        renderForecastChart(result.forecast);
                        updateRecommendations(result.recommendations);
                        updateOptimizationTable(result.breakdown);
                    } else {
                        Swal.fire('Error', result.message || 'Failed to load optimization data', 'error');
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error fetching optimization data:', error);
                    Swal.fire('Error', 'Failed to load optimization data', 'error');
                });
        }

        function updateOptimizationSummary(summary) {
            document.getElementById('totalSpent').textContent = 'Rp ' + formatNumber(summary.total_spent);
            document.getElementById('totalSales').textContent = 'Rp ' + formatNumber(summary.total_sales);
            document.getElementById('avgRoas').textContent = summary.avg_roas + 'x';
            document.getElementById('bestPlatform').textContent = summary.best_platform;
        }

        function renderHistoricalTrendChart(data) {
            if (!data || !data.dates) return;
            
            const traces = [];
            
            // Sales trend
            traces.push({
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Sales',
                x: data.dates,
                y: data.sales,
                yaxis: 'y',
                line: {color: '#28a745', width: 2},
                marker: {size: 4}
            });
            
            // Marketing spend trend
            traces.push({
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Marketing Spend',
                x: data.dates,
                y: data.marketing,
                yaxis: 'y2',
                line: {color: '#dc3545', width: 2},
                marker: {size: 4}
            });
            
            // ROAS trend
            traces.push({
                type: 'scatter',
                mode: 'lines+markers',
                name: 'ROAS',
                x: data.dates,
                y: data.roas,
                yaxis: 'y3',
                line: {color: '#ffc107', width: 2},
                marker: {size: 4}
            });
            
            const layout = {
                title: 'Historical Performance Trends (60 Days)',
                xaxis: {
                    title: 'Date',
                    type: 'date'
                },
                yaxis: {
                    title: 'Sales (Rp)',
                    side: 'left',
                    tickformat: ',.0f'
                },
                yaxis2: {
                    title: 'Marketing Spend (Rp)',
                    side: 'right',
                    overlaying: 'y',
                    tickformat: ',.0f'
                },
                yaxis3: {
                    title: 'ROAS',
                    side: 'right',
                    overlaying: 'y',
                    position: 0.85,
                    tickformat: '.2f'
                },
                showlegend: true,
                hovermode: 'x unified'
            };
            
            Plotly.newPlot('historicalTrendChart', traces, layout, {responsive: true});
        }
        function renderPlatformComparisonChart(data) {
            if (!data || !data.platforms) return;
            
            const traces = [
                {
                    type: 'bar',
                    name: 'Total Spent',
                    x: data.platforms,
                    y: data.spent,
                    yaxis: 'y',
                    marker: {color: '#dc3545'}
                },
                {
                    type: 'bar',
                    name: 'Total Sales',
                    x: data.platforms,
                    y: data.sales,
                    yaxis: 'y',
                    marker: {color: '#28a745'}
                },
                {
                    type: 'scatter',
                    mode: 'lines+markers',
                    name: 'ROAS',
                    x: data.platforms,
                    y: data.roas,
                    yaxis: 'y2',
                    line: {color: '#ffc107', width: 3},
                    marker: {size: 8}
                }
            ];
            
            const layout = {
                title: 'Platform Performance Comparison',
                xaxis: {title: 'Platform'},
                yaxis: {
                    title: 'Amount (Rp)',
                    side: 'left',
                    tickformat: ',.0f'
                },
                yaxis2: {
                    title: 'ROAS',
                    side: 'right',
                    overlaying: 'y',
                    tickformat: '.2f'
                },
                showlegend: true,
                barmode: 'group'
            };
            
            Plotly.newPlot('platformComparisonChart', traces, layout, {responsive: true});
        }

        function renderForecastChart(data) {
            if (!data || !data.historical_dates) return;
            
            const traces = [];
            
            // Historical data
            traces.push({
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Historical Sales',
                x: data.historical_dates,
                y: data.historical_sales,
                line: {color: '#007bff', width: 2},
                marker: {size: 4}
            });
            
            traces.push({
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Historical Marketing',
                x: data.historical_dates,
                y: data.historical_marketing,
                yaxis: 'y2',
                line: {color: '#6c757d', width: 2},
                marker: {size: 4}
            });
            
            // Forecast data
            traces.push({
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Predicted Sales',
                x: data.forecast_dates,
                y: data.forecast_sales,
                line: {color: '#28a745', width: 2, dash: 'dash'},
                marker: {size: 6}
            });
            
            traces.push({
                type: 'scatter',
                mode: 'lines+markers',
                name: 'Recommended Marketing',
                x: data.forecast_dates,
                y: data.forecast_marketing,
                yaxis: 'y2',
                line: {color: '#dc3545', width: 2, dash: 'dash'},
                marker: {size: 6}
            });
            
            const layout = {
                title: '3-Day Sales & Marketing Forecast',
                xaxis: {
                    title: 'Date',
                    type: 'date'
                },
                yaxis: {
                    title: 'Sales (Rp)',
                    side: 'left',
                    tickformat: ',.0f'
                },
                yaxis2: {
                    title: 'Marketing Spend (Rp)',
                    side: 'right',
                    overlaying: 'y',
                    tickformat: ',.0f'
                },
                showlegend: true,
                hovermode: 'x unified',
                shapes: [{
                    type: 'line',
                    x0: data.historical_dates[data.historical_dates.length - 1],
                    x1: data.historical_dates[data.historical_dates.length - 1],
                    y0: 0,
                    y1: 1,
                    yref: 'paper',
                    line: {
                        color: 'rgba(255, 0, 0, 0.5)',
                        width: 2,
                        dash: 'dot'
                    }
                }],
                annotations: [{
                    x: data.forecast_dates[0],
                    y: 0.9,
                    yref: 'paper',
                    text: 'Forecast Period',
                    showarrow: false,
                    bgcolor: 'rgba(255, 255, 255, 0.8)',
                    bordercolor: 'rgba(0, 0, 0, 0.5)',
                    borderwidth: 1
                }]
            };
            
            Plotly.newPlot('forecastChart', traces, layout, {responsive: true});
        }

        function updateRecommendations(recommendations) {
            const container = document.getElementById('recommendationsContent');
            
            if (!recommendations || recommendations.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No specific recommendations available.</div>';
                return;
            }
            
            let html = '<div class="recommendations-list">';
            
            recommendations.forEach((rec, index) => {
                const alertType = rec.priority === 'high' ? 'danger' : 
                                rec.priority === 'medium' ? 'warning' : 'info';
                
                html += `
                    <div class="alert alert-${alertType} mb-2">
                        <strong>${rec.title}</strong><br>
                        ${rec.description}<br>
                        <small class="text-muted">Expected Impact: ${rec.impact}</small>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }

        function updateOptimizationTable(breakdown) {
            const tbody = document.getElementById('optimizationTableBody');
            
            if (!breakdown || breakdown.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No data available</td></tr>';
                return;
            }
            
            let html = '';
            breakdown.forEach(item => {
                const roasClass = item.roas >= 3 ? 'text-success' : 
                                item.roas >= 2 ? 'text-warning' : 'text-danger';
                
                html += `
                    <tr>
                        <td>${item.sku_name}</td>
                        <td>${item.platform}</td>
                        <td>Rp ${formatNumber(item.total_spent)}</td>
                        <td>Rp ${formatNumber(item.total_sales)}</td>
                        <td class="${roasClass}">${item.roas}x</td>
                        <td>Rp ${formatNumber(item.avg_daily_spent)}</td>
                        <td>${item.conversion_rate}%</td>
                        <td>
                            <small class="badge badge-${item.recommendation_type}">
                                ${item.recommendation}
                            </small>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Event listeners for the optimization tab
        document.getElementById('optimizationSku').addEventListener('change', loadOptimizationData);
        document.getElementById('refreshOptimization').addEventListener('click', loadOptimizationData);


        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.getAttribute('href') === '#recapChartTab') {
                renderWaterfallChart();
            } else if (e.target.getAttribute('href') === '#correlationTab') {
                loadCorrelationChart();
            } else if (e.target.getAttribute('href') === '#detailCorrelationTab') {
                loadDetailCorrelationChart();
            } else if (e.target.getAttribute('href') === '#optimizationTab') {
                loadOptimizationData();
            }
        });
        $('#skuFilter').on('change', function() {
            loadDetailCorrelationChart();
        });
        $('#platformFilter').on('change', function() {
            loadDetailCorrelationChart();
        });

        document.getElementById('correlationVariable').addEventListener('change', loadCorrelationChart);
    </script>
@stop
