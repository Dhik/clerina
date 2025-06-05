<!-- Tab: Spent vs GMV Content -->
<div class="tab-pane fade" id="spent-vs-gmv-content" role="tabpanel" aria-labelledby="spent-vs-gmv-tab">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <input type="text" id="spentVsGmvFilterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                                </div>
                                <div class="col-auto">
                                    <select class="form-control" id="spentVsGmvChannelFilter">
                                        <option value="">All Channels</option>
                                        <option value="Shopee and Meta">Shopee and Meta</option>
                                        <option value="TikTok Shop">TikTok Shop</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-default" id="spentVsGmvResetFilterBtn">Reset Filter</button>
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info" id="btnRefreshSpentVsGmvData">
                                            <i class="fas fa-sync-alt"></i> Refresh Data
                                        </button>
                                        <button type="button" class="btn btn-success" id="btnExportSpentVsGmvReport">
                                            <i class="fas fa-file-export"></i> Export Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Spent vs GMV Performance</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="spentVsGmvChart" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ROAS Chart -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">ROAS Trend (GMV/Spent)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="roasTrendChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4 id="totalGmvStat">Rp 0</h4>
                            <p class="mb-0">Total GMV</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h4 id="totalSpentStat">Rp 0</h4>
                            <p class="mb-0">Total Spent</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4 id="avgRoasStat">0.00</h4>
                            <p class="mb-0">Avg ROAS</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4 id="spentPercentageStat">0%</h4>
                            <p class="mb-0">Spent/GMV Ratio</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTable -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Spent vs GMV Data</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="spentVsGmvTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Channel</th>
                                    <th>GMV</th>
                                    <th>Ad Spent</th>
                                    <th>ROAS</th>
                                    <th>Spent/GMV %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>