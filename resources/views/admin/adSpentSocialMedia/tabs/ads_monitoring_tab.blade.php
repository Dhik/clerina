<!-- Tab: Ads Monitoring Content -->
<div class="tab-pane fade" id="ads-monitoring-content" role="tabpanel" aria-labelledby="ads-monitoring-tab">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <input type="text" id="adsMonitoringFilterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                                </div>
                                <div class="col-auto">
                                    <select class="form-control" id="adsMonitoringChannelFilter">
                                        <option value="">All Channels</option>
                                        <option value="meta_ads">Meta Ads</option>
                                        <option value="shopee_ads">Shopee Ads</option>
                                        <option value="tiktok_ads">TikTok Ads</option>
                                        <option value="google_ads">Google Ads</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-default" id="adsMonitoringResetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success" id="btnExportAdsMonitoringReport">
                                            <i class="fas fa-file-export"></i> Export Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Performance Charts Row -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">GMV vs Target Performance</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="gmvPerformanceChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">ROAS vs Target Performance</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="roasPerformanceChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Spent and CPA Charts Row -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Ad Spend vs Target</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="spentPerformanceChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">CPA vs Target Performance</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="cpaPerformanceChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTable -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ads Monitoring Data</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="adsMonitoringTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Channel</th>
                                    <th>GMV Target</th>
                                    <th>GMV Actual</th>
                                    <th>GMV Variance</th>
                                    <th>Spent Target</th>
                                    <th>Spent Actual</th>
                                    <th>Spent Variance</th>
                                    <th>ROAS Target</th>
                                    <th>ROAS Actual</th>
                                    <th>ROAS Variance</th>
                                    <th>CPA Target</th>
                                    <th>CPA Actual</th>
                                    <th>CPA Variance</th>
                                    <th>AOV/CPA Target</th>
                                    <th>AOV/CPA Actual</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>