<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $processedProducts->count() }}</h3>
                <p>Total Products</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>Rp {{ number_format($processedProducts->sum('sales') / 1000000, 1) }}M</h3>
                <p>Total Revenue</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ round($processedProducts->avg('conversion_rate'), 2) }}%</h3>
                <p>Avg Conversion Rate</p>
            </div>
            <div class="icon">
                <i class="fas fa-percentage"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ round($processedProducts->where('biaya_ads', '>', 0)->avg('roas'), 1) }}x</h3>
                <p>Avg ROAS</p>
            </div>
            <div class="icon">
                <i class="fas fa-ad"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quadrant Summary Cards -->
<div class="row mb-4">
    @foreach($quadrantSummary as $summary)
    <div class="col-lg-3 col-md-6">
        <div class="card" style="border-left: 4px solid {{ $summary['quadrant'] === 'Stars' ? '#28a745' : ($summary['quadrant'] === 'Cash Cows' ? '#ffc107' : ($summary['quadrant'] === 'Question Marks' ? '#17a2b8' : '#dc3545')) }};">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">
                            @if($summary['quadrant'] === 'Stars')
                                <i class="fas fa-star text-success"></i>
                            @elseif($summary['quadrant'] === 'Cash Cows')
                                <i class="fas fa-coins text-warning"></i>
                            @elseif($summary['quadrant'] === 'Question Marks')
                                <i class="fas fa-question text-info"></i>
                            @else
                                <i class="fas fa-times text-danger"></i>
                            @endif
                            {{ $summary['quadrant'] }}
                        </h5>
                        <p class="text-muted mb-0 small">
                            @if($summary['quadrant'] === 'Stars')
                                High Traffic, High Conversion
                            @elseif($summary['quadrant'] === 'Cash Cows')
                                Low Traffic, High Conversion
                            @elseif($summary['quadrant'] === 'Question Marks')
                                High Traffic, Low Conversion
                            @else
                                Low Traffic, Low Conversion
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <h3 class="mb-0">{{ $summary['count'] }}</h3>
                        <small class="text-muted">Products</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center small">
                    <div class="col-6">
                        <strong>Revenue</strong><br>
                        <span class="text-success">Rp {{ number_format($summary['total_revenue'] / 1000000, 1) }}M</span>
                    </div>
                    <div class="col-6">
                        <strong>Avg Conv.</strong><br>
                        <span class="text-primary">{{ $summary['avg_conversion'] }}%</span>
                    </div>
                </div>
                <div class="row text-center mt-2 small">
                    <div class="col-6">
                        <strong>Ads Cost</strong><br>
                        <span class="text-danger">Rp {{ number_format($summary['total_ads_cost'] / 1000000, 1) }}M</span>
                    </div>
                    <div class="col-6">
                        <strong>ROAS</strong><br>
                        <span class="text-info">{{ $summary['avg_roas'] }}x</span>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="progress" style="height: 5px;">
                        @php
                            $totalRevenue = $quadrantSummary->sum('total_revenue');
                            $percentage = $totalRevenue > 0 ? ($summary['total_revenue'] / $totalRevenue) * 100 : 0;
                        @endphp
                        <div class="progress-bar" style="width: {{ $percentage }}%; background-color: {{ $summary['quadrant'] === 'Stars' ? '#28a745' : ($summary['quadrant'] === 'Cash Cows' ? '#ffc107' : ($summary['quadrant'] === 'Question Marks' ? '#17a2b8' : '#dc3545')) }}"></div>
                    </div>
                    <small class="text-muted">{{ round($percentage, 1) }}% of total revenue</small>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>