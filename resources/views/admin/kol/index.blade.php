@extends('adminlte::page')

@section('title', trans('labels.key_opinion_leader'))

@section('content_header')
    <h1>{{ trans('labels.key_opinion_leader') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <div class="row mb-2">
                                <div class="col-auto mb-2">
                                    <select class="form-control" id="filterChannel">
                                        <option value="" selected>{{ trans('placeholder.select_channel') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($channels as $channel)
                                            <option value={{ $channel }}>{{ ucfirst($channel) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto mb-2">
                                    <select class="form-control" id="filterNiche">
                                        <option value="" selected>{{ trans('placeholder.select_niche') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($niches as $niche)
                                            <option value={{ $niche }}>{{ ucfirst($niche) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto mb-2">
                                    <select class="form-control" id="filterSkinType">
                                        <option value="" selected>{{ trans('placeholder.select_skin_type') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($skinTypes as $skinType)
                                            <option value={{ $skinType }}>{{ ucfirst($skinType) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto mb-2">
                                    <select class="form-control" id="filterSkinConcern">
                                        <option value="" selected>{{ trans('placeholder.select_skin_concern') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($skinConcerns as $skinConcern)
                                            <option value={{ $skinConcern }}>{{ ucfirst($skinConcern) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto mb-2">
                                    <select class="form-control" id="filterContentType">
                                        <option value="" selected>{{ trans('placeholder.select_content_type') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($contentTypes as $contentType)
                                            <option value={{ $contentType }}>{{ ucfirst($contentType) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto mb-2">
                                    <select class="form-control" id="filterPIC">
                                        <option value="" selected>{{ trans('placeholder.select_pic') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($marketingUsers as $marketingUser)
                                            <option value={{ $marketingUser->id }}>{{ $marketingUser->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto mb-2">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <a href="{{ route('kol.create') }}" type="button" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                                </a>
                                <a href="{{ route('kol.create-excel') }}" type="button" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> {{ trans('labels.excel') }}
                                </a>
                                <button type="button" class="btn btn-primary" id="btnExportKol">
                                    <i class="fas fa-file-download"></i> {{ trans('labels.export') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="kolTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="kol-info" width="100%">
                        <thead>
                        <tr>
                            <th>id</th>
                            <th>{{ trans('labels.channel') }}</th>
                            <th>{{ trans('labels.username') }}</th>
                            <th>{{ trans('labels.niche') }}</th>
                            <th>{{ trans('labels.skin_type') }}</th>
                            <th>{{ trans('labels.skin_concern') }}</th>
                            <th>{{ trans('labels.content_type') }}</th>
                            <th>{{ trans('labels.pic_contact') }}</th>
                            <th width="10%">{{ trans('labels.action') }}</th>
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
        const kolTableSelector = $('#kolTable');
        const channelSelector = $('#filterChannel');
        const nicheSelector = $('#filterNiche');
        const skinTypeSelector = $('#filterSkinType');
        const skinConcernSelector = $('#filterSkinConcern');
        const contentTypeSelector = $('#filterContentType');
        const picSelector = $('#filterPIC');
        const btnExportKol = $('#btnExportKol');

        // datatable
        let kolTable = kolTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('kol.get') }}",
                data: function (d) {
                    d.channel = channelSelector.val();
                    d.niche = nicheSelector.val();
                    d.skinType = skinTypeSelector.val();
                    d.skinConcern = skinConcernSelector.val();
                    d.contentType = contentTypeSelector.val();
                    d.pic = picSelector.val();
                }
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'channel', name: 'channel'},
                {data: 'username', name: 'username'},
                {data: 'niche', name: 'niche'},
                {data: 'skin_type', name: 'skin_type'},
                {data: 'skin_concern', name: 'skin_concern'},
                {data: 'content_type', name: 'content_type'},
                {data: 'pic_contact_name', name: 'picContact.name'},
                {data: 'actions', sortable: false, orderable: false}
            ],
            columnDefs: [
                { "targets": [0], "visible": false },
                {
                    targets: [1, 3, 4, 5, 6],
                    render: function(data, type, row, meta) {
                        if (type === 'display') {
                            return data.charAt(0).toUpperCase() + data.slice(1); // Convert the data to uppercase
                        }
                        return data;
                    }
                }
            ],
            order: [[0, 'desc']]
        });

        $('#filterChannel, #filterNiche, #filterSkinType, #filterSkinConcern, #filterContentType, #filterPIC').change(function() {
            kolTable.draw()
        });

        $('#resetFilterBtn').click(function () {
            channelSelector.val('');
            nicheSelector.val('');
            skinTypeSelector.val('');
            skinConcernSelector.val('');
            contentTypeSelector.val('');
            picSelector.val('');
            kolTable.draw()
        })

        btnExportKol.click(function () {
            let data = {
                channel: channelSelector.val(),
                niche: nicheSelector.val(),
                skinType: skinTypeSelector.val(),
                skinConcern: skinConcernSelector.val(),
                contentType: contentTypeSelector.val(),
                pic: picSelector.val()
            };

            let spinner = $('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>'); // Create spinner element

            // Disable the submit button to prevent multiple submissions
            btnExportKol.prop('disabled', true).append(spinner);

            let now = moment();
            let formattedTime = now.format('YYYYMMDD-HHmmss');

            // Make an AJAX GET request
            $.ajax({
                url: "{{ route('kol.export') }}",
                type: "GET",
                data: data,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(response);
                    link.download = 'KOL-' + formattedTime + '.xlsx';
                    link.click();

                    btnExportKol.prop('disabled', false);
                    spinner.remove();
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);

                    btnExportKol.prop('disabled', false);
                    spinner.remove();
                }
            });
        });

        $(function () {
            kolTable.draw()
        });
    </script>
@stop
