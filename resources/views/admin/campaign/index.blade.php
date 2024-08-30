@extends('adminlte::page')

@section('title', trans('labels.campaign'))

@section('content_header')
    <h1>{{ trans('labels.campaign') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">

            <!-- DataTables -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto">
                                @can(\App\Domain\User\Enums\PermissionEnum::CreateCampaign)
                                        <div class="btn-group">
                                            <a href="{{ route('campaign.create') }}" type="button" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                                <div class="col-auto">
                                    @can(\App\Domain\User\Enums\PermissionEnum::UpdateCampaign)
                                        <div class="btn-group">
                                            <button id="bulkRefreshBtn" type="button" class="btn btn-success">
                                                <i class="fas fa-sync-alt"></i> {{ trans('labels.bulk_refresh') }}
                                                <span id="bulkRefreshLoading" class="spinner-border spinner-border-sm d-none"></span>
                                            </button>
                                        </div>
                                    @endcan
                                </div>

                                <div class="col-auto">
                                <input type="month" class="form-control" id="filterMonth" placeholder="{{ trans('placeholder.select_month') }}" autocomplete="off">
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                        </div>
                        <div class="card-body">
                        <div class="row">
                <!-- Summary Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4 id="kpi_total_expense">0</h4>
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
                            <h4 id="kpi_total_content">0</h4>
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
                            <h4 id="kpi_cpm">0</h4>
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
                                <!-- <tfoot>
                                <tr>
                                    <th colspan="2"></th>
                                    <th>Total Expense</th>
                                    <th>CPM</th>
                                    <th>Views</th>
                                    <th colspan="3"></th>
                                </tr>
                                </tfoot> -->
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
    let filterMonth = $('#filterMonth');

    // Initialize DataTables
    let campaignTable = campaignTableSelector.DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('campaign.get') }}",
            data: function (d) {
                if (filterMonth.val()) {
                    d.filterMonth = filterMonth.val();
                } else {
                    delete d.filterMonth; // Remove the filterMonth parameter when not used
                }
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
            {
                data: 'total_expense',
                name: 'total_expense',
                render: function(data) {
                    return new Intl.NumberFormat('en-US').format(data);
                },
                searchable: false
            },
            {
                data: 'cpm',
                name: 'cpm',
                render: function(data) {
                    return new Intl.NumberFormat('en-US').format(data);
                },
                searchable: false
            },
            {
                data: 'view',
                name: 'view',
                render: function(data) {
                    return new Intl.NumberFormat('en-US').format(data);
                },
                searchable: false
            },
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
        order: [[0, 'desc']],
    });

    // Event handler for table draw event
    campaignTable.on('draw.dt', function() {
        let tableBodySelector = $('#campaignTable tbody');

        // Delete button click event
        tableBodySelector.on('click', '.deleteButton', function() {
            let rowData = campaignTable.row($(this).closest('tr')).data();
            let route = '{{ route('campaign.destroy', ':id') }}'.replace(':id', rowData.id);

            deleteAjax(route, rowData.id, campaignTable);
        });
    });

    // Reset Filter Button Click
    $('#resetFilterBtn').click(function () {
        filterMonth.val(''); // Clear the month filter input
        campaignTable.search(''); // Clear the search term
        campaignTable.ajax.reload(); // Reload DataTables without filter
        loadCampaignSummary(''); // Reload summary without filter and search term
    });

    // When the filter changes, reload the DataTables with the new filter
    filterMonth.change(function () {
        let searchTerm = campaignTable.search(); // Capture the current search term
        campaignTable.ajax.reload(); // Reload DataTables with filter
        loadCampaignSummary(filterMonth.val(), searchTerm); // Reload summary with the same filter and search term
    });

    // Listen for search event and update the summary accordingly
    campaignTableSelector.on('search.dt', function () {
        let searchTerm = campaignTable.search(); // Capture the current search term
        loadCampaignSummary(filterMonth.val(), searchTerm); // Reload summary with the filter and search term
    });

    // Load the summary data with an optional month and search term
    function loadCampaignSummary(month = '', searchTerm = '') {
        $.ajax({
            url: "{{ route('campaign.summary') }}",
            method: 'GET',
            data: { 
                filterMonth: month,
                search: searchTerm
            },
            success: function(response) {
                // Update the KPI cards with formatted numbers
                $('#kpi_total_expense').text(response.total_expense);
                $('#kpi_cpm').text(response.cpm);
                $('#views').text(response.views);
                $('#kpi_total_content').text(response.total_content);
            },
            error: function(response) {
                console.error('Error fetching campaign summary:', response);
            }
        });
    }

    // Initial Load
    loadCampaignSummary();

    function deleteAjax(route, id, table) {
    Swal.fire({
        title: '{{ trans('labels.are_you_sure') }}',
        text: '{{ trans('labels.not_be_able_to_recover') }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '{{ trans('buttons.confirm_swal') }}',
        cancelButtonText: '{{ trans('buttons.cancel_swal') }}',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: route,
                type: 'DELETE', // This ensures the correct HTTP method is used
                data: {
                    _token: '{{ csrf_token() }}' // Include the CSRF token
                },
                success: function (response) {
                    Swal.fire(
                        '{{ trans('labels.success') }}',
                        '{{ trans('messages.success_delete') }}',
                        'success'
                    ).then(() => {
                        table.ajax.reload(); // Reload the DataTable after successful deletion
                    });
                },
                error: function (xhr, status, error) {
                    if (xhr.status === 422) {
                        Swal.fire(
                            '{{ trans('labels.failed') }}',
                            xhr.responseJSON.message,
                            'error'
                        );
                    } else {
                        Swal.fire(
                            '{{ trans('labels.failed') }}',
                            '{{ trans('messages.error_delete') }}',
                            'error'
                        );
                    }
                }
            });
        }
    });
}

// Bulk Refresh Button Click
$('#bulkRefreshBtn').click(function () {
        $('#bulkRefreshLoading').removeClass('d-none'); // Show the loading spinner
        $.ajax({
            url: "{{ route('campaign.bulkRefresh') }}",
            method: 'GET',
            success: function(response) {
                $('#bulkRefreshLoading').addClass('d-none'); // Hide the loading spinner
                Swal.fire(
                    '{{ trans('labels.success') }}',
                    '{{ trans('messages.success_bulk_update', ['model' => trans('labels.campaign')]) }}',
                    'success'
                ).then(() => {
                    campaignTable.ajax.reload(); // Reload the DataTable after the refresh
                    loadCampaignSummary(); // Reload the summary after the refresh
                });
            },
            error: function(response) {
                $('#bulkRefreshLoading').addClass('d-none'); // Hide the loading spinner
                Swal.fire(
                    '{{ trans('labels.failed') }}',
                    '{{ trans('messages.error_bulk_update') }}',
                    'error'
                );
            }
        });
    });

</script>
@stop
