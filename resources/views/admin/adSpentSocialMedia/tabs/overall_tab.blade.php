<!-- Tab 4: Overall Performance Content -->
<div class="tab-pane fade" id="overall-content" role="tabpanel" aria-labelledby="overall-tab">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <input type="text" id="overallFilterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                                </div>
                                <div class="col-auto">
                                    <select class="form-control" id="overallKategoriProdukFilter">
                                        <option value="">All Categories</option>
                                        @foreach($kategoriProdukList as $kategori)
                                            <option value="{{ $kategori }}">{{ $kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select class="form-control" id="overallPicFilter">
                                        <option value="">All PIC</option>
                                        @foreach($picList as $pic)
                                            <option value="{{ $pic }}">{{ $pic }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-default" id="overallResetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success" id="btnExportOverallReport">
                                            <i class="fas fa-file-export"></i> Export Overall Report
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
                                        <h5 class="card-title mb-0">Platform Comparison</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="platformComparisonChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Overall Performance Metrics</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="overallPerformanceChart"></div>
                                        <div id="overallPerformanceMetrics" class="mt-4"></div>
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
                <table id="overallPerformanceTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Platform</th>
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
                            <th>Compare</th>
                        </tr>
                    </thead>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>