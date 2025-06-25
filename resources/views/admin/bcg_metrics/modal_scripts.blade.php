<script>
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

function generateRecommendationsHTML(data) {
    let html = '<div class="row">';
    
    // Recommendations by Quadrant
    html += '<div class="col-12"><h4><i class="fas fa-chess-board"></i> Quadrant Strategies</h4></div>';
    
    Object.entries(data.recommendations).forEach(([quadrant, rec]) => {
        const colors = {
            'Stars': 'success',
            'Cash Cows': 'warning', 
            'Question Marks': 'info',
            'Dogs': 'danger'
        };
        
        html += `
            <div class="col-md-6 mb-3">
                <div class="card border-${colors[quadrant]}">
                    <div class="card-header bg-${colors[quadrant]} text-white">
                        <h5 class="mb-0">${quadrant}</h5>
                        <small>${rec.strategy}</small>
                    </div>
                    <div class="card-body">
                        <h6>Recommended Actions:</h6>
                        <ul class="list-unstyled">`;
        
        rec.actions.forEach(action => {
            html += `<li><i class="fas fa-check-circle text-${colors[quadrant]}"></i> ${action}</li>`;
        });
        
        html += `</ul>
                        <h6>Priority Products:</h6>
                        <small class="text-muted">${rec.priority_products.join(', ')}</small>
                    </div>
                </div>
            </div>`;
    });
    
    // Opportunities Section
    html += '<div class="col-12 mt-4"><h4><i class="fas fa-rocket"></i> Growth Opportunities</h4></div>';
    
    if (data.opportunities.high_traffic_low_conversion.length > 0) {
        html += `
            <div class="col-md-4 mb-3">
                <div class="card border-warning">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0">High Traffic, Low Conversion</h6>
                    </div>
                    <div class="card-body">
                        <small>Focus on conversion optimization for these high-traffic products:</small>
                        <ul class="mt-2">`;
        
        data.opportunities.high_traffic_low_conversion.slice(0, 5).forEach(product => {
            html += `<li class="small">${product.nama_produk} (${product.visitor.toLocaleString()} visitors)</li>`;
        });
        
        html += `</ul></div></div></div>`;
    }
    
    if (data.opportunities.underinvested_stars.length > 0) {
        html += `
            <div class="col-md-4 mb-3">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">Underinvested Stars</h6>
                    </div>
                    <div class="card-body">
                        <small>High ROAS stars that could benefit from more investment:</small>
                        <ul class="mt-2">`;
        
        data.opportunities.underinvested_stars.forEach(product => {
            html += `<li class="small">${product.nama_produk} (ROAS: ${product.roas}x)</li>`;
        });
        
        html += `</ul></div></div></div>`;
    }
    
    // Risk Products Section
    html += '<div class="col-12 mt-4"><h4><i class="fas fa-exclamation-triangle text-danger"></i> Risk Alerts</h4></div>';
    
    if (data.risks.high_ads_low_return.length > 0) {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">High Ad Spend, Low Return</h6>
                    </div>
                    <div class="card-body">
                        <small>Products with high advertising costs but poor ROAS:</small>
                        <ul class="mt-2">`;
        
        data.risks.high_ads_low_return.forEach(product => {
            html += `<li class="small">${product.nama_produk} (ROAS: ${product.roas}x, Ads: Rp ${product.biaya_ads.toLocaleString()})</li>`;
        });
        
        html += `</ul></div></div></div>`;
    }
    
    if (data.risks.overstocked.length > 0) {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card border-warning">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0">Overstocked Products</h6>
                    </div>
                    <div class="card-body">
                        <small>Products with slow stock movement:</small>
                        <ul class="mt-2">`;
        
        data.risks.overstocked.slice(0, 5).forEach(product => {
            html += `<li class="small">${product.nama_produk} (${product.stock.toLocaleString()} units, Turnover: ${product.stock_turnover})</li>`;
        });
        
        html += `</ul></div></div></div>`;
    }
    
    html += '</div>';
    return html;
}

function showProductDetails(kode_produk) {
    $('#productDetailsModal').modal('show');
    
    fetch(`/admin/bcg_metrics/product/${kode_produk}`)
        .then(response => response.json())
        .then(data => {
            let html = generateProductDetailsHTML(data);
            $('#productDetailsContent').html(html);
        })
        .catch(error => {
            $('#productDetailsContent').html('<div class="alert alert-danger">Error loading product details</div>');
        });
}

function generateProductDetailsHTML(data) {
    const product = data.basic_info;
    const metrics = data.calculated_metrics;
    const bcg = data.bcg_classification;
    const recommendations = data.recommendations;
    
    let html = `
        <div class="row">
            <div class="col-md-6">
                <h5>${product.nama_produk}</h5>
                <table class="table table-sm">
                    <tr><td><strong>Product Code:</strong></td><td>${product.kode_produk}</td></tr>
                    <tr><td><strong>SKU:</strong></td><td>${product.sku || 'N/A'}</td></tr>
                    <tr><td><strong>Price:</strong></td><td>Rp ${product.harga.toLocaleString()}</td></tr>
                    <tr><td><strong>Stock:</strong></td><td>${product.stock.toLocaleString()}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>BCG Classification</h6>
                <span class="badge badge-lg" style="background-color: ${bcg.quadrant_color}; color: white; font-size: 1.1em;">
                    ${bcg.quadrant}
                </span>
                <p class="mt-2">
                    <small>Benchmark Conversion: ${bcg.benchmark_conversion}%</small><br>
                    <small>Performance Score: ${metrics.performance_score}/100</small>
                </p>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <h6>Key Metrics</h6>
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="card bg-light">
                            <div class="card-body p-2">
                                <h4 class="text-primary">${metrics.conversion_rate}%</h4>
                                <small>Conversion Rate</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card bg-light">
                            <div class="card-body p-2">
                                <h4 class="text-success">${metrics.roas}x</h4>
                                <small>ROAS</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card bg-light">
                            <div class="card-body p-2">
                                <h4 class="text-info">Rp ${metrics.revenue_per_visitor.toLocaleString()}</h4>
                                <small>Revenue/Visitor</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="card bg-light">
                            <div class="card-body p-2">
                                <h4 class="text-warning">${metrics.stock_turnover}</h4>
                                <small>Stock Turnover</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <h6>Recommendations</h6>
                <div class="alert alert-info">
                    <strong>${recommendations.primary}</strong>
                </div>
                <ul>`;
    
    recommendations.actions.forEach(action => {
        html += `<li>${action}</li>`;
    });
    
    if (recommendations.urgent) {
        html += `</ul><div class="alert alert-danger mt-2">`;
        recommendations.urgent.forEach(urgent => {
            html += `<strong>${urgent}</strong><br>`;
        });
        html += `</div>`;
    } else {
        html += `</ul>`;
    }
    
    html += `</div></div>`;
    
    return html;
}

function applyAdvancedFilter() {
    const formData = new FormData(document.getElementById('advancedFilterForm'));
    const params = new URLSearchParams(formData);
    
    fetch(`/admin/bcg_metrics/advanced-filter?${params}`)
        .then(response => response.json())
        .then(data => {
            updateProductTable(data.products);
            $('#advancedFilterModal').modal('hide');
            
            // Show filter summary
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
            `<span class="badge" style="background-color: ${product.quadrant_color};">${product.quadrant}</span>`,
            product.kode_produk,
            product.sku || '-',
            product.visitor.toLocaleString(),
            product.jumlah_pembeli.toLocaleString(),
            `<span class="badge badge-${product.conversion_rate >= product.benchmark_conversion ? 'success' : 'warning'}">${product.conversion_rate}%</span>`,
            `${product.benchmark_conversion}%`,
            `Rp ${product.harga.toLocaleString()}`,
            `Rp ${product.sales.toLocaleString()}`,
            product.roas > 0 ? `<span class="badge badge-${product.roas >= 3 ? 'success' : (product.roas >= 1 ? 'warning' : 'danger')}">${product.roas}x</span>` : 'N/A',
            product.stock.toLocaleString()
        ];
        table.row.add(rowData);
    });
    
    table.draw();
}

function showFilterSummary(data) {
    const summary = `
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Filter Applied:</strong> Showing ${data.total} products matching your criteria.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    $('.container-fluid').prepend(summary);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        $('.alert-info').alert('close');
    }, 5000);
}

function resetFilters() {
    document.getElementById('advancedFilterForm').reset();
    $('#advancedFilterModal').modal('hide');
    
    // Reload original data
    location.reload();
}

function exportRecommendations() {
    window.open('/admin/bcg_metrics/export/excel', '_blank');
}

// Add click handlers for product codes in table
$(document).ready(function() {
    $('#productsTable').on('click', 'td:nth-child(2)', function() {
        const kode_produk = $(this).text();
        showProductDetails(kode_produk);
    });
});
</script>