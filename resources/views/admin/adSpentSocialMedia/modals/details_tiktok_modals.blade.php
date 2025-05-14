<!-- TikTok Daily Details Modal -->
<div class="modal fade" id="tiktokDailyDetailsModal" tabindex="-1" role="dialog" aria-labelledby="tiktokDailyDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 95%; width: 95%;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tiktokDailyDetailsModalLabel">TikTok Campaign Details</h5>
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
                            <input type="text" id="tiktokModalFilterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
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
                            <div class="col-4">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4 id="tiktokSummaryTofuSpent">-</h4>
                                        <p>TOFU Spent <span id="tiktokSummaryTofuPercentage" class="badge badge-light">-%</span></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-funnel-dollar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4 id="tiktokSummaryMofuSpent">-</h4>
                                        <p>MOFU Spent <span id="tiktokSummaryMofuPercentage" class="badge badge-light">-%</span></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-funnel-dollar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4 id="tiktokSummaryBofuSpent">-</h4>
                                        <p>BOFU Spent <span id="tiktokSummaryBofuPercentage" class="badge badge-light">-%</span></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-funnel-dollar"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Existing KPI Cards -->
                            <div class="col-3">
                                <div class="small-box bg-gradient-success">
                                    <div class="inner">
                                        <h4 id="tiktokSummaryAccountsCount">-</h4>
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
                                        <h4 id="tiktokSummaryTotalSpent">-</h4>
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
                                        <h4 id="tiktokSummaryTotalPurchases">-</h4>
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
                                        <h4 id="tiktokSummaryConversionValue">-</h4>
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
                                        <h4 id="tiktokSummaryRoas">-</h4>
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
                                        <h4 id="tiktokSummaryCostPerPurchase">-</h4>
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
                                        <h4 id="tiktokSummaryImpressions">-</h4>
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
                                        <h4 id="tiktokSummaryCtr">-</h4>
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
                    <table id="tiktokCampaignDetailsTable" class="table table-bordered table-striped dataTable" width="100%">
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