@extends('adminlte::page')

@section('title', trans('labels.customer'))

@section('content_header')
    <h1>{{ trans('labels.customer') }}</h1>
@stop

@section('content')
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
            const filterTenant = $('#filterTenant');

            let customerTable = customerTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('customer.get') }}",
                    data: function (d) {
                        d.filterCountOrders = filterCountOrders.val();
                        d.filterTenant = filterTenant.val();
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

            filterCountOrders.change(function () {
                customerTable.draw();
            });

            filterTenant.change(function () {
                customerTable.draw();
            });

            $('#resetFilterBtn').click(function () {
                filterCountOrders.val('');
                filterTenant.val('');
                customerTable.draw();
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