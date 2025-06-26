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
        const sku = $(this).data('sku'); // Change from product to sku
        showProductDetails(sku);
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

function showProductDetails(sku) {
    $('#productDetailsModal').modal('show');
    
    fetch(`{{ route('bcg_metrics.product_details', '') }}/${sku}`) // Use SKU in URL
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

function generateRecommendationsHTML(data) {
    // This function would generate HTML for recommendations
    // Implementation depends on your data structure
    return '<div class="alert alert-info">Recommendations will be displayed here</div>';
}

function generateProductDetailsHTML(data) {
    // This function would generate HTML for product details
    // Implementation depends on your data structure
    return '<div class="alert alert-info">Product details will be displayed here</div>';
}

function showFilterSummary(data) {
    // Show a summary of applied filters
    console.log('Filter applied:', data);
}
</script>