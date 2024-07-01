@extends('adminlte::page')

@section('title', trans('labels.campaign'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div class="col-auto">
            <h1 class="mb-0">{{ $campaign->title }} : {{ $campaign->start_date }} - {{ $campaign->end_date }}</h1>
        </div>
        <div class="col-auto">
            <div class="row align-items-center">
                <div class="col-auto">
                    <input type="text" class="form-control filterDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                </div>
                <div class="col-auto">
                    <a href="{{ route('campaign.show', $campaign->id) }}" class="btn btn-outline-info">{{ trans('labels.offer') }}</a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
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
                                <a class="btn btn-success" href={{ route('statistic.bulkRefresh', $campaign->id) }}>
                                    <i class="fas fa-sync-alt"></i> {{ trans('labels.refresh') }} {{ trans('labels.all2') }}
                                </a>
                                <a class="btn btn-outline-primary" href={{ route('campaignContent.export', $campaign->id) }}>
                                    <i class="fas fa-file-download"></i> {{ trans('labels.export') }}
                                </a>
                                @can('UpdateCampaign', $campaign)
                                    <button class="btn btn-outline-success" data-toggle="modal" data-target="#contentImportModal">
                                        <i class="fas fa-file-download"></i> {{ trans('labels.import') }}
                                    </button>
                                @endcan
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
                                <th>{{ trans('labels.cpm') }}</th>
                                <th width="20%">{{ trans('labels.info') }}</th>
                                <th width="10%">{{ trans('labels.action') }}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ trans('labels.created_by') }} {{ $campaign->createdBy->name ?? '' }}

                        @can('deleteCampaign', $campaign)
                            <a href="#" class="delete-campaign">{{ trans('buttons.delete') }}</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.campaign.content.modal-create-content')
    @include('admin.campaign.content.modal-create-statistic')
    @include('admin.campaign.content.modal-update-content')
    @include('admin.campaign.content.modal-import-content')
    @include('admin.campaign.content.modal-detail-content')
@endsection

@section('js')
    <script>
        const filterDates = $('#filterDates')
        const filterInfluencer = $('#filterInfluencer')
        const filterProduct = $('#filterProduct')
        const filterPlatform = $('#filterPlatform')
        const filterFyp = $('#filterFyp')
        const filterPayment = $('#filterPayment')
        const filterDelivery = $('#filterDelivery')

        let campaignId = {{ $campaign->id }};

        // datatable
        let contentTable = $('#contentTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('campaignContent.getDataTable', ['campaignId' => ':campaignId']) }}".replace(':campaignId', campaignId),
                data: function (d) {
                    d.filterDates = filterDates.val();
                    d.filterInfluencer = filterInfluencer.val();
                    d.filterProduct = filterProduct.val();
                    d.filterPlatform = filterPlatform.val();
                    d.filterFyp = filterFyp.prop('checked');
                    d.filterPayment = filterPayment.prop('checked');
                    d.filterDelivery = filterDelivery.prop('checked');
                }
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'key_opinion_leader_username', name: 'keyOpinionLeader.username', sortable: false, orderable: false},
                {data: 'channel', name: 'channel'},
                {data: 'product', name: 'product'},
                {data: 'task_name', name: 'task_name'},
                {data: 'like', name: 'latestStatistic.like', sortable: false, orderable: false},
                {data: 'comment', name: 'latestStatistic.comment', sortable: false, orderable: false},
                {data: 'view', name: 'latestStatistic.view', sortable: false, orderable: false},
                {data: 'cpm', name: 'latestStatistic.view', sortable: false, orderable: false},
                {data: 'additional_info', sortable: false, orderable: false},
                {data: 'actions', sortable: false, orderable: false}
            ],
            columnDefs: [
                { "targets": [0], "visible": false },
                { "targets": [5], "className": "text-right" },
                { "targets": [6], "className": "text-right" },
                { "targets": [7], "className": "text-right" },
                { "targets": [8], "className": "text-right" },
                { "targets": [9], "className": "text-center" },
                { "targets": [10], "className": "text-center" },
            ],
            order: [[0, 'desc']]
        });

        // Handle row click event to open modal and fill form
        contentTable.on('draw.dt', function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        filterDates.change(function (){
            contentTable.ajax.reload()
            updateCard();
            initChart();
        })

        filterPlatform.change(function() {
            contentTable.ajax.reload()
        });

        filterFyp.change(function() {
            contentTable.ajax.reload()
        });

        filterPayment.change(function() {
            contentTable.ajax.reload()
        });

        filterDelivery.change(function() {
            contentTable.ajax.reload()
        });

        $(function() {
            const startFilter = moment('{{ $campaign->start_date }}', "DD MMMM YYYY");
            const endFilter = moment('{{ $campaign->end_date }}', "DD MMMM YYYY");

            $('.filterDate').daterangepicker({
                startDate: startFilter,
                endDate: endFilter,
                autoApply: true,
                showDropdowns: true,
                locale: {
                    format: 'DD/MM/YYYY'
                }
            });

            bsCustomFileInput.init()

            $('#username').select2({
                theme: 'bootstrap4',
                placeholder: '{{ trans('placeholder.select', ['field' => trans('labels.influencer')]) }}',
                dropdownParent: $("#contentModal"),
                ajax: {
                    url: "{{ route('campaignContent.select', ['campaignId' => ':campaignId']) }}".replace(':campaignId', campaignId),
                    data: function (params) {
                        return {
                            search: params.term,
                        };
                    },
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        let formattedResults = [];
                        data.forEach(function(result) {
                            formattedResults.push({
                                id: result.key_opinion_leader_id,
                                text: result.key_opinion_leader.username + ' - ' +result.key_opinion_leader.channel+ ' - {{ trans('labels.remaining_slot') }}:'+ result.remaining_slot
                            });
                        });

                        return {
                            results: formattedResults
                        };
                    },
                    cache: true
                }
            });
        })
    </script>

    @include('admin.campaign.content.script.script-button-info')
    @include('admin.campaign.content.script.script-refresh')
    @include('admin.campaign.content.script.script-manual-statistic')
    @include('admin.campaign.content.script.script-add-content')
    @include('admin.campaign.content.script.script-update-content')
    @include('admin.campaign.content.script.script-import')
    @include('admin.campaign.content.script.script-detail-content')
    @include('admin.campaign.content.script.script-card')
    @include('admin.campaign.content.script.script-chart')
    @include('admin.campaign.content.script.script-delete-content')
@endsection
