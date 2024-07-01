@extends('adminlte::page')

@section('title', trans('labels.contest'))

@section('content_header')
    <h1>{{ trans('labels.contest') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <div class="btn-group">
                                <a href="{{ route('contest.create') }}" type="button" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="contestTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="kol-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.created_at') }}</th>
                            <th>{{ trans('labels.title') }}</th>
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
        const contestTableSelector = $('#contestTable');

        // datatable
        let contestTable = contestTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('contest.get') }}",
            },
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'title', name: 'title'},
                {data: 'actions', sortable: false, orderable: false}
            ],
            columnDefs: [
                { "targets": [0], "visible": false },
                { "targets": [2], "className": "text-center" }
            ],
            order: [[0, 'desc']]
        });

        // Handle row click event to open modal and fill form
        contestTableSelector.on('draw.dt', function() {
            const tableBodySelector =  $('#contestTable tbody');

            tableBodySelector.on('click', '.deleteButton', function() {
                let rowData = contestTable.row($(this).closest('tr')).data();
                let route = '{{ route('contest.destroy', ':id') }}';
                deleteAjax(route, rowData.id, contestTable);
            });
        });
    </script>
@stop
