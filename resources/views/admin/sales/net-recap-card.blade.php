<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                <li class="nav-item"><a class="nav-link active" href="#recapChartTab" data-toggle="tab">Recap</a></li>
                    <li class="nav-item"><a class="nav-link" href="#visitChartTab" data-toggle="tab">Trend Line</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="recapChartTab">
                        <div id="waterfallChart"></div>
                    </div>
                    <div class="tab-pane" id="visitChartTab">
                        <canvas id="visitChart" width="800" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
