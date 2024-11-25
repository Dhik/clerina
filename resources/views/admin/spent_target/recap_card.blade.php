<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#KOLTab" data-toggle="tab">KOL</a></li>
                    <li class="nav-item"><a class="nav-link" href="#AdsTab" data-toggle="tab">Ads</a></li>
                    <li class="nav-item"><a class="nav-link" href="#CreativeTab" data-toggle="tab">Creative</a></li>
                    <li class="nav-item"><a class="nav-link" href="#OthersTab" data-toggle="tab">Others</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="KOLTab">
                        <div class="row">
                            <!-- Progress Bar -->
                            <div class="col-12 mb-3">
                                <div class="progress" style="height: 30px;">
                                    <div id="kol-progress-bar" 
                                        class="progress-bar" 
                                        role="progressbar" 
                                        style="width: 0%;" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        <span id="kol-progress-label" class="text-dark font-weight-bold">0%</span>
                                    </div>
                                </div>
                            </div>
                            <!-- KOL Data Card -->
                            <div class="col-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">KOL Data</h5>
                                    </div>
                                    <div id="kol-content" class="card-body p-3">
                                        <p>Loading...</p>
                                    </div>
                                </div>
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h1 id="percentage">0</h1>
                                        <p>Achieved</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                            </div>
                            <!-- KOL Daily Spend Card -->
                            <div class="col-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">KOL Daily Spend</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="kolLineChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="tab-pane" id="AdsTab">
                        <h5>Ads Data</h5>
                        <div id="ads-content" class="p-3">
                            Loading...
                        </div>
                    </div>

                    <div class="tab-pane" id="CreativeTab">
                        <h5>Creative Data</h5>
                        <div id="creative-content" class="p-3">
                            Loading...
                        </div>
                    </div>

                    <div class="tab-pane" id="OthersTab">
                        <h5>Other Data</h5>
                        <div id="others-content" class="p-3">
                            Loading...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
