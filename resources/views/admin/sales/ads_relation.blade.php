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
        loadCorrelationChart();

        function loadOptimizationData() {
            const selectedSku = document.getElementById('optimizationSku').value;
            const filterDates = document.getElementById('filterDates').value;
            
            showLoadingSwal('Loading logistic regression analysis...');
            
            let url = `{{ route('net-profit.sales-optimization') }}?sku=${selectedSku}`;
            if (filterDates) {
                url += `&filterDates=${filterDates}`;
            }
            
            fetch(url)
                .then(response => response.json())
                .then(result => {
                    Swal.close();
                    
                    if (result.success) {
                        updateKPICards(result.kpi);
                        renderLogisticRegressionChart(result.logistic_data);
                        updateIdealSpendingTable(result.sku_breakdown);
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

        function updateKPICards(kpi) {
            document.getElementById('totalIdealSpent').textContent = 'Rp ' + formatNumber(kpi.total_ideal_spent);
            document.getElementById('shopeeIdealSpent').textContent = 'Rp ' + formatNumber(kpi.shopee_ideal_spent);
            document.getElementById('metaIdealSpent').textContent = 'Rp ' + formatNumber(kpi.meta_ideal_spent);
            document.getElementById('platformRatio').textContent = kpi.platform_ratio;
        }

        function renderLogisticRegressionChart(data) {
            if (!data || !data.dates) return;
            
            const traces = [];
            
            // Historical data points for Meta Ads
            if (data.meta_historical && data.meta_historical.length > 0) {
                traces.push({
                    type: 'scatter',
                    mode: 'markers',
                    name: 'Meta Ads (Historical)',
                    x: data.historical_dates,
                    y: data.meta_historical,
                    marker: {
                        color: '#1877F2',
                        size: 6,
                        opacity: 0.7
                    },
                    hovertemplate: 'Date: %{x}<br>Meta Spent: Rp %{y:,.0f}<extra></extra>'
                });
            }
            
            // Historical data points for Shopee Ads
            if (data.shopee_historical && data.shopee_historical.length > 0) {
                traces.push({
                    type: 'scatter',
                    mode: 'markers',
                    name: 'Shopee Ads (Historical)',
                    x: data.historical_dates,
                    y: data.shopee_historical,
                    marker: {
                        color: '#EE4D2D',
                        size: 6,
                        opacity: 0.7
                    },
                    hovertemplate: 'Date: %{x}<br>Shopee Spent: Rp %{y:,.0f}<extra></extra>'
                });
            }
            
            // Logistic regression curve for Meta Ads
            if (data.meta_regression && data.meta_regression.length > 0) {
                traces.push({
                    type: 'scatter',
                    mode: 'lines',
                    name: 'Meta Ads (Logistic Trend)',
                    x: data.dates,
                    y: data.meta_regression,
                    line: {
                        color: '#1877F2',
                        width: 3,
                        shape: 'spline'
                    },
                    hovertemplate: 'Date: %{x}<br>Meta Trend: Rp %{y:,.0f}<extra></extra>'
                });
            }
            
            // Logistic regression curve for Shopee Ads
            if (data.shopee_regression && data.shopee_regression.length > 0) {
                traces.push({
                    type: 'scatter',
                    mode: 'lines',
                    name: 'Shopee Ads (Logistic Trend)',
                    x: data.dates,
                    y: data.shopee_regression,
                    line: {
                        color: '#EE4D2D',
                        width: 3,
                        shape: 'spline'
                    },
                    hovertemplate: 'Date: %{x}<br>Shopee Trend: Rp %{y:,.0f}<extra></extra>'
                });
            }
            
            // Forecast points for Meta Ads
            if (data.meta_forecast && data.meta_forecast.length > 0) {
                traces.push({
                    type: 'scatter',
                    mode: 'markers+lines',
                    name: 'Meta Ads (Forecast)',
                    x: data.forecast_dates,
                    y: data.meta_forecast,
                    line: {
                        color: '#1877F2',
                        width: 2,
                        dash: 'dash'
                    },
                    marker: {
                        color: '#1877F2',
                        size: 8,
                        symbol: 'diamond'
                    },
                    hovertemplate: 'Date: %{x}<br>Meta Forecast: Rp %{y:,.0f}<extra></extra>'
                });
            }
            
            // Forecast points for Shopee Ads
            if (data.shopee_forecast && data.shopee_forecast.length > 0) {
                traces.push({
                    type: 'scatter',
                    mode: 'markers+lines',
                    name: 'Shopee Ads (Forecast)',
                    x: data.forecast_dates,
                    y: data.shopee_forecast,
                    line: {
                        color: '#EE4D2D',
                        width: 2,
                        dash: 'dash'
                    },
                    marker: {
                        color: '#EE4D2D',
                        size: 8,
                        symbol: 'diamond'
                    },
                    hovertemplate: 'Date: %{x}<br>Shopee Forecast: Rp %{y:,.0f}<extra></extra>'
                });
            }
            
            const layout = {
                title: {
                    text: 'Logistic Regression Analysis: 60 Days Historical + 3 Days Forecast',
                    font: { size: 16 }
                },
                xaxis: {
                    title: 'Date',
                    type: 'date',
                    tickformat: '%d/%m',
                    showgrid: true,
                    gridcolor: 'rgba(128,128,128,0.2)'
                },
                yaxis: {
                    title: 'Marketing Spend (Rp)',
                    tickformat: ',.0f',
                    showgrid: true,
                    gridcolor: 'rgba(128,128,128,0.2)'
                },
                showlegend: true,
                legend: {
                    orientation: 'h',
                    y: -0.2,
                    x: 0.5,
                    xanchor: 'center'
                },
                hovermode: 'closest',
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                shapes: []
            };
            
            // Add vertical line to separate historical and forecast
            if (data.forecast_dates && data.forecast_dates.length > 0) {
                const separatorDate = data.forecast_dates[0];
                layout.shapes.push({
                    type: 'line',
                    x0: separatorDate,
                    x1: separatorDate,
                    y0: 0,
                    y1: 1,
                    yref: 'paper',
                    line: {
                        color: 'rgba(255, 0, 0, 0.5)',
                        width: 2,
                        dash: 'dot'
                    }
                });
                
                // Add annotation for forecast period
                layout.annotations = [{
                    x: separatorDate,
                    y: 0.9,
                    yref: 'paper',
                    text: 'Forecast Period',
                    showarrow: false,
                    bgcolor: 'rgba(255, 255, 255, 0.8)',
                    bordercolor: 'rgba(0, 0, 0, 0.5)',
                    borderwidth: 1,
                    font: { size: 12 }
                }];
            }
            
            Plotly.newPlot('logisticRegressionChart', traces, layout, {
                responsive: true,
                displayModeBar: true,
                modeBarButtonsToRemove: ['pan2d', 'lasso2d', 'select2d']
            });
        }

        function updateIdealSpendingTable(breakdown) {
            const tbody = document.getElementById('idealSpendingTableBody');
            
            if (!breakdown || breakdown.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No data available</td></tr>';
                return;
            }
            
            let html = '';
            breakdown.forEach(item => {
                html += `
                    <tr>
                        <td><strong>${item.sku}</strong></td>
                        <td>${item.product_name}</td>
                        <td><span class="text-primary font-weight-bold">Rp ${formatNumber(item.total_ideal_spent)}</span></td>
                        <td><span class="text-danger">Rp ${formatNumber(item.shopee_ideal_spent)}</span></td>
                        <td><span class="text-info">Rp ${formatNumber(item.meta_ideal_spent)}</span></td>
                        <td><span class="badge badge-secondary">${item.ratio}</span></td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(Math.round(num));
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
