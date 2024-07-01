<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#visitChartTab" data-toggle="tab">{{ trans('labels.visit') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#closingRateChartTab" data-toggle="tab">{{ trans('labels.closing_rate') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#orderChartTab" data-toggle="tab">{{ trans('labels.order') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#turnOverChartTab" data-toggle="tab">{{ trans('labels.sales') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#adSpentChartTab" data-toggle="tab">{{ trans('labels.ad_spent_total') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#roasChartTab" data-toggle="tab">{{ trans('labels.roas') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#qtyChartTab" data-toggle="tab">{{ trans('labels.qty') }}</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="visitChartTab">
                        <canvas id="visitChart" width="800" height="200"></canvas>
                    </div>
                    <div class="tab-pane" id="closingRateChartTab">
                        <canvas id="closingRateChart" width="800" height="200"></canvas>
                    </div>
                    <div class="tab-pane" id="orderChartTab">
                        <canvas id="orderChart" width="800" height="200"></canvas>
                    </div>
                    <div class="tab-pane" id="turnOverChartTab">
                        <canvas id="turnOverChart" width="800" height="200"></canvas>
                    </div>
                    <div class="tab-pane" id="adSpentChartTab">
                        <canvas id="adSpentChart" width="800" height="200"></canvas>
                    </div>
                    <div class="tab-pane" id="roasChartTab">
                        <canvas id="roasChart" width="800" height="200"></canvas>
                    </div>
                    <div class="tab-pane" id="qtyChartTab">
                        <canvas id="qtyChart" width="800" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
