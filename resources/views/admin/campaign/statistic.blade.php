

<div class="container-fluid">
    <div class="row">
        <div class="d-flex justify-content-between align-items-center">
            <div class="col-auto">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <input type="text" class="form-control filterDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                    </div>
                    <div class="col-auto">
                        <button id="resetFilterBtn" class="btn btn-outline-secondary">
                            {{ trans('buttons.reset_filter') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    @include('admin.campaign.content.statisticCard')
    @include('admin.campaign.content.topStatistic')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <canvas id="statisticChart" class="w-100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-between">
                        <div class="col-auto">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <select class="form-control" id="filterPlatform">
                                        <option value="" selected>{{ trans('placeholder.select', ['field' => trans('labels.platform')]) }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($platforms as $platform)
                                            <option value={{ $platform['value'] }}>{{ $platform['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" id="filterFyp">
                                        <label for="filterFyp">
                                            {{ trans('labels.fyp') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" id="filterPayment">
                                        <label for="filterPayment">
                                            {{ trans('labels.payment') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" id="filterDelivery">
                                        <label for="filterDelivery">
                                            {{ trans('labels.product_delivery') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            @can('UpdateCampaign', $campaign)
                                <button class="btn btn-primary" data-toggle="modal" data-target="#contentModal">
                                    <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                                </button>
                            @endcan
                            <button id="refreshAllBtn" class="btn btn-success">
                                <i class="fas fa-sync-alt"></i> {{ trans('labels.refresh') }} Statistics
                            </button>

                            <button id="refreshFollowersBtn" class="btn btn-info">
                                <i class="fas fa-sync-alt"></i> Refresh Followers
                            </button>

                            
                        </div>
                        <div class="col-auto">
                            <a class="btn btn-outline-primary" href={{ route('campaignContent.export', $campaign->id) }}>
                                <i class="fas fa-file-download"></i> {{ trans('labels.export') }}
                            </a>
                            @can('UpdateCampaign', $campaign)
                                @if(stripos(strtolower($campaign->title), 'ibooming') !== false)
                                    <button class="btn btn-outline-success" data-toggle="modal" data-target="#contentImportModal">
                                        <i class="fas fa-file-download"></i> {{ trans('labels.import') }}
                                    </button>
                                @elseif(stripos(strtolower($campaign->title), 'kol') !== false)
                                    <button class="btn btn-outline-danger" data-toggle="modal" data-target="#contentImportKOLModal">
                                        <i class="fas fa-file-download"></i> {{ trans('labels.import') }} KOL
                                    </button>
                                @else
                                    <button class="btn btn-outline-success" data-toggle="modal" data-target="#contentImportModal">
                                        <i class="fas fa-file-download"></i> {{ trans('labels.import') }}
                                    </button>
                                @endif
                            @endcan


                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="contentTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.influencer') }}</th>
                                <th>{{ trans('labels.platform') }}</th>
                                <th>{{ trans('labels.product') }}</th>
                                <th>{{ trans('labels.task') }}</th>
                                <th>{{ trans('labels.kode_ads') }}</th>
                                <th>{{ trans('labels.upload_date') }}</th>
                                <th>{{ trans('labels.like') }}</th>
                                <th>{{ trans('labels.comment') }}</th>
                                <th>{{ trans('labels.view') }}</th>
                                <th data-toggle="tooltip" data-placement="top" title="{{ trans('labels.cpm') }}">
                                    {{ trans('labels.cpm_short') }}
                                </th>
                                <th>ER</th>
                                <th>Followers</th>
                                <th>Tierring</th>
                                <th>{{ trans('labels.additional_info') }}</th>
                                <th>{{ trans('labels.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>




@include('admin.campaign.content.modal-refresh-content')
@include('admin.campaign.content.modal-refresh-followers')
@include('admin.campaign.content.modal-create-content')
@include('admin.campaign.content.modal-create-statistic')
@include('admin.campaign.content.modal-update-content')
@include('admin.campaign.content.modal-import-content')
@include('admin.campaign.content.modal-import-kol-content')
@include('admin.campaign.content.modal-detail-content')
