@extends('adminlte::page')

@section('title', trans('labels.key_opinion_leader'))

@section('content_header')
    <h1>Account</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <div class="row mb-2">
                                <div class="col-auto mb-2">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <div class="card">
                <!-- <div class="card-body">
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
                </div> -->
                <div class="card-body">
                    <table id="kolTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="kol-info" width="100%">
                        <thead>
                        <tr>
                            <th>id</th>
                            <th>{{ trans('labels.channel') }}</th>
                            <th>{{ trans('labels.username') }}</th>
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
                {data: 'actions', sortable: false, orderable: false}
            ],
            order: [[0, 'desc']]
        });

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
