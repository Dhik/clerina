<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#totalChartTab" data-toggle="tab">{{ trans('labels.total') }}</a></li>
                    @foreach($salesChannels as $salesChannel)
                        <li class="nav-item">
                            <a class="nav-link" href="#{{ str()->replace(' ', '', $salesChannel->name) }}" data-toggle="tab">
                                {{ $salesChannel->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="totalChartTab">
                        <canvas id="totalChart" width="800" height="200"></canvas>
                    </div>
                    @foreach($salesChannels as $salesChannel)
                        <div class="tab-pane" id="{{ str()->replace(' ', '', $salesChannel->name) }}">
                            <canvas id="{{ str()->replace(' ', '', $salesChannel->name) }}Chart" width="800" height="200"></canvas>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
