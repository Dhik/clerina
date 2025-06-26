<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions & Insights</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-trophy"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Top Performer</span>
                                @php $topProduct = $processedProducts->sortByDesc('sales')->first(); @endphp
                                <span class="info-box-number">{{ Str::limit($topProduct['nama_produk'], 20) }}</span>
                                <span class="progress-description">Rp {{ number_format($topProduct['sales']) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Needs Attention</span>
                                @php $lowRoas = $processedProducts->where('biaya_ads', '>', 1000000)->where('roas', '<', 1)->count(); @endphp
                                <span class="info-box-number">{{ $lowRoas }}</span>
                                <span class="progress-description">High spend, low ROAS</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-rocket"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Growth Opportunities</span>
                                @php $opportunities = $processedProducts->where('quadrant', 'Question Marks')->filter(function($p) { return $p['conversion_rate'] >= $p['benchmark_conversion'] * 0.8; })->count(); @endphp
                                <span class="info-box-number">{{ $opportunities }}</span>
                                <span class="progress-description">Question Marks near conversion</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-warehouse"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Overstocked</span>
                                @php $overstocked = $processedProducts->filter(function($p) { return $p['stock'] > 1000 && ($p['qty_sold']/$p['stock']) < 0.3; })->count(); @endphp
                                <span class="info-box-number">{{ $overstocked }}</span>
                                <span class="progress-description">Slow moving inventory</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>