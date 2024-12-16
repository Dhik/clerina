@extends('adminlte::page')

@section('title', trans('labels.sales'))

@section('content_header')
    <h1>{{ trans('labels.sales') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-auto">
                                    <input type="text" class="form-control rangeDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" id="filterChannel">
                                        <option value="" selected>{{ trans('placeholder.select_sales_channel') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($salesChannels as $salesChannel)
                                            <option value={{ $salesChannel->id }}>{{ $salesChannel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary" id="refreshDataBtn">
                                        <i class="fas fa-sync-alt"></i> Refresh Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4 id="newSalesCount">0</h4>
                            <p>Total Sales</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h4 id="newVisitCount">0</h4>
                            <p>Total Visit</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4 id="newOrderCount">0</h4>
                            <p>Total Order</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger" id="totalSpentCard" style="cursor: pointer;">
                        <div class="inner">
                            <h4 id="newAdSpentCount">0</h4>
                            <p>Total Spent</p>
                            <p id="newCampaignExpense" style="display: none;">Campaign Expense: 0</p>
                            <p id="newAdsSpentTotal" style="display: none;">Total Ads Spent: 0</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-teal">
                        <div class="inner">
                            <h4 id="newRoasCount">0</h4>
                            <p>{{ trans('labels.roas') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-area"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-pink">
                        <div class="inner">
                            <h4 id="newClosingRateCount">0</h4>
                            <p>{{ trans('labels.closing_rate') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-maroon">
                        <div class="inner">
                            <h4 id="newQtyCount">0</h4>
                            <p>Qty</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-orange">
                        <div class="inner">
                            <h4 id="newCPACount">0</h4>
                            <p>CPA</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-area"></i>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.sales.recap-card')
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#visitModal" id="btnAddVisit">
                                    <i class="fas fa-plus"></i> {{ trans('labels.visit') }}
                                </button>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#adSpentSocialMediaModal" id="btnAddAdSpentSM">
                                    <i class="fas fa-plus"></i> {{ trans('labels.ad_spent_social_media') }}
                                </button>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#adSpentMarketPlaceModal" id="btnAddAdSpentMP">
                                    <i class="fas fa-plus"></i> {{ trans('labels.ad_spent_market_place') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="salesTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="order-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.date') }}</th>
                            <th>{{ trans('labels.visit') }}</th>
                            <th>{{ trans('labels.qty') }}</th>
                            <th>{{ trans('labels.order') }}</th>
                            <th>{{ trans('labels.closing_rate') }}</th>
                            <th>{{ trans('labels.ad_spent_social_media') }}</th>
                            <th>{{ trans('labels.ad_spent_market_place') }}</th>
                            <th>{{ trans('labels.spend_total') }}</th>
                            <th>{{ trans('labels.roas') }}</th>
                            <th>{{ trans('labels.turnover') }} ({{ trans('labels.rp') }})</th>
                            <th>{{ trans('labels.action') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.visit.modal')
    @include('admin.adSpentSocialMedia.modal')
    @include('admin.adSpentMarketPlace.modal')
    @include('admin.sales.modal-visitor')
    @include('admin.sales.modal-omset')

    <!-- Omset Modal -->
    <div class="modal fade" id="omsetModal" tabindex="-1" role="dialog" aria-labelledby="omsetModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 80%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="omsetModalLabel">{{ trans('labels.turnover') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('buttons.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="orderTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="order-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.order_id') }}</th>
                                <th>{{ trans('labels.customer_name') }}</th>
                                <th>{{ trans('labels.customer_phone_number') }}</th>
                                <th>{{ trans('labels.product') }}</th>
                                <th>{{ trans('labels.qty') }}</th>
                                <th>{{ trans('labels.amount') }}</th>
                                <th>{{ trans('labels.payment_method') }}</th>
                                <th>{{ trans('labels.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Order data will be dynamically populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Spent Modal -->
    <div class="modal fade" id="detailSpentModal" tabindex="-1" role="dialog" aria-labelledby="detailSpentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 40%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailSpentModalLabel">Detail Spent</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="modalCampaignExpense">Campaign Expense: 0</p>
                    <p id="modalAdsSpentTotal">Total Ads Spent: 0</p>
                    <p id="modalTotalSpent">Total Spent: 0</p>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
    <script>
        salesTableSelector = $('#salesTable');
        filterDate = $('#filterDates');
        filterChannel = $('#filterChannel');

        $('#btnAddVisit').click(function() {
            $('#dateVisit').val(moment().format("DD/MM/YYYY"));
        });

        $('#btnAddAdSpentSM').click(function() {
            $('#dateAdSpentSocialMedia').val(moment().format("DD/MM/YYYY"));
        });

        $('#btnAddAdSpentMP').click(function() {
            $('#dateAdSpentMarketPlace').val(moment().format("DD/MM/YYYY"));
        });

        $('#resetFilterBtn').click(function () {
            filterDate.val('')
            filterChannel.val('')
            updateRecapCount()
            salesTable.draw()
        })

        filterDate.change(function () {
            salesTable.draw()
            updateRecapCount()
        });

        filterChannel.change(function () {
            salesTable.draw()
            updateRecapCount()
        });

        function updateRecapCount() {
            $.ajax({
                url: '{{ route('sales.get-sales-recap') }}?filterDates=' + filterDate.val() + '&filterChannel=' + filterChannel.val(),
                method: 'GET',
                success: function(response) {
                    // Update the count with the retrieved value
                    $('#newSalesCount').text(response.total_sales);
                    $('#newVisitCount').text(response.total_visit);
                    $('#newOrderCount').text(response.total_order);
                    $('#newAdSpentCount').text(response.total_ad_spent);
                    $('#newQtyCount').text(response.total_qty);
                    $('#newRoasCount').text(response.total_roas);
                    $('#newClosingRateCount').text(response.closing_rate);
                    $('#newCPACount').text(response.cpa);
                    $('#newCampaignExpense').text(response.campaign_expense);
                    $('#newAdsSpentTotal').text(response.total_ads_spent);
                    generateChart(response);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching new orders count:', error);
                }
            });
        }

        // datatable
        let salesTable = salesTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('sales.get') }}",
                data: function (d) {
                    d.filterDates = filterDate.val()
                }
            },
            columns: [
                {data: 'date', name: 'date'},
                {data: 'visitFormatted', name: 'visit', sortable: false},
                {data: 'qtyFormatted', name: 'qty', sortable: false},
                {data: 'orderFormatted', name: 'order', sortable: false},
                {data: 'closingRateFormatted', name: 'closing_rate', sortable: false},
                {data: 'adSpentSocialMediaFormatted', name: 'ad_spent_social_media', sortable: false},
                {data: 'adSpentMarketPlaceFormatted', name: 'ad_spent_market_place', sortable: false},
                {data: 'totalFormatted', name: 'total_spend', sortable: false},
                {data: 'roasFormatted', name: 'roas', sortable: false},
                {data: 'adSpentTotalFormatted', name: 'total_spend', sortable: false},
                {data: 'actions', sortable: false}
            ],
            columnDefs: [
                { "targets": [1], "className": "text-right" },
                { "targets": [2], "className": "text-right" },
                { "targets": [3], "className": "text-right" },
                { "targets": [4], "className": "text-right" },
                { "targets": [5], "className": "text-right" },
                { "targets": [6], "className": "text-right" },
                { "targets": [7], "className": "text-right" },
                { "targets": [8], "className": "text-center" }
            ],
            order: [[0, 'desc']]
        });

        // Handle row click event to open modal and fill form
        salesTable.on('draw.dt', function() {
            const tableBodySelector =  $('#salesTable tbody');

            tableBodySelector.on('click', '.visitButtonDetail', function(event) {
                event.preventDefault();
                let rowData = salesTable.row($(this).closest('tr')).data();
                showVisitorDetail(rowData);
            });

            tableBodySelector.on('click', '.omsetButtonDetail', function(event) {
                event.preventDefault();
                let rowData = salesTable.row($(this).closest('tr')).data();
                showOmsetDetail(rowData);
            });

            tableBodySelector.on('click', '.omset-link', function(event) {
                event.preventDefault();
                let date = $(this).data('date');
                showOmsetDetail(date);
            });
        });

        function showVisitorDetail(data) {
            $.ajax({
                url: "{{ route('visit.getByDate') }}?date=" + data.date,
                type: 'GET',
                success: function(response) {
                    let visitTableBody = $("#visit-table-body");
                    visitTableBody.empty(); // Clear existing rows

                    if (response.length > 0) {
                        response.forEach(function(item) {
                            let row = `<tr>
                            <td>${item.sales_channel.name ?? ''}</td>
                            <td>${item.visit_amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}</td>
                        </tr>`;
                            visitTableBody.append(row);
                        });
                    } else {
                        let row = `<tr><td colspan="2" class="text-center">{{ trans('messages.no_data') }}</td></tr>`;
                        visitTableBody.append(row);
                    }

                    $('#showVisitorModal').modal('show');
                },
                error: function(error) {
                    console.log(error);
                    alert("An error occurred");
                }
            });
        }

        function showOmsetDetail(data) {
            $.ajax({
                url: "{{ route('order.getOrdersByDate') }}?date=" + data.date,
                type: 'GET',
                success: function(response) {
                    let omsetTableBody = $("#omset-table-body");
                    omsetTableBody.empty(); // Clear existing rows

                    if (response.length > 0) {
                        response.forEach(function(item) {
                            let row = `<tr>
                            <td>${item.sales_channel ?? ''}</td>
                            <td>${item.total_amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}</td>
                        </tr>`;
                            omsetTableBody.append(row);
                        });
                    } else {
                        let row = `<tr><td colspan="2" class="text-center">{{ trans('messages.no_data') }}</td></tr>`;
                        omsetTableBody.append(row);
                    }
                    $('#showOmsetModal').modal('show');
                },
                error: function(error) {
                    console.log(error);
                    alert("An error occurred");
                }
            });
        }

        // Click event for the Total Spent card
        $('#totalSpentCard').click(function() {
            const campaignExpense = $('#newCampaignExpense').text().trim();
            const adsSpentTotal = $('#newAdsSpentTotal').text().trim();
            const totalSpent = $('#newAdSpentCount').text().trim();
            console.log(campaignExpense);
            console.log(adsSpentTotal);
            console.log(totalSpent);

            // Update modal content
            $('#modalCampaignExpense').text('Campaign Expense: ' + campaignExpense);
            $('#modalAdsSpentTotal').text('Total Ads Spent: ' + adsSpentTotal);
            $('#modalTotalSpent').text('Total Spent: ' + totalSpent);

            // Show the modal
            $('#detailSpentModal').modal('show');
        });

        $(function () {
            salesTable.draw();
            updateRecapCount();
            $('[data-toggle="tooltip"]').tooltip(); // Initialize tooltips
        });

        function showLoadingSwal(message) {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        $('#refreshDataBtn').click(function () {
            showLoadingSwal('Refreshing data, please wait...');

            $.ajax({
                url: "{{ route('order.fetch-all') }}",
                method: 'GET',
                success: function(response) {
                    console.log('Orders fetched and saved successfully');

                    // Call the updateSalesTurnover route after orders are fetched
                    $.ajax({
                        url: "{{ route('order.update_turnover') }}", // Route for updateSalesTurnover
                        method: 'GET',
                        success: function(response) {
                            console.log('Sales turnover updated successfully');

                            // Proceed with the rest of the data import/update process
                            $.ajax({
                                url: "{{ route('sales.import_ads') }}",
                                method: 'GET',
                                success: function(response) {
                                    $.ajax({
                                        url: "{{ route('sales.update_ads') }}",
                                        method: 'GET',
                                        success: function(response) {
                                            $.ajax({
                                                url: "{{ route('visit.import_cleora') }}",
                                                method: 'GET',
                                                success: function(response) {
                                                    $.ajax({
                                                        url: "{{ route('visit.import_azrina') }}",
                                                        method: 'GET',
                                                        success: function(response) {
                                                            $.ajax({
                                                                url: "{{ route('visit.update') }}",
                                                                method: 'GET',
                                                                success: function(response) {
                                                                    Swal.fire({
                                                                        icon: 'success',
                                                                        title: 'Data refreshed successfully!',
                                                                        text: 'All data has been imported and updated.',
                                                                        timer: 2000,
                                                                        showConfirmButton: false
                                                                    });
                                                                    updateRecapCount();
                                                                    salesTable.draw();
                                                                },
                                                                error: function(xhr, status, error) {
                                                                    Swal.fire({
                                                                        icon: 'error',
                                                                        title: 'Error updating monthly visit data!',
                                                                        text: xhr.responseJSON?.message || 'An error occurred.',
                                                                    });
                                                                }
                                                            });
                                                        },
                                                        error: function(xhr, status, error) {
                                                            Swal.fire({
                                                                icon: 'error',
                                                                title: 'Error importing Azrina data!',
                                                                text: xhr.responseJSON?.message || 'An error occurred.',
                                                            });
                                                        }
                                                    });
                                                },
                                                error: function(xhr, status, error) {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Error importing Cleora data!',
                                                        text: xhr.responseJSON?.message || 'An error occurred.',
                                                    });
                                                }
                                            });
                                        },
                                        error: function(xhr, status, error) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error updating monthly ad spent data!',
                                                text: xhr.responseJSON?.message || 'An error occurred.',
                                            });
                                        }
                                    });
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error importing data from Google Sheets!',
                                        text: xhr.responseJSON?.message || 'An error occurred.',
                                    });
                                }
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error updating sales turnover data!',
                                text: xhr.responseJSON?.message || 'An error occurred.',
                            });
                        }
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error fetching orders!',
                        text: xhr.responseJSON?.message || 'An error occurred.',
                    });
                }
            });
        });
    </script>

    @include('admin.visit.script')
    @include('admin.adSpentSocialMedia.script')
    @include('admin.adSpentMarketPlace.script')
    @include('admin.sales.script-chart')
@stop
