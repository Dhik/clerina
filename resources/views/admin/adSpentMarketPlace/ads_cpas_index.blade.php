@extends('adminlte::page')

@section('title', trans('labels.sales'))

@section('content_header')
    <h1>Ads CPAS Monitor</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-auto">
                                    <input type="text" class="form-control rangeDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importMetaAdsSpentModal" id="btnImportMetaAdsSpent">
                                            <i class="fas fa-file-upload"></i> Import Meta Ads Spent (.csv)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                        <div class="row">
                                        <div class="col-md-6">
                                            <div class="card h-100">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Impressions Over Time</h5>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="impressionChart" width="400" height="300"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card h-100">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Funnel Analysis</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div id="funnelChart"></div>
                                                    <div id="funnelMetrics" class="mt-4"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                <table id="adsMetaTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Spent</th>
                            <th>View Content</th>
                            <th>ATC</th>
                            <th>Purchase</th>
                            <th>CPP</th>
                            <th>Conversion Value</th>
                            <th>ROAS</th>
                            <th>Impression</th>
                            <th>CPM</th>
                            <th>Link Clicks</th>
                            <th>CTR</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                </table>
                </div>
            </div>
        </div>
    </div>
    @include('admin.adSpentMarketPlace.adds_meta')
    <div class="modal fade" id="detailSalesModal" tabindex="-1" role="dialog" aria-labelledby="detailSalesModalLabel" aria-hidden="true">
</div>

<div class="modal fade" id="dailyDetailsModal" tabindex="-1" role="dialog" aria-labelledby="dailyDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dailyDetailsModalLabel">Campaign Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="campaignDetailsTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th width="25%">Campaign</th>
                            <th>Product Category</th>
                            <th>Total Spent</th>
                            <th>Conversion Value</th>
                            <th>ROAS</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
    

@stop

@section('css')
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
#funnelMetrics {
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 4px;
}
.text-muted {
    color: #6c757d;
}
.font-weight-bold {
    font-weight: 600;
}
.ml-2 {
    margin-left: 0.5rem;
}
.mb-2 {
    margin-bottom: 0.5rem;
}
</style>
@stop

@section('js')
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        filterDate = $('#filterDates');
        filterChannel = $('#filterChannel');
        let funnelChart = null;
        let impressionChart = null;

        $('#btnAddVisit').click(function() {
            $('#dateVisit').val(moment().format("DD/MM/YYYY"));
        });

        $('#btnAddAdSpentSM').click(function() {
            $('#dateAdSpentSocialMedia').val(moment().format("DD/MM/YYYY"));
        });

        $('#btnAddAdSpentMP').click(function() {
            $('#dateAdSpentMarketPlace').val(moment().format("DD/MM/YYYY"));
        });

        $('#resetFilterBtn').click(function () {
            filterDate.val('')
            filterChannel.val('')
            updateRecapCount()
            adsMetaTable.draw()
        });

        $('#metaAdsCsvFile').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });

        $('#importMetaAdsSpentForm').on('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            $.ajax({
                url: "{{ route('adSpentSocialMedia.import') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#importMetaAdsSpentModal').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    $('#errorImportMetaAdsSpent').addClass('d-none');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON.message,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        filterDate.change(function () {
            adsMetaTable.draw()
            updateRecapCount()
            initFunnelChart()
            fetchImpressionData()
        });

        filterChannel.change(function () {
            adsMetaTable.draw()
            updateRecapCount()
        });

        function updateRecapCount() {
            $.ajax({
                url: '{{ route('sales.get-sales-recap') }}?filterDates=' + filterDate.val() + '&filterChannel=' + filterChannel.val(),
                method: 'GET',
                success: function(response) {
                    $('#newSalesCount').text(response.total_sales);
                    $('#newVisitCount').text(response.total_visit);
                    $('#newOrderCount').text(response.total_order);
                    $('#newAdSpentCount').text(response.total_ad_spent);
                    $('#newQtyCount').text(response.total_qty);
                    $('#newRoasCount').text(response.total_roas);
                    $('#newClosingRateCount').text(response.closing_rate);
                    $('#newCPACount').text(response.cpa);
                    $('#newCampaignExpense').text(response.campaign_expense);
                    $('#newAdsSpentTotal').text(response.total_ads_spent);
                    generateChart(response);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching new orders count:', error);
                }
            });
        }

        let adsMetaTable = $('#adsMetaTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('adSpentSocialMedia.get_ads_cpas') }}",
                data: function (d) {
                    if ($('#dateRangeAds').val()) {
                        let dates = $('#dateRangeAds').val().split(' - ');
                        d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                        d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    }
                    d.kategori_produk = $('#kategoriProdukFilter').val();
                }
            },
            columns: [
                {data: 'date', name: 'date'},
                {data: 'total_amount_spent', name: 'total_amount_spent'},
                {data: 'total_content_views', name: 'total_content_views'},
                {data: 'total_adds_to_cart', name: 'total_adds_to_cart'},
                {data: 'total_purchases', name: 'total_purchases'},
                {data: 'cost_per_purchase', name: 'cost_per_purchase', searchable: false},
                {data: 'total_conversion_value', name: 'total_conversion_value'},
                {data: 'roas', name: 'roas', searchable: false},
                {data: 'total_impressions', name: 'total_impressions'},
                {data: 'cpm', name: 'cpm', searchable: false},
                {data: 'total_link_clicks', name: 'total_link_clicks'},
                {data: 'ctr', name: 'ctr', searchable: false},
                {data: 'performance', name: 'performance', searchable: false}
            ],
            columnDefs: [
                { "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11], "className": "text-right" },
                { "targets": [12], "className": "text-center" }
            ],
            order: [[0, 'desc']]
        });

        let campaignDetailsTable = $('#campaignDetailsTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: "{{ route('adSpentSocialMedia.get_details_by_date') }}",
                data: function (d) {
                    d.date = $('#dailyDetailsModal').data('date');
                    d.kategori_produk = $('#kategoriProdukFilter').val();
                }
            },
            columns: [
                {data: 'campaign_name', name: 'campaign_name', width: '25%'},
                {data: 'kategori_produk', name: 'kategori_produk'},
                {data: 'amount_spent', name: 'amount_spent'},
                {data: 'purchases_conversion_value_shared_items', name: 'purchases_conversion_value_shared_items'},
                {data: 'roas', name: 'roas', searchable: false},
                {data: 'performance', name: 'performance', searchable: false}
            ],
            columnDefs: [
                { "targets": [2, 3, 4], "className": "text-right" },
                { "targets": [1, 5], "className": "text-center" }
            ],
            order: [[0, 'asc']]
        });

        $('#adsMetaTable').on('click', '.date-details', function(){
            let date = $(this).data('date');
            let formattedDate = $(this).text();
            
            $('#dailyDetailsModalLabel').text('Campaign Details for ' + formattedDate);
            $('#dailyDetailsModal').data('date', date);
            
            campaignDetailsTable.draw();
            $('#dailyDetailsModal').modal('show');
        });


        $('#totalSpentCard').click(function() {
            const campaignExpense = $('#newCampaignExpense').text().trim();
            const adsSpentTotal = $('#newAdsSpentTotal').text().trim();
            const totalSpent = $('#newAdSpentCount').text().trim();
            console.log(campaignExpense);
            console.log(adsSpentTotal);
            console.log(totalSpent);

            $('#modalCampaignExpense').text('Campaign Expense: ' + campaignExpense);
            $('#modalAdsSpentTotal').text('Total Ads Spent: ' + adsSpentTotal);
            $('#modalTotalSpent').text('Total Spent: ' + totalSpent);

            $('#detailSpentModal').modal('show');
        });

        let salesPieChart = null;

        $('#totalSalesCard').click(function() {
            $('#detailSalesModal').modal('show');
            
            loadPieChart();
            loadTrendChart();
        });
        function createLineChart(ctx, label, dates, data) {
            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    tooltips: {
                        enabled: true,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                let label = data.datasets[tooltipItem.datasetIndex].label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                return label;
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(value, index, values) {
                                    if (parseInt(value) >= 1000) {
                                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                    } else {
                                        return value;
                                    }
                                }
                            }
                        }]
                    }
                }
            });
        }
        function initFunnelChart() {
            const filterValue = filterDate.val();
            const url = new URL('{{ route("adSpentSocialMedia.funnel-data") }}');
            if (filterValue) {
                url.searchParams.append('filterDates', filterValue);
            }

            // Destroy existing ApexCharts instance if it exists
            if (funnelChart) {
                funnelChart.destroy();
                funnelChart = null;
            }

            fetch(url)
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        const data = result.data;
                        
                        const options = {
                            chart: {
                                type: 'bar',
                                height: 350,
                                toolbar: {
                                    show: false
                                }
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 4,
                                    horizontal: true,
                                    distributed: true,
                                    dataLabels: {
                                        position: 'bottom'
                                    },
                                }
                            },
                            colors: ['#60A5FA', '#3B82F6', '#2563EB', '#1D4ED8'],
                            dataLabels: {
                                enabled: true,
                                formatter: function(val) {
                                    return val.toLocaleString();
                                },
                                style: {
                                    fontSize: '12px',
                                }
                            },
                            xaxis: {
                                categories: data.map(item => item.name),
                                labels: {
                                    show: true,
                                    style: {
                                        fontSize: '12px'
                                    }
                                }
                            },
                            yaxis: {
                                labels: {
                                    show: true,
                                    style: {
                                        fontSize: '12px'
                                    }
                                }
                            },
                            grid: {
                                yaxis: {
                                    lines: {
                                        show: false
                                    }
                                }
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val.toLocaleString();
                                    }
                                }
                            }
                        };

                        const series = [{
                            name: 'Total',
                            data: data.map(item => item.value)
                        }];

                        // Create new ApexCharts instance
                        funnelChart = new ApexCharts(document.querySelector("#funnelChart"), {
                            ...options,
                            series: series
                        });
                        funnelChart.render();

                        const metricsHtml = data.map((item, index) => `
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>${item.name}</span>
                                <span class="font-weight-bold">
                                    ${item.value.toLocaleString()}
                                    ${index > 0 ? `
                                        <span class="text-muted ml-2">
                                            (${((item.value / data[0].value) * 100).toFixed(2)}%)
                                        </span>
                                    ` : ''}
                                </span>
                            </div>
                        `).join('');

                        document.querySelector('#funnelMetrics').innerHTML = metricsHtml;
                    }
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        }
        function loadTrendChart() {
            fetch('{{ route("order.daily-trend") }}')
                .then(response => response.json())
                .then(chartData => {
                    const ctx = document.getElementById('salesTrendChart').getContext('2d');
                    
                    if (salesTrendChart instanceof Chart) {
                        salesTrendChart.destroy();
                    }

                    const processedDatasets = chartData.datasets.map(dataset => ({
                        ...dataset,
                        data: dataset.data.map(point => ({
                            x: new Date(point.x.split(' ').join(' ')),
                            y: parseInt(point.y)
                        })),
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                        fill: true
                    }));
                    
                    salesTrendChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            datasets: processedDatasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'nearest',
                                axis: 'x',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'start',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: {
                                            size: 11
                                        },
                                        boxWidth: 8
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        title: function(context) {
                                            return new Date(context[0].parsed.x).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'short',
                                                year: 'numeric'
                                            });
                                        },
                                        label: function(context) {
                                            const value = context.parsed.y;
                                            return ` ${context.dataset.label}: Rp ${value.toLocaleString('id-ID')}`;
                                        }
                                    },
                                    padding: 10
                                }
                            },
                            scales: {
                                x: {
                                    type: 'time',
                                    time: {
                                        unit: 'day',
                                        displayFormats: {
                                            day: 'dd MMM'
                                        }
                                    },
                                    ticks: {
                                        source: 'auto',
                                        autoSkip: true,
                                        maxRotation: 0
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        drawBorder: true,
                                        drawOnChartArea: true,
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        },
                                        padding: 10
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading trend chart data:', error);
                });
        }

        function fetchImpressionData() {
            const filterValue = filterDate.val();
            const url = new URL('{{ route("adSpentSocialMedia.line-data") }}');
            if (filterValue) {
                url.searchParams.append('filterDates', filterValue);
            }

            // Destroy existing Chart.js instance if it exists
            if (impressionChart) {
                impressionChart.destroy();
                impressionChart = null;
            }

            fetch(url)
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        const impressionData = result.impressions;
                        const impressionDates = impressionData.map(data => data.date);
                        const impressions = impressionData.map(data => data.impressions);

                        const ctxImpression = document.getElementById('impressionChart').getContext('2d');
                        impressionChart = createLineChart(ctxImpression, 'Impressions', impressionDates, impressions);
                    }
                })
                .catch(error => {
                    console.error('Error fetching impression data:', error);
                });
        }

        function loadPieChart() {
            fetch('{{ route("order.pie-status") }}')
                .then(response => response.json())
                .then(chartData => {
                    const ctx = document.getElementById('salesPieChart').getContext('2d');
                    
                    if (salesPieChart instanceof Chart) {
                        salesPieChart.destroy();
                    }
                    
                    salesPieChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: chartData.data.labels,
                            datasets: [{
                                data: chartData.data.datasets[0].data,
                                backgroundColor: chartData.data.datasets[0].backgroundColor,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'center',
                                    labels: {
                                        padding: 15,
                                        usePointStyle: true,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const value = parseInt(context.raw);
                                            return ` ${context.label}: Rp ${value.toLocaleString('id-ID')}`;
                                        }
                                    }
                                }
                            }
                        }
                    });

                    updateTable(chartData);
                })
                .catch(error => {
                    console.error('Error loading pie chart data:', error);
                });
        }
        function updateTable(chartData) {
            const tableBody = $('#salesDetailTable');
            tableBody.empty();

            const { labels, values, percentages } = chartData.rawData;
            
            labels.forEach((label, index) => {
                const amount = parseInt(values[index]);
                const percentage = percentages[index];
                const row = `
                    <tr>
                        <td>${label}</td>
                        <td class="text-right">${amount ? amount.toLocaleString('id-ID') : '0'}</td>
                        <td class="text-right">${percentage.toFixed(2)}%</td>
                    </tr>
                `;
                tableBody.append(row);
            });

            $('#totalAmount').text(parseInt(chartData.rawData.totalAmount).toLocaleString('id-ID'));
        }        
        

        $(function () {
            adsMetaTable.draw();
            updateRecapCount();
            $('[data-toggle="tooltip"]').tooltip();
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

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.getAttribute('href') === '#funnelChartTab') {
                initFunnelChart();
            }
        });
    </script>
    @include('admin.sales.script-chart')
@stop
