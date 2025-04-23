<script>
/**
 * Overall Performance tab functionality
 */
$(document).ready(function() {
    // Initialize variables
    let overallFilterDate = initDateRangePicker('overallFilterDates');
    let overallFilterCategory = $('#overallKategoriProdukFilter');
    let overallFilterPic = $('#overallPicFilter');
    let platformComparisonChart = null;
    let overallPerformanceChart = null;

    // Initialize Overall Performance DataTable
    let overallPerformanceTable = $('#overallPerformanceTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('adSpentSocialMedia.get_overall_performance') }}",
            data: function (d) {
                if (overallFilterDate.val()) {
                    let dates = overallFilterDate.val().split(' - ');
                    d.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
                    d.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
                }
                if (overallFilterCategory.val()) {
                    d.kategori_produk = overallFilterCategory.val();
                }
                if (overallFilterPic.val()) {
                    d.pic = overallFilterPic.val();
                }
            }
        },
        columns: [
            {data: 'date', name: 'date'},
            {data: 'platform', name: 'platform'},
            {
                data: 'total_amount_spent', 
                name: 'total_amount_spent',
                render: function(data) {
                    return 'Rp ' + Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            },
            {
                data: 'total_content_views', 
                name: 'total_content_views',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'total_adds_to_cart', 
                name: 'total_adds_to_cart',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'total_purchases', 
                name: 'total_purchases',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'cost_per_purchase', 
                name: 'cost_per_purchase',
                searchable: false,
                render: function(data) {
                    return 'Rp ' + Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'total_conversion_value', 
                name: 'total_conversion_value',
                render: function(data) {
                    return 'Rp ' + Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'roas', 
                name: 'roas', 
                searchable: false,
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'total_impressions', 
                name: 'total_impressions',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            },
            {
                data: 'cpm', 
                name: 'cpm', 
                searchable: false,
                render: function(data) {
                    return 'Rp ' + Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'total_link_clicks', 
                name: 'total_link_clicks',
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'ctr', 
                name: 'ctr', 
                searchable: false,
                render: function(data) {
                    return Number(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + '%';
                }
            },
            {data: 'performance', name: 'performance', searchable: false},
            {data: 'compare', name: 'compare', orderable: false, searchable: false}
        ],
        columnDefs: [
            { "targets": [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], "className": "text-right" },
            { "targets": [1, 13, 14], "className": "text-center" }
        ],
        order: [[0, 'desc'], [1, 'asc']],
        fixedHeader: true,
        scrollCollapse: true,
        deferRender: true,
        scroller: true
    });

    // Button click handlers
    $('#overallResetFilterBtn').click(function() {
        overallFilterDate.val('');
        overallFilterCategory.val('');
        overallFilterPic.val('');
        overallPerformanceTable.draw();
        fetchPlatformComparisonData();
        initOverallPerformanceChart();
    });

    // Export button handler
    $('#btnExportOverallReport').click(function() {
        let params = {};
        
        if (overallFilterDate.val()) {
            let dates = overallFilterDate.val().split(' - ');
            params.date_start = moment(dates[0], 'DD/MM/YYYY').format('YYYY-MM-DD');
            params.date_end = moment(dates[1], 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        
        if (overallFilterCategory.val()) {
            params.kategori_produk = overallFilterCategory.val();
        }
        
        if (overallFilterPic.val()) {
            params.pic = overallFilterPic.val();
        }
        
        // Show loading state
        showLoadingSwal('Generating report...');
        
        // Create URL with params
        const url = new URL("{{ route('adSpentSocialMedia.export_overall_report') }}", window.location.origin);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        // Create temporary form for POST request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url.toString();
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = $('meta[name="csrf-token"]').attr('content');
        form.appendChild(csrfInput);
        
        // Add to document, submit, and remove
        document.body.appendChild(form);
        form.submit();
        
        // Close loading after a short delay
        setTimeout(function() {
            Swal.close();
        }, 2000);
    });

    // Filter change handlers
    overallFilterCategory.change(function() {
        overallPerformanceTable.draw();
        fetchPlatformComparisonData();
        initOverallPerformanceChart();
    });

    overallFilterDate.change(function() {
        overallPerformanceTable.draw();
        fetchPlatformComparisonData();
        initOverallPerformanceChart();
    });

    overallFilterPic.change(function() {
        overallPerformanceTable.draw();
        fetchPlatformComparisonData();
        initOverallPerformanceChart();
    });

    // Functions specific to Overall Performance tab
    function fetchPlatformComparisonData() {
        const filterValue = overallFilterDate.val();
        const kategoriProduk = overallFilterCategory.val();
        const picValue = overallFilterPic.val();
        
        const url = new URL("{{ route('adSpentSocialMedia.platform-comparison-data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        
        if (kategoriProduk) {
            url.searchParams.append('kategori_produk', kategoriProduk);
        }

        if (picValue) {
            url.searchParams.append('pic', picValue);
        }

        // Destroy existing chart if it exists
        if (platformComparisonChart && typeof platformComparisonChart.destroy === 'function') {
            platformComparisonChart.destroy();
        }
        
        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const ctx = document.getElementById('platformComparisonChart').getContext('2d');
                    
                    // Create the platform comparison chart
                    platformComparisonChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: result.data.labels,
                            datasets: [
                                {
                                    label: 'Ad Spend (Rp)',
                                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1,
                                    data: result.data.spent
                                },
                                {
                                    label: 'Revenue (Rp)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1,
                                    data: result.data.revenue
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: {
                                    stacked: false,
                                    title: {
                                        display: true,
                                        text: 'Platform'
                                    }
                                },
                                y: {
                                    stacked: false,
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(value) {
                                            if (value >= 1000000) {
                                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                            } else if (value >= 1000) {
                                                return 'Rp ' + (value / 1000).toFixed(1) + 'K';
                                            }
                                            return 'Rp ' + value;
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += 'Rp ' + Number(context.raw).toLocaleString('id-ID');
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching platform comparison data:', error);
            });
    }

    function initOverallPerformanceChart() {
        const filterValue = overallFilterDate.val();
        const picValue = overallFilterPic.val();
        const kategoriProduk = overallFilterCategory.val();

        const url = new URL("{{ route('adSpentSocialMedia.overall-metrics-data') }}", window.location.origin);
        
        if (filterValue) {
            url.searchParams.append('filterDates', filterValue);
        }
        
        if (kategoriProduk) {
            url.searchParams.append('kategori_produk', kategoriProduk);
        }
        
        if (picValue) {
            url.searchParams.append('pic', picValue);
        }

        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    // Create overall performance metrics chart
                    const options = {
                        chart: {
                            type: 'radialBar',
                            height: 350
                        },
                        plotOptions: {
                            radialBar: {
                                dataLabels: {
                                    name: {
                                        fontSize: '16px',
                                    },
                                    value: {
                                        fontSize: '14px',
                                        formatter: function(val) {
                                            return val.toFixed(2) + '%';
                                        }
                                    },
                                    total: {
                                        show: true,
                                        label: 'Overall',
                                        formatter: function(w) {
                                            const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0) / w.globals.series.length;
                                            return total.toFixed(2) + '%';
                                        }
                                    }
                                }
                            }
                        },
                        labels: result.data.labels,
                        colors: ['#1a73e8', '#ea4335', '#34a853', '#fbbc04']
                    };

                    // Destroy existing chart if exists
                    if (overallPerformanceChart) {
                        overallPerformanceChart.destroy();
                    }

                    // Create new chart
                    overallPerformanceChart = new ApexCharts(
                        document.querySelector("#overallPerformanceChart"), 
                        {
                            ...options,
                            series: result.data.series
                        }
                    );
                    overallPerformanceChart.render();

                    // Generate metrics table
                    const metricsHtml = `
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Metric</th>
                                        <th>Value</th>
                                        <th>Platform Breakdown</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Total Ad Spend</td>
                                        <td>Rp ${numberFormat(result.data.metrics.total_spent)}</td>
                                        <td>${generatePlatformBreakdownHtml(result.data.metrics.spent_breakdown)}</td>
                                    </tr>
                                    <tr>
                                        <td>Total Revenue</td>
                                        <td>Rp ${numberFormat(result.data.metrics.total_revenue)}</td>
                                        <td>${generatePlatformBreakdownHtml(result.data.metrics.revenue_breakdown)}</td>
                                    </tr>
                                    <tr>
                                        <td>Overall ROAS</td>
                                        <td>${numberFormat(result.data.metrics.overall_roas, 2)}</td>
                                        <td>${generatePlatformBreakdownHtml(result.data.metrics.roas_breakdown, 2)}</td>
                                    </tr>
                                    <tr>
                                        <td>Total Purchases</td>
                                        <td>${numberFormat(result.data.metrics.total_purchases)}</td>
                                        <td>${generatePlatformBreakdownHtml(result.data.metrics.purchases_breakdown)}</td>
                                    </tr>
                                    <tr>
                                        <td>Avg. CPP</td>
                                        <td>Rp ${numberFormat(result.data.metrics.avg_cpp)}</td>
                                        <td>${generatePlatformBreakdownHtml(result.data.metrics.cpp_breakdown)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;

                    document.querySelector('#overallPerformanceMetrics').innerHTML = metricsHtml;
                }
            })
            .catch(error => {
                console.error('Error fetching overall performance metrics:', error);
            });
    }

    // Helper function to generate platform breakdown HTML
    function generatePlatformBreakdownHtml(breakdown, decimals = 0) {
        let html = '<div class="d-flex flex-column">';
        
        Object.keys(breakdown).forEach(platform => {
            const value = typeof breakdown[platform] === 'number' 
                ? (platform.toLowerCase().includes('roas') 
                    ? breakdown[platform].toFixed(2) 
                    : numberFormat(breakdown[platform], decimals))
                : breakdown[platform];
                
            const prefix = platform.toLowerCase().includes('roas') ? '' : 
                          (platform.toLowerCase().includes('cpp') ? 'Rp ' : '');
                
            html += `<small>${platform}: ${prefix}${value}</small>`;
        });
        
        html += '</div>';
        return html;
    }

    // Initialize when Overall tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'overall-tab') {
            setTimeout(function() {
                overallPerformanceTable.columns.adjust();
                if (!platformComparisonChart) {
                    fetchPlatformComparisonData();
                }
                if (!overallPerformanceChart) {
                    initOverallPerformanceChart();
                }
            }, 150);
        }
    });

    // Initialize if Overall tab is active on page load
    if ($('#overall-tab').hasClass('active')) {
        fetchPlatformComparisonData();
        initOverallPerformanceChart();
    }
});
</script>