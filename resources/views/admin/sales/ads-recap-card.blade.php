<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#correlationTab" data-toggle="tab">Sales vs Marketing</a></li>
                    <li class="nav-item"><a class="nav-link" href="#detailCorrelationTab" data-toggle="tab">Detail Sales vs Marketing</a></li>
                    <li class="nav-item"><a class="nav-link" href="#optimizationTab" data-toggle="tab">Sales Optimization</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Existing Sales vs Marketing Tab -->
                    <div class="tab-pane active" id="correlationTab">
                        <div class="row">
                            <div class="col-10">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <select class="form-control" id="correlationVariable">
                                            <option value="marketing">Marketing</option>
                                            <option value="spent_kol">KOL Spending</option>
                                            <option value="affiliate">Affiliate</option>
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
                    
                    <!-- Existing Detail Sales vs Marketing Tab -->
                    <div class="tab-pane" id="detailCorrelationTab">
                        <div class="row">
                            <div class="col-10">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <select class="form-control" id="skuFilter">
                                            <option value="all">All SKUs</option>
                                            <option value="CLE-RS-047">Red Saviour (CLE-RS-047)</option>
                                            <option value="CLE-JB30-001">Jelly Booster (CLE-JB30-001)</option>
                                            <option value="CL-GS">Glowsmooth (CL-GS)</option>
                                            <option value="CLE-XFO-008">3 Minutes (CLE-XFO-008)</option>
                                            <option value="CLE-CLNDLA-025">Calendula (CLE-CLNDLA-025)</option>
                                            <option value="CLE-NEG-071">Natural Exfo (CLE-NEG-071)</option>
                                            <option value="CL-TNR">Pore Glow (CL-TNR)</option>
                                            <option value="CL-8XHL">8X Hyalu (CL-8XHL)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="platformFilter">
                                            <option value="all">All Platforms</option>
                                            <option value="Meta Ads">Meta Ads</option>
                                            <option value="Shopee Ads">Shopee Ads</option>
                                            <option value="Meta and Shopee Ads">Meta and Shopee Ads</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="detailCorrelationChart" style="height: 600px;"></div>
                            </div>
                            <div class="col-2">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h4 id="detailCorrelationCoefficient">0</h4>
                                        <p>Correlation Coefficient (r)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="detailRSquared">0</h4>
                                        <p>R-squared (R²)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h4 id="detailDataPoints">0</h4>
                                        <p>Data Points</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NEW Sales Optimization Tab -->
                    <div class="tab-pane" id="optimizationTab">
                        <div class="row">
                            <div class="col-12">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <select class="form-control" id="optimizationSku">
                                            <option value="all">All Products</option>
                                            <option value="CLE-RS-047">Red Saviour (CLE-RS-047)</option>
                                            <option value="CLE-JB30-001">Jelly Booster (CLE-JB30-001)</option>
                                            <option value="CL-GS">Glowsmooth (CL-GS)</option>
                                            <option value="CLE-XFO-008">3 Minutes (CLE-XFO-008)</option>
                                            <option value="CLE-CLNDLA-025">Calendula (CLE-CLNDLA-025)</option>
                                            <option value="CLE-NEG-071">Natural Exfo (CLE-NEG-071)</option>
                                            <option value="CL-TNR">Pore Glow (CL-TNR)</option>
                                            <option value="CL-8XHL">8X Hyalu (CL-8XHL)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-primary" id="refreshOptimization">
                                            <i class="fas fa-sync-alt"></i> Refresh Analysis
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Cards Row -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h4 id="totalSpent">Rp 0</h4>
                                        <p>Total Spent (60 days)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="totalSales">Rp 0</h4>
                                        <p>Total Sales (60 days)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h4 id="avgRoas">0.00x</h4>
                                        <p>Average ROAS</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-secondary">
                                    <div class="inner">
                                        <h4 id="bestPlatform">-</h4>
                                        <p>Best Performing Platform</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Row -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Historical Trends (60 Days)</h3>
                                    </div>
                                    <div class="card-body">
                                        <div id="historicalTrendChart" style="height: 400px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Platform Performance Comparison</h3>
                                    </div>
                                    <div class="card-body">
                                        <div id="platformComparisonChart" style="height: 400px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Forecast and Recommendations Row -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">3-Day Forecast</h3>
                                    </div>
                                    <div class="card-body">
                                        <div id="forecastChart" style="height: 400px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Optimization Recommendations</h3>
                                    </div>
                                    <div class="card-body">
                                        <div id="recommendationsContent">
                                            <div class="text-center">
                                                <i class="fas fa-spinner fa-spin"></i> Loading recommendations...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Breakdown Table -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Detailed Performance Breakdown</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="optimizationTable">
                                                <thead>
                                                    <tr>
                                                        <th>SKU</th>
                                                        <th>Platform</th>
                                                        <th>Total Spent</th>
                                                        <th>Total Sales</th>
                                                        <th>ROAS</th>
                                                        <th>Avg Daily Spent</th>
                                                        <th>Conversion Rate</th>
                                                        <th>Recommendation</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="optimizationTableBody">
                                                    <tr>
                                                        <td colspan="8" class="text-center">
                                                            <i class="fas fa-spinner fa-spin"></i> Loading data...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
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
</div>