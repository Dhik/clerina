<div class="modal fade" id="recommendationsModal" tabindex="-1" role="dialog" aria-labelledby="recommendationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="recommendationsModalLabel">
                    <i class="fas fa-lightbulb"></i> Strategic Recommendations
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="recommendationsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading recommendations...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="exportRecommendations()">
                    <i class="fas fa-download"></i> Export Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal fade" id="productDetailsModal" tabindex="-1" role="dialog" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productDetailsModalLabel">Product Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="productDetailsContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Filter Modal -->
<div class="modal fade" id="advancedFilterModal" tabindex="-1" role="dialog" aria-labelledby="advancedFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="advancedFilterModalLabel">Advanced Filters</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="advancedFilterForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Quadrant</label>
                                <select name="quadrant" class="form-control">
                                    <option value="all">All Quadrants</option>
                                    <option value="Stars">Stars</option>
                                    <option value="Cash Cows">Cash Cows</option>
                                    <option value="Question Marks">Question Marks</option>
                                    <option value="Dogs">Dogs</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sort By</label>
                                <select name="sort_by" class="form-control">
                                    <option value="sales">Revenue</option>
                                    <option value="conversion_rate">Conversion Rate</option>
                                    <option value="roas">ROAS</option>
                                    <option value="visitor">Traffic</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Min Revenue (Rp)</label>
                                <input type="number" name="min_revenue" class="form-control" placeholder="e.g. 10000000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Min Conversion Rate (%)</label>
                                <input type="number" name="min_conversion" class="form-control" step="0.1" placeholder="e.g. 1.5">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Max ROAS</label>
                                <input type="number" name="max_roas" class="form-control" step="0.1" placeholder="e.g. 10">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sort Direction</label>
                                <select name="sort_direction" class="form-control">
                                    <option value="desc">Descending (High to Low)</option>
                                    <option value="asc">Ascending (Low to High)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyAdvancedFilter()">Apply Filters</button>
                <button type="button" class="btn btn-warning" onclick="resetFilters()">Reset</button>
            </div>
        </div>
    </div>
</div>

<script>
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
    
    html += '</div>';
    return html;
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

function exportRecommendations() {
    window.open('{{ route("bcg_metrics.export", ["format" => "csv"]) }}', '_blank');
}

function resetFilters() {
    document.getElementById('advancedFilterForm').reset();
    $('#advancedFilterModal').modal('hide');
    location.reload();
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
    
    setTimeout(() => {
        $('.alert-info').alert('close');
    }, 5000);
}
</script>