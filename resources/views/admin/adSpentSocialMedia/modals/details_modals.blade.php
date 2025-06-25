<!-- Detail Sales Modal -->
<div class="modal fade" id="detailSalesModal" tabindex="-1" role="dialog" aria-labelledby="detailSalesModalLabel" aria-hidden="true">
</div>

<!-- Daily Details Modal -->
<div class="modal fade" id="dailyDetailsModal" tabindex="-1" role="dialog" aria-labelledby="dailyDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 95%; width: 95%;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dailyDetailsModalLabel">Campaign Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4 offset-md-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" id="modalFilterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                        </div>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- New Funnel Stage KPI Cards -->
                            <div class="col-3">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4 id="summaryTofuSpent">-</h4>
                                        <p>TOFU Spent <span id="summaryTofuPercentage" class="badge badge-light">-%</span></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-funnel-dollar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4 id="summaryMofuSpent">-</h4>
                                        <p>MOFU Spent <span id="summaryMofuPercentage" class="badge badge-light">-%</span></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-funnel-dollar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4 id="summaryBofuSpent">-</h4>
                                        <p>BOFU Spent <span id="summaryBofuPercentage" class="badge badge-light">-%</span></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-funnel-dollar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4 id="summaryShopeeSpent">-</h4>
                                        <p>SHOPEE Spent <span id="summaryShopeePercentage" class="badge badge-light">-%</span></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fab fa-shopify"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Existing KPI Cards -->
                            <div class="col-3">
                                <div class="small-box bg-gradient-success">
                                    <div class="inner">
                                        <h4 id="summaryAccountsCount">-</h4>
                                        <p>Total Accounts</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-primary">
                                    <div class="inner">
                                        <h4 id="summaryTotalSpent">-</h4>
                                        <p>Total Spent</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-money-bill"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-info">
                                    <div class="inner">
                                        <h4 id="summaryTotalPurchases">-</h4>
                                        <p>Total Purchases</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-teal">
                                    <div class="inner">
                                        <h4 id="summaryConversionValue">-</h4>
                                        <p>Conversion Value</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-info">
                                    <div class="inner">
                                        <h4 id="summaryRoas">-</h4>
                                        <p>Overall ROAS</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-teal">
                                    <div class="inner">
                                        <h4 id="summaryCostPerPurchase">-</h4>
                                        <p>Avg. CPP</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-primary">
                                    <div class="inner">
                                        <h4 id="summaryImpressions">-</h4>
                                        <p>Total Impressions</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-success">
                                    <div class="inner">
                                        <h4 id="summaryCtr">-</h4>
                                        <p>Overall CTR</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-mouse-pointer"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Details Table -->
                <div class="table-responsive">
                    <table id="campaignDetailsTable" class="table table-bordered table-striped dataTable" width="100%">
                        <thead>
                            <tr>
                                <th>Nama Akun</th>
                                <th>Product Category</th>
                                <th>TOFU Spent</th>
                                <th>TOFU %</th>
                                <th>MOFU Spent</th>
                                <th>MOFU %</th>
                                <th>BOFU Spent</th>
                                <th>BOFU %</th>
                                <th>SHOPEE Spent</th>
                                <th>SHOPEE %</th>
                                <th>Last Updated Count</th>
                                <th>New Created Count</th>
                                <th>Total Spent</th>
                                <th>Impressions</th>
                                <th>Link Clicks</th>
                                <th>Content Views</th>
                                <th>Adds to Cart</th>
                                <th>Purchases</th>
                                <th>Conversion Value</th>
                                <th>Cost per View</th>
                                <th>Cost per ATC</th>
                                <th>Cost per Purchase</th>
                                <th>ROAS</th>
                                <th>CPM</th>
                                <th>CTR</th>
                                <th>Performance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="platformComparisonModal" tabindex="-1" role="dialog" aria-labelledby="platformComparisonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="platformComparisonModalLabel">Platform Comparison</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Performance Comparison</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="comparisonChart" width="100%" height="400"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Performance Metrics</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="comparisonMetricsTable">
                                        <thead>
                                            <tr>
                                                <th>Metric</th>
                                                <th>Shopee Mall</th>
                                                <th>Shopee 2</th>
                                                <th>Shopee 3</th>
                                                <th>TikTok</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Impressions</td>
                                                <td id="shopeeMallImpressions">-</td>
                                                <td id="shopee2Impressions">-</td>
                                                <td id="shopee3Impressions">-</td>
                                                <td id="tiktokImpressions">-</td>
                                            </tr>
                                            <tr>
                                                <td>Ad Spend</td>
                                                <td id="shopeeMallSpent">-</td>
                                                <td id="shopee2Spent">-</td>
                                                <td id="shopee3Spent">-</td>
                                                <td id="tiktokSpent">-</td>
                                            </tr>
                                            <tr>
                                                <td>CTR</td>
                                                <td id="shopeeMallCtr">-</td>
                                                <td id="shopee2Ctr">-</td>
                                                <td id="shopee3Ctr">-</td>
                                                <td id="tiktokCtr">-</td>
                                            </tr>
                                            <tr>
                                                <td>Conversion Rate</td>
                                                <td id="shopeeMallConvRate">-</td>
                                                <td id="shopee2ConvRate">-</td>
                                                <td id="shopee3ConvRate">-</td>
                                                <td id="tiktokConvRate">-</td>
                                            </tr>
                                            <tr>
                                                <td>CPP</td>
                                                <td id="shopeeMallCpp">-</td>
                                                <td id="shopee2Cpp">-</td>
                                                <td id="shopee3Cpp">-</td>
                                                <td id="tiktokCpp">-</td>
                                            </tr>
                                            <tr>
                                                <td>ROAS</td>
                                                <td id="shopeeMallRoas">-</td>
                                                <td id="shopee2Roas">-</td>
                                                <td id="shopee3Roas">-</td>
                                                <td id="tiktokRoas">-</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>