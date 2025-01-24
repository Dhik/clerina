<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#recapChartTab" data-toggle="tab">Recap</a></li>
                    <li class="nav-item"><a class="nav-link" href="#netProfitsTab" data-toggle="tab">Net Profits</a></li>
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

document.addEventListener('DOMContentLoaded', loadNetProfitsChart);
</script>