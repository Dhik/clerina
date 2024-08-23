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
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto">
                                    <input type="text" class="form-control rangeDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Summary Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4 id="totalExpense">0</h4>
                            <p>Total Expense</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h4 id="totalContent">0</h4>
                            <p>Total Content</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-video"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4 id="cpm">0</h4>
                            <p>CPM</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h4 id="views">0</h4>
                            <p>Total Views</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTables -->
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
        </div>
    </div>
@stop

@section('js')
    <script>
        let campaignTableSelector = $('#campaignTable');
        let filterDate = $('#filterDates');

        // Initialize Date Range Picker
        filterDate.daterangepicker({
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY'
            }
        });

        $('#resetFilterBtn').click(function () {
            filterDate.val('');
            loadCampaignSummary();
            campaignTable.ajax.url("{{ route('campaign.get') }}").load();
        });

        filterDate.change(function () {
            loadCampaignSummary(filterDate.val());
            campaignTable.ajax.url("{{ route('campaign.get') }}?filterDates=" + filterDate.val()).load();
        });

        // Load the summary data with an optional date range and search term
        function loadCampaignSummary(dateRange = '', searchTerm = '') {
            $.ajax({
                url: "{{ route('campaign.summary') }}",
                method: 'GET',
                data: { 
                    filterDates: dateRange,
                    search: searchTerm
                },
                success: function(response) {
                    $('#totalExpense').text(response.total_expense);
                    $('#totalContent').text(response.total_content);
                    $('#cpm').text(response.cpm);
                    $('#views').text(response.views);
                },
                error: function(response) {
                    console.error('Error fetching campaign summary:', response);
                }
            });
        }

        // Initialize DataTables
        let campaignTable = campaignTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('campaign.get') }}",
                data: function (d) {
                    d.filterDates = filterDate.val();
                }
            },
            columns: [
                {data: 'created_at', name: 'created_at'},
                {
                    data: 'title',
                    name: 'title',
                    render: function(data, type, row) {
                        return '<a href="/admin/campaign/' + row.id + '/show">' + data + '</a>';
                    }
                },
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
                { "targets": [6], "visible": false },
                { "targets": [7], "className": "text-center" }
            ],
            order: [[0, 'desc']]
        });

        // Listen for search event
        campaignTableSelector.on('search.dt', function() {
            let searchTerm = campaignTable.search();
            loadCampaignSummary(filterDate.val(), searchTerm);
        });

        // Handle bulk refresh button click
        $('#bulkRefreshBtn').click(function () {
            let refreshBtn = $(this);
            let refreshText = $('#bulkRefreshText');
            let refreshLoading = $('#bulkRefreshLoading');

            // Show loading animation
            refreshText.addClass('d-none');
            refreshLoading.removeClass('d-none');

            $.ajax({
                url: "{{ route('campaign.bulkRefresh') }}",
                method: 'GET',
                success: function(response) {
                    // Hide loading animation and show success message
                    refreshText.removeClass('d-none');
                    refreshLoading.addClass('d-none');
                    window.location.reload();
                },
                error: function(response) {
                    console.error('Error refreshing campaigns:', response);
                    refreshText.removeClass('d-none');
                    refreshLoading.addClass('d-none');
                }
            });
        });

        // Load the initial summary data
        loadCampaignSummary();
    </script>
@stop
