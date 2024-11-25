@extends('adminlte::page')

@section('title', trans('labels.contest'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $contest->title }}</h1>
        @auth
            <div>
                <a href="{{ route('contest.public', $contest) }}" target="_blank" class="btn btn-primary">
                    Public Page
                </a>
            </div>
        @endauth
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 col-6">
                            <h5>Sisa Anggaran: {{ $contest->remaining_budget_formatted }} ({{ $contest->remaining_percentage }}%)</h5>
                        </div>
                        <div class="col-lg-6 col-6">
                            <h5>Update terakhir: {{ $contest->last_update }}</h5>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-6 col-6">
                            <div class="progress">
                                <div class="progress-bar bg-primary progress-bar-striped"
                                     role="progressbar"
                                     aria-valuenow="40"
                                     aria-valuemin="0"
                                     aria-valuemax="100"
                                     style="width: {{ $contest->remaining_percentage }}%">
                                    <span class="sr-only">40% Complete (success)</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-6">

                        </div>
                    </div>

                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4>{{ $contest->total_creator }}</h4>
                                    <p>Total Kreator</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-6">
                            <div class="small-box bg-purple">
                                <div class="inner">
                                    <h4>{{ $contest->total_content }}</h4>
                                    <p>Total Konten</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4>{{ number_format($contest->cumulative_views, '0', ',', '.') }}</h4>
                                    <p>Tayangan Kumulatif</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-6">
                            <div class="small-box bg-teal">
                                <div class="inner">
                                    <h4>{{ $contest->interaction ?? 0 }}%</</h4>
                                    <p>Tingkat Interaksi</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-area"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                @auth
                    <div class="card-body">
                        <div class="row ">
                            <div class="col-md-12 d-flex justify-content-end">
                                <div class="col-auto">
                                    <input type="text" class="form-control rangeDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                                </div>
                                <div class="btn-group">
                                    <a href="{{ route('contestContent.create', $contest) }}" type="button" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                                    </a>
                                    <a class="btn btn-success" href="{{ route('contestContent.bulkRefresh', $contest) }}">
                                        <i class="fas fa-sync-alt"></i> {{ trans('labels.refresh') }} {{ trans('labels.all2') }}
                                    </a>
                                    <a class="btn btn-info" href="{{ route('contestContent.export', $contest) }}">
                                        <i class="fas fa-file-export"></i> {{ trans('labels.export') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth
                <div class="card-body">
                    <table id="contentTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="kol-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.created_at') }}</th>
                                <th>{{ trans('labels.username') }}</th>
                                <th>{{ trans('labels.view') }}</th>
                                <th>{{ trans('labels.like') }}</th>
                                <th>{{ trans('labels.comment') }}</th>
                                <th>{{ trans('labels.share') }}</th>
                                <th>{{ trans('labels.interaction') }}</th>
                                <th width="15%">{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.contest.content.modal-detail-content')
@endsection

@section('js')
    <script>
        const contentTableSelector = $('#contentTable');
        const filterDate = $('#filterDates');

        // Initialize date range picker
        filterDate.daterangepicker({
            locale: {
                format: 'DD/MM/YYYY'
            }
        });

        // datatable
        let contentTable = contentTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('contestContent.get-recap', $contest) }}",
                data: function(d) {
                    d.filterDates = filterDate.val();
                }
            },
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'username_link', name: 'username'},
                {data: 'view_formatted', name: 'view'},
                {data: 'like_formatted', name: 'like'},
                {data: 'comment_formatted', name: 'comment'},
                {data: 'share_formatted', name: 'share'},
                {data: 'interaction_formatted', name: 'interaction'},
                {data: 'actions', sortable: false, orderable: false}
            ],
            columnDefs: [
                { "targets": [0], "visible": false },
                { "targets": [2, 3, 4, 5, 6], "className": "text-right" },
                { "targets": [7], "className": "text-center" }
            ],
            order: [[0, 'desc']]
        });

        filterDate.change(function() {
            contentTable.draw();
        });

        // Handle row click event to open modal and fill form
        contentTable.on('draw.dt', function() {
            const tableBodySelector =  $('#contentTable tbody');

            tableBodySelector.on('click', '.deleteButton', function() {
                let rowData = contentTable.row($(this).closest('tr')).data();
                let route = '{{ route('contestContent.destroy', ':id') }}';
                deleteAjax(route, rowData.id, contentTable);
            });
        });

        $(document).on('click', '.btnRefresh', refresh());

        function refresh () {
            return function () {
                let rowData = contentTable.row($(this).closest('tr')).data();

                $.ajax({
                    type: 'GET',
                    url: "{{ route('contestContent.refresh', ['contestContent' => ':contestContentId']) }}".replace(':contestContentId', rowData.id),
                    success: function(response) {
                        contentTable.ajax.reload();
                        toastr.success('{{ trans('messages.refresh_success') }}');
                    },
                    error: function(xhr, status, error) {
                        toastr.error('{{ trans('messages.refresh_failed') }}');
                    }
                });
            }
        }

        $(document).on('click', '.btnDetail', detailContent());

        function detailContent() {
            return function () {
                let rowData = contentTable.row($(this).closest('tr')).data();

                $('#likeModal').text(rowData.like_formatted);
                $('#viewModal').text(rowData.view_formatted);
                $('#commentModal').text(rowData.comment_formatted);
                $('#shareModal').text(rowData.shared_formatted);
                $('#interactionModal').text(rowData.interaction_formatted);
                $('#rateCardModal').text(rowData.rate_formatted);
                $('#rateTotalCardModal').text(rowData.rate_total_formatted);
                $('#durationModal').text(rowData.human_duration);

                if (rowData.upload_date !== null) {
                    $('#uploadDateModal').text(rowData.upload_date);
                }

                if (rowData.link !== '') {
                    $.ajax({
                        url: "https://www.tiktok.com/oembed?url=" + rowData.link,
                        type: 'GET',
                        success: function (response) {
                            $('#contentEmbed').html(response.html)
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                }

                $('#detailModal').modal('show');
            }
        }
    </script>
@endsection
