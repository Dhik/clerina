<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#recapChartTab" data-toggle="tab">Recap</a></li>
                    <li class="nav-item"><a class="nav-link" href="#netProfitsTab" data-toggle="tab">Net Profits</a></li>
                    <li class="nav-item"><a class="nav-link" href="#correlationTab" data-toggle="tab">Sales vs Marketing</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="recapChartTab">
                        <div id="waterfallChart"></div>
                    </div>
                    
                    <div class="tab-pane" id="netProfitsTab">
                        <canvas id="netProfitsChart" style="height: 400px;"></canvas>
                    </div>
                    <div class="tab-pane" id="correlationTab">
                        <div class="row">
                            <div class="col-10">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <select class="form-control" id="correlationVariable">
                                            <option value="marketing">Marketing</option>
                                            <option value="spent_kol">KOL Spending</option>
                                            <option value="affiliate">Affiliate</option>
                                            <option value="operasional">Operational</option>
                                            <option value="hpp">HPP</option>
                                            <option value="net_profit">Net Profit</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="correlationChart" style="height: 600px;"></div>
                            </div>
                            <div class="col-2">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h4 id="correlationCoefficient">0</h4>
                                        <p>Correlation Coefficient (r)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="rSquared">0</h4>
                                        <p>R-squared (R²)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h4 id="dataPoints">0</h4>
                                        <p>Data Points</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadNetProfitsChart() {
        fetch("{{ route('sales.net_sales_line') }}")
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('netProfitsChart').getContext('2d');
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(item => item.date),
                        datasets: [{
                            label: 'Sales',
                            data: data.map(item => item.sales),
                            borderColor: '#4CAF50',
                            tension: 0.1,
                            fill: false
                        }, {
                            label: 'Marketing',
                            data: data.map(item => item.marketing),
                            borderColor: '#2196F3',
                            tension: 0.1,
                            fill: false
                        }, {
                            label: 'HPP',
                            data: data.map(item => item.hpp),
                            borderColor: '#FFC107',
                            tension: 0.1,
                            fill: false
                        }, {
                            label: 'Net Profit',
                            data: data.map(item => item.netProfit),
                            borderColor: '#F44336',
                            tension: 0.1,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': Rp ' + Math.round(context.raw).toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                grid: {
                                    zeroLineColor: '#888',
                                    zeroLineWidth: 1
                                },
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + Math.round(value).toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            })
            .catch(error => console.error('Error:', error));
    }
    function loadCorrelationChart() {
        const filterDates = document.getElementById('filterDates').value;
        const selectedVariable = document.getElementById('correlationVariable').value;
        
        fetch(`{{ route('net-profit.sales-vs-marketing') }}?variable=${selectedVariable}${filterDates ? `&filterDates=${filterDates}` : ''}`)
            .then(response => response.json())
            .then(result => {
                // Create the Plotly chart
                Plotly.newPlot('correlationChart', result.data, result.layout, {
                    responsive: true,
                    displayModeBar: true
                });

                // Update statistics
                document.getElementById('correlationCoefficient').textContent = 
                    result.statistics.correlation.toFixed(4);
                document.getElementById('rSquared').textContent = 
                    result.statistics.r_squared.toFixed(4);
                document.getElementById('dataPoints').textContent = 
                    result.statistics.data_points;
            })
            .catch(error => console.error('Error fetching correlation data:', error));
    }

    document.getElementById('correlationVariable').addEventListener('change', loadCorrelationChart);


    document.addEventListener('DOMContentLoaded', function() {
        loadNetProfitsChart();
        loadCorrelationChart();
    });
</script>