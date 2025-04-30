@extends('adminlte::page')

@section('title', trans('labels.customer'))

@section('content_header')
    <h1>{{ trans('labels.customer') }}</h1>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{ trans('labels.filters') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filterCountOrders">{{ trans('labels.total_order') }}</label>
                                <input type="number" id="filterCountOrders" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>{{ trans('labels.order_date') }}</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="dateFilter" id="currentMonthOnly" value="currentMonth" checked>
                                    <label class="form-check-label" for="currentMonthOnly">
                                        {{ trans('labels.current_month_only') }}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="dateFilter" id="showAllDates" value="allDates">
                                    <label class="form-check-label" for="showAllDates">
                                        {{ trans('labels.show_all_dates') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button id="applyFilterBtn" class="btn btn-primary mr-2">{{ trans('labels.apply') }}</button>
                            <button id="resetFilterBtn" class="btn btn-secondary">{{ trans('labels.reset') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="customerTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="customerTable-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.name') }}</th>
                                <th>{{ trans('labels.phone_number') }}</th>
                                <th>{{ trans('labels.total_order') }}</th>
                                <th>{{ trans('labels.last_order_date') }}</th>
                                <th width="10%">{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.socialMedia.modal')
    @include('admin.socialMedia.modal-update')
@stop

@section('css')
<style>
    .small-box {
        transition: transform 0.2s;
    }
    .small-box:hover {
        transform: translateY(-3px);
    }
    .kpi-detail-content {
        transition: all 0.3s ease;
    }
    .info-box.bg-info {
        color: #fff;
    }
    .info-box.bg-info .info-box-content {
        padding: 15px;
    }
    .info-box.bg-info .current-value {
        font-size: 24px;
        font-weight: bold;
    }
</style>
@stop

@section('js')
    <script>
        $(document).ready(function () {
            let loadingCounter = 0;
            const totalLoads = 3;

            function checkAllLoaded() {
                loadingCounter++;
                if (loadingCounter === totalLoads) {
                    // Counter function kept but Swal.close() removed
                }
            }

            $('a[href="#dailyCustomerTab"]').on('click', function() {
                loadCustomerLineChart('daily');
            });

            $('a[href="#monthlyCustomerTab"]').on('click', function() {
                loadCustomerLineChart('monthly');
            });

            $('a[href="#dailyTab"]').on('click', function() {
                loadMultipleLineChart('daily');
            });

            $('a[href="#monthlyTab"]').on('click', function() {
                loadMultipleLineChart('monthly');
            });
            
            const customerTableSelector = $('#customerTable');
            const filterCountOrders = $('#filterCountOrders');

            let customerTable = customerTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('customer.get') }}",
                    data: function (d) {
                        d.filterCountOrders = filterCountOrders.val();
                        d.currentMonthOnly = $('#currentMonthOnly').prop('checked');
                        d.showAllDates = $('#showAllDates').prop('checked');
                    }
                },
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'phone_number', name: 'phone_number'},
                    {data: 'count_orders', name: 'count_orders'},
                    {data: 'last_order_date', name: 'last_order_date'},
                    {data: 'actions', sortable: false, orderable: false}
                ],
                order: [[3, 'desc']] // Sort by last_order_date column (index 3) in descending order
            });

            // Apply filters button
            $('#applyFilterBtn').click(function() {
                customerTable.draw();
            });

            // Reset filters button
            $('#resetFilterBtn').click(function () {
                filterCountOrders.val('');
                $('#currentMonthOnly').prop('checked', true);
                $('#showAllDates').prop('checked', false);
                customerTable.draw();
            });

            // For radio buttons
            $('input[name="dateFilter"]').change(function() {
                if ($(this).val() === 'currentMonth') {
                    $('#currentMonthOnly').prop('checked', true);
                    $('#showAllDates').prop('checked', false);
                } else {
                    $('#currentMonthOnly').prop('checked', false);
                    $('#showAllDates').prop('checked', true);
                }
            });
            
            $('.small-box').click(function() {
                const kpiId = $(this).find('h4').attr('id');
                const kpiTitle = $(this).find('p').text();
                const kpiValue = $(this).find('h4').text(); // Get the KPI value
                
                // Hide all KPI detail content first
                $('.kpi-detail-content').hide();
                
                // Show the details section
                $('#kpiDetailsSection').show();
                
                // Show the specific KPI detail and update its current value
                $(`#${kpiId}-detail`).show();
                $(`#${kpiId}-detail .current-value`).text(kpiValue);
                
                // Update the title with the current value
                $('#kpiDetailTitle').text(`${kpiTitle} Details - Current Value: ${kpiValue}`);
                
                // Scroll to the details section
                $('html, body').animate({
                    scrollTop: $('#kpiDetailsSection').offset().top - 100
                }, 500);
            });

            // Close button handler
            $('#closeKpiDetails').click(function() {
                $('#kpiDetailsSection').hide();
            });

            // Make KPI cards look clickable
            $('.small-box').css('cursor', 'pointer');
        });
    </script>
@stop