<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#KOLTab" data-toggle="tab">KOL</a></li>
                    <li class="nav-item"><a class="nav-link" href="#AdsTab" data-toggle="tab">Ads</a></li>
                    <li class="nav-item"><a class="nav-link" href="#CreativeTab" data-toggle="tab">Creative</a></li>
                    <li class="nav-item"><a class="nav-link" href="#ActivationTab" data-toggle="tab">Activation</a></li>
                    <li class="nav-item"><a class="nav-link" href="#FreeProductTab" data-toggle="tab">Free Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="#OthersTab" data-toggle="tab">Others</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="KOLTab">
                        <div class="row">
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
                                        <h1 id="percentage" style="font-size: 60px;">0</h1>
                                        <p>Achieved</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="remainingKOL">0</h4>
                                        <p id="statusKOL"></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
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
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="progress" style="height: 30px;">
                                    <div id="ads-progress-bar" 
                                        class="progress-bar" 
                                        role="progressbar" 
                                        style="width: 0%;" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        <span id="ads-progress-label" class="text-dark font-weight-bold">0%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Ads Data</h5>
                                    </div>
                                    <div id="ads-content" class="card-body p-3">
                                        <p>Loading...</p>
                                    </div>
                                </div>
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h1 id="ads-percentage" style="font-size: 60px;">0</h1>
                                        <p>Achieved</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="remainingAds">0</h4>
                                        <p id="statusAds"></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Ads Daily Spend</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="adsLineChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="CreativeTab">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="progress" style="height: 30px;">
                                    <div id="ads-progress-bar" 
                                        class="progress-bar" 
                                        role="progressbar" 
                                        style="width: 0%;" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        <span id="ads-progress-label" class="text-dark font-weight-bold">0%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Creative Data</h5>
                                    </div>
                                    <div id="creative-content" class="card-body p-3">
                                        <p>Loading...</p>
                                    </div>
                                </div>
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h1 id="creative-percentage" style="font-size: 60px;">0</h1>
                                        <p>Achieved</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="remainingCreative">0</h4>
                                        <p id="statusCreative"></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Creative Daily Spend</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="creativeLineChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="ActivationTab">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="progress" style="height: 30px;">
                                    <div id="ads-progress-bar" 
                                        class="progress-bar" 
                                        role="progressbar" 
                                        style="width: 0%;" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        <span id="ads-progress-label" class="text-dark font-weight-bold">0%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Ads Data</h5>
                                    </div>
                                    <div id="activation-content" class="card-body p-3">
                                        <p>Loading...</p>
                                    </div>
                                </div>
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h1 id="activation-percentage" style="font-size: 60px;">0</h1>
                                        <p>Achieved</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="remainingActivation">0</h4>
                                        <p id="statusActivation"></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Activation Daily Spend</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="activationLineChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="FreeProductTab">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="progress" style="height: 30px;">
                                    <div id="ads-progress-bar" 
                                        class="progress-bar" 
                                        role="progressbar" 
                                        style="width: 0%;" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        <span id="ads-progress-label" class="text-dark font-weight-bold">0%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Free Product Data</h5>
                                    </div>
                                    <div id="free-product-content" class="card-body p-3">
                                        <p>Loading...</p>
                                    </div>
                                </div>
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h1 id="free-product-percentage" style="font-size: 60px;">0</h1>
                                        <p>Achieved</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="remainingFreeProduct">0</h4>
                                        <p id="statusFreeProduct"></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Free Product Daily Spend</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="freeProductLineChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="OthersTab">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="progress" style="height: 30px;">
                                    <div id="ads-progress-bar" 
                                        class="progress-bar" 
                                        role="progressbar" 
                                        style="width: 0%;" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        <span id="ads-progress-label" class="text-dark font-weight-bold">0%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Others Data</h5>
                                    </div>
                                    <div id="others-content" class="card-body p-3">
                                        <p>Loading...</p>
                                    </div>
                                </div>
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h1 id="others-percentage" style="font-size: 60px;">0</h1>
                                        <p>Achieved</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="remainingOthers">0</h4>
                                        <p id="statusOthers"></p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Others Daily Spend</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="othersLineChart"></canvas>
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
