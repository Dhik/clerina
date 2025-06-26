<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">BCG Matrix - Traffic vs Conversion Rate</h3>
                <div class="card-tools">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-default" onclick="toggleChartScale()">
                            <i class="fas fa-expand-arrows-alt"></i> Toggle Scale
                        </button>
                        <button type="button" class="btn btn-primary" onclick="refreshChart()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 500px;">
                    <canvas id="bcgChart"></canvas>
                </div>
                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <span class="badge badge-lg" style="background-color: #28a745; color: white; padding: 8px 12px;">
                                <i class="fas fa-star"></i> Stars ({{ $quadrantSummary->get('Stars')['count'] ?? 0 }})
                            </span>
                            <small class="d-block text-muted">High Traffic, High Conversion</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="badge badge-lg" style="background-color: #ffc107; color: black; padding: 8px 12px;">
                                <i class="fas fa-coins"></i> Cash Cows ({{ $quadrantSummary->get('Cash Cows')['count'] ?? 0 }})
                            </span>
                            <small class="d-block text-muted">Low Traffic, High Conversion</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="badge badge-lg" style="background-color: #17a2b8; color: white; padding: 8px 12px;">
                                <i class="fas fa-question"></i> Question Marks ({{ $quadrantSummary->get('Question Marks')['count'] ?? 0 }})
                            </span>
                            <small class="d-block text-muted">High Traffic, Low Conversion</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="badge badge-lg" style="background-color: #dc3545; color: white; padding: 8px 12px;">
                                <i class="fas fa-times"></i> Dogs ({{ $quadrantSummary->get('Dogs')['count'] ?? 0 }})
                            </span>
                            <small class="d-block text-muted">Low Traffic, Low Conversion</small>
                        </div>
                    </div>
                </div>
                
                <!-- Chart Info Panel -->
                <div class="mt-3 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Median Traffic Threshold:</strong> {{ number_format($medianTraffic) }} visitors
                        </div>
                        <div class="col-md-4">
                            <strong>Conversion Benchmarks:</strong> 0.6% - 2.0% (price-based)
                        </div>
                        <div class="col-md-4">
                            <strong>Bubble Size:</strong> Represents total revenue
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>