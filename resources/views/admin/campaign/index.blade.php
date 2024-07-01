@extends('adminlte::page')

@section('title', trans('labels.campaign'))

@section('content_header')
    <h1>{{ trans('labels.campaign') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            @can(\App\Domain\User\Enums\PermissionEnum::CreateCampaign)
                                <div class="btn-group">
                                    <a href="{{ route('campaign.create') }}" type="button" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="campaignTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="kol-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.created_at') }}</th>
                            <th>{{ trans('labels.title') }}</th>
                            <th>{{ trans('labels.total_spend') }}</th>
                            <th data-toggle="tooltip" data-placement="top" title="{{ trans('labels.cpm') }}">{{ trans('labels.cpm_short') }}</th>
                            <th>{{ trans('labels.views') }}</th>
                            <th>{{ trans('labels.period') }}</th>
                            <th>{{ trans('labels.created_by') }}</th>
                            <th width="15%">{{ trans('labels.action') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        const campaignTableSelector = $('#campaignTable');

        $('[data-toggle="tooltip"]').tooltip();

        // datatable
        let campaignTable = campaignTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('campaign.get') }}",
            },
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'title', name: 'title'},
                {data: 'expense_formatted', name: 'total_expense', searchable: false},
                {data: 'cpm_formatted', name: 'cpm', searchable: false},
                {data: 'views_formatted', name: 'view', searchable: false},
                {data: 'period', name: 'period', sortable: false, orderable: false, searchable: false},
                {data: 'created_by_name', name: 'created_by_name'},
                {data: 'actions', sortable: false, orderable: false}
            ],
            columnDefs: [
                { "targets": [0], "visible": false },
                { "targets": [2], "className": "text-right" },
                { "targets": [3], "className": "text-right" },
                { "targets": [4], "className": "text-right" },
                { "targets": [5], "className": "text-center" },
                { "targets": [7], "className": "text-center" }
            ],
            order: [[0, 'desc']]
        });
    </script>
@stop
