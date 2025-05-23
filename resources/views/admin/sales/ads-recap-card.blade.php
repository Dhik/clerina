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
                        <!-- Filter Row -->
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

                        <!-- KPI Cards Row -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h4 id="totalIdealSpent">Rp 0</h4>
                                        <p>Total Ideal Spent Today</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="shopeeIdealSpent">Rp 0</h4>
                                        <p>Shopee Ideal Spent Today</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fab fa-shopify"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h4 id="metaIdealSpent">Rp 0</h4>
                                        <p>Meta Ideal Spent Today</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fab fa-facebook"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-secondary">
                                    <div class="inner">
                                        <h4 id="platformRatio">1:1</h4>
                                        <p>Meta : Shopee Ratio</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-balance-scale"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Logistic Regression Chart -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Logistic Regression: 60 Days Historical + 3 Days Forecast</h3>
                                    </div>
                                    <div class="card-body">
                                        <div id="logisticRegressionChart" style="height: 500px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ideal Spending Table -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Ideal Spending per SKU</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="idealSpendingTable">
                                                <thead>
                                                    <tr>
                                                        <th>SKU</th>
                                                        <th>Product Name</th>
                                                        <th>Total Ideal Spent</th>
                                                        <th>Shopee Ideal Spent</th>
                                                        <th>Meta Ideal Spent</th>
                                                        <th>Meta : Shopee Ratio</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="idealSpendingTableBody">
                                                    <tr>
                                                        <td colspan="6" class="text-center">
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