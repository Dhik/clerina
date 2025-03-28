<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <!-- <li class="nav-item"><a class="nav-link active" href="#recapChartTab" data-toggle="tab">Recap</a></li> -->
                    <li class="nav-item"><a class="nav-link active" href="#netProfitsTab" data-toggle="tab">Net Profits</a></li>
                    <li class="nav-item"><a class="nav-link" href="#correlationTab" data-toggle="tab">Sales vs Marketing</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- <div class="tab-pane active" id="recapChartTab">
                        <div id="waterfallChart"></div>
                    </div> -->
                    
                    <div class="tab-pane active" id="netProfitsTab">
                        <canvas id="netProfitsChart" style="height: 400px;"></canvas>
                    </div>
                    <div class="tab-pane" id="correlationTab">
                        <div class="row">
                            <div class="col-10">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <select class="form-control" id="correlationVariable">
                                            <option value="marketing">Marketing</option>
                                            <option value="spent_kol">KOL Spending</option>
                                            <option value="affiliate">Affiliate</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="correlationChart" style="height: 600px;"></div>
                            </div>
                            <div class="col-2">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h4 id="correlationCoefficient">0</h4>
                                        <p>Correlation Coefficient (r)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="rSquared">0</h4>
                                        <p>R-squared (R²)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h4 id="dataPoints">0</h4>
                                        <p>Data Points</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>