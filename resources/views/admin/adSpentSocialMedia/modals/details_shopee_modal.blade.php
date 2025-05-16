<!-- Shopee Ads Details Modal -->
<div class="modal fade" id="shopeeDetailsModal" tabindex="-1" role="dialog" aria-labelledby="shopeeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 95%; width: 95%;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shopeeDetailsModalLabel">Shopee Ads Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" id="shopeeModalFilterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="shopeeModalKodeProdukFilter">
                            <option value="">All Products</option>
                            @foreach($kategoriProdukList as $produk)
                                <option value="{{ $produk }}">{{ $produk }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <div class="small-box bg-gradient-success">
                                    <div class="inner">
                                        <h4 id="shopeeSummaryTotalAds">-</h4>
                                        <p>Total Ads</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-ad"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-primary">
                                    <div class="inner">
                                        <h4 id="shopeeSummaryTotalImpressions">-</h4>
                                        <p>Total Impressions</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-info">
                                    <div class="inner">
                                        <h4 id="shopeeSummaryTotalClicks">-</h4>
                                        <p>Total Clicks</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-mouse-pointer"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-warning">
                                    <div class="inner">
                                        <h4 id="shopeeSummaryTotalConversions">-</h4>
                                        <p>Total Conversions</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-shopping-basket"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-danger">
                                    <div class="inner">
                                        <h4 id="shopeeSummaryTotalCost">-</h4>
                                        <p>Total Cost</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-money-bill"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-success">
                                    <div class="inner">
                                        <h4 id="shopeeSummaryTotalRevenue">-</h4>
                                        <p>Total Revenue</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-hand-holding-usd"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-info">
                                    <div class="inner">
                                        <h4 id="shopeeSummaryAvgCtr">-</h4>
                                        <p>Average CTR</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-primary">
                                    <div class="inner">
                                        <h4 id="shopeeSummaryAvgConversionRate">-</h4>
                                        <p>Avg. Conversion Rate</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="small-box bg-gradient-success">
                                    <div class="inner">
                                        <h4 id="shopeeSummaryAvgRoas">-</h4>
                                        <p>Average ROAS</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="shopeeDetailsTable" class="table table-bordered table-striped dataTable" width="100%">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>SKU Induk</th>
                                <th>Dilihat</th>
                                <th>Suka</th>
                                <th>Jumlah Klik</th>
                                <th>CTR</th>
                                <th>Keranjang</th>
                                <th>Pesanan Dibuat</th>
                                <th>Pesanan Siap</th>
                                <th>Terjual</th>
                                <th>CR</th>
                                <th>Biaya</th>
                                <th>Omzet</th>
                                <th>ROAS</th>
                                <th>Efektivitas</th>
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