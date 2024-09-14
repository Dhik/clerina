

<div class="container-fluid">
    <div class="row">
        <div class="d-flex justify-content-between align-items-center">
            <div class="col-auto">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <input type="text" class="form-control filterDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
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
                            <!-- @can('UpdateCampaign', $campaign)
                                <button class="btn btn-primary" data-toggle="modal" data-target="#contentModal">
                                    <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                                </button>
                            @endcan -->
                            <!-- <a class="btn btn-success" href={{ route('statistic.bulkRefresh', $campaign->id) }}>
                                <i class="fas fa-sync-alt"></i> {{ trans('labels.refresh') }} {{ trans('labels.all2') }}
                            </a> -->
                            <button id="refreshAllBtn" class="btn btn-success">
                                <i class="fas fa-sync-alt"></i> {{ trans('labels.refresh') }} {{ trans('labels.all2') }}
                            </button>

                            <a class="btn btn-outline-primary" href={{ route('campaignContent.export', $campaign->id) }}>
                                <i class="fas fa-file-download"></i> {{ trans('labels.export') }}
                            </a>
                            @can('UpdateCampaign', $campaign)
                                <button class="btn btn-outline-success" data-toggle="modal" data-target="#contentImportModal">
                                    <i class="fas fa-file-download"></i> {{ trans('labels.import') }}
                                </button>
                            @endcan

                        </div>
                        <div class="col-auto mt-3">
                            <div class="btn-group mb-3 mr-3" role="group" aria-label="Sorting buttons">
                                <button type="button" class="btn btn-outline-primary" id="sortLikeAsc">
                                    <i class="fas fa-sort-amount-up"></i> {{ trans('labels.like') }} Asc
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="sortLikeDesc">
                                    <i class="fas fa-sort-amount-down"></i> {{ trans('labels.like') }} Desc
                                </button>
                            </div>
                            <div class="btn-group mb-3 mr-3" role="group" aria-label="Sorting buttons">
                                <button type="button" class="btn btn-outline-primary" id="sortCommentAsc">
                                    <i class="fas fa-sort-amount-up"></i> {{ trans('labels.comment') }} Asc
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="sortCommentDesc">
                                    <i class="fas fa-sort-amount-down"></i> {{ trans('labels.comment') }} Desc
                                </button>
                            </div>
                            <div class="btn-group mb-3 mr-3" role="group" aria-label="Sorting buttons">
                                <button type="button" class="btn btn-outline-primary" id="sortViewAsc">
                                    <i class="fas fa-sort-amount-up"></i> {{ trans('labels.view') }} Asc
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="sortViewDesc">
                                    <i class="fas fa-sort-amount-down"></i> {{ trans('labels.view') }} Desc
                                </button>
                            </div>
                            <div class="btn-group mb-3 mr-3" role="group" aria-label="Sorting buttons">
                                <button type="button" class="btn btn-outline-primary" id="sortCPMAsc">
                                    <i class="fas fa-sort-amount-up"></i> {{ trans('labels.cpm_short') }} Asc
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="sortCPMDesc">
                                    <i class="fas fa-sort-amount-down"></i> {{ trans('labels.cpm_short') }} Desc
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="contentTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="offer-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.id') }}</th>
                            <th>{{ trans('labels.influencer') }}</th>
                            <th>{{ trans('labels.platform') }}</th>
                            <th>{{ trans('labels.product') }}</th>
                            <th>{{ trans('labels.task') }}</th>
                            <th>{{ trans('labels.like') }}</th>
                            <th>{{ trans('labels.comment') }}</th>
                            <th>{{ trans('labels.view') }}</th>
                            <th data-toggle="tooltip" data-placement="top" title="{{ trans('labels.cpm') }}">
                                {{ trans('labels.cpm_short') }}
                            </th>
                            <th width="20%">{{ trans('labels.info') }}</th>
                            <th width="10%">{{ trans('labels.action') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>




@include('admin.campaign.content.modal-refresh-content')
@include('admin.campaign.content.modal-create-content')
@include('admin.campaign.content.modal-create-statistic')
@include('admin.campaign.content.modal-update-content')
@include('admin.campaign.content.modal-import-content')
@include('admin.campaign.content.modal-detail-content')
