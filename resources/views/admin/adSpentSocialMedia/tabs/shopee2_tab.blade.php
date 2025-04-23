<!-- Tab 2: Google Ads Content -->
<div class="tab-pane fade" id="google-content" role="tabpanel" aria-labelledby="google-tab">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <input type="text" id="googleFilterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                                </div>
                                <div class="col-auto">
                                    <select class="form-control" id="googleKategoriProdukFilter">
                                        <option value="">All Categories</option>
                                        @foreach($kategoriProdukList as $kategori)
                                            <option value="{{ $kategori }}">{{ $kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select class="form-control" id="googlePicFilter">
                                        <option value="">All PIC</option>
                                        @foreach($picList as $pic)
                                            <option value="{{ $pic }}">{{ $pic }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-default" id="googleResetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importGoogleAdsSpentModal" id="btnImportGoogleAdsSpent">
                                            <i class="fas fa-file-upload"></i> Import Google Ads Spent (csv or zip)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Google Impressions Over Time</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="googleImpressionChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Google Funnel Analysis</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="googleFunnelChart"></div>
                                        <div id="googleFunnelMetrics" class="mt-4"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                <table id="adsShopee2Table" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Last Updated Count</th>
                            <th>New Created Count</th>
                            <th>Total Spent</th>
                            <th>View Content</th>
                            <th>ATC</th>
                            <th>Purchase</th>
                            <th>CPP</th>
                            <th>Conversion Value</th>
                            <th>ROAS</th>
                            <th>Impression</th>
                            <th>CPM</th>
                            <th>Link Clicks</th>
                            <th>CTR</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>