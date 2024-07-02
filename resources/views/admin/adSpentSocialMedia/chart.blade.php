<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#totalChartTab" data-toggle="tab">{{ trans('labels.total') }}</a></li>
                    @foreach($socialMedia as $media)
                        <li class="nav-item">
                            <a class="nav-link" href="#{{ str()->replace(' ', '', $media->name) }}" data-toggle="tab">
                                {{ $media->name }}
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
                    @foreach($socialMedia as $media)
                        <div class="tab-pane" id="{{ str()->replace(' ', '', $media->name) }}">
                            <canvas id="{{ str()->replace(' ', '', $media->name) }}Chart" width="800" height="200"></canvas>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
