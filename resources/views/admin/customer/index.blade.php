@extends('adminlte::page')

@section('title', trans('labels.customer'))

@section('content_header')
    <h1>{{ trans('labels.customer') }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="btn-group">
            <form id="orderExportForm" action="{{ route('customer.export') }}" method="post">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-file-download"></i> {{ trans('labels.export') }}
                </button>
            </form>
        </div>
        <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Trend Customer Count</h3>
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link" href="#dailyCustomerTab" data-toggle="tab" onclick="loadCustomerLineChart('customerLineChart', '{{ route('customer.daily-count') }}', 'daily')">Daily</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="#monthlyCustomerTab" data-toggle="tab" onclick="loadCustomerLineChart('customerLineChart', '{{ route('customer.daily-count') }}', 'monthly')">Monthly</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <canvas id="customerLineChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3>First Timer vs Repeated</h3>
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link" href="#dailyTab" data-toggle="tab" onclick="loadMultipleLineChart('customerOrderChart', '{{ route('customer.daily-order-stats') }}', 'daily')">Daily</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="#monthlyTab" data-toggle="tab" onclick="loadMultipleLineChart('customerOrderChart', '{{ route('customer.daily-order-stats') }}', 'monthly')">Monthly</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <canvas id="customerOrderChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- KPI Cards Section -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h4 id="kpi-churned-customers">0</h4>
                        <p>Churned Customers</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h4 id="kpi-churn-rate">0</h4>
                        <p>Churn Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h4 id="kpi-avg-lifespan">0</h4>
                        <p>Avg. Customer Lifespan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h4 id="kpi-max-lifespan">0</h4>
                        <p>Max Customer Lifespan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h4 id="kpi-avg-clv">0</h4>
                        <p>Avg. Customer Lifespan Value</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h4 id="kpi-repeat-purchase-rate">0</h4>
                        <p>Repeat Purchase Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add this right after the KPI cards row -->
        <div class="row mt-4" id="kpiDetailsSection" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title" id="kpiDetailTitle"></h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" id="closeKpiDetails">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Churned Customers Details -->
                        <div class="kpi-detail-content" id="kpi-churned-customers-detail" style="display: none;">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="info-box bg-info">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Current Value</span>
                                            <span class="info-box-number current-value">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Churned customers represent those who haven't made a purchase in the last 90 days.</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Last Month</span>
                                            <span class="info-box-number">12%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Industry Average</span>
                                            <span class="info-box-number">15%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Target</span>
                                            <span class="info-box-number">8%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Churn Rate Details -->
                        <div class="kpi-detail-content" id="kpi-churn-rate-detail" style="display: none;">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="info-box bg-info">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Current Value</span>
                                            <span class="info-box-number current-value">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                            <!-- Rest of the content... -->
                        </div>

                        <!-- Continue with other KPI details following the same pattern... -->
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
                                <th>{{ trans('labels.tenant_name') }}</th>
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
        function loadCustomerLineChart(type) {
            const ctx = document.getElementById('customerLineChart').getContext('2d');

            $.ajax({
                url: '{{ route('customer.daily-count') }}',
                method: 'GET',
                data: { type: type },
                success: function(data) {
                    const labels = data.map(item => item.period);
                    const counts = data.map(item => item.customer_count);

                    if (window.customerLineChart instanceof Chart) {
                        window.customerLineChart.destroy();
                    }

                    window.customerLineChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: type === 'daily' ? 'Daily Customer Count' : 'Monthly Customer Count',
                                data: counts,
                                borderWidth: 1,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: type === 'daily' ? 'Date' : 'Month'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Customer Count'
                                    },
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        function loadMultipleLineChart(type) {
            const ctx = document.getElementById('customerOrderChart').getContext('2d');

            $.ajax({
                url: '{{ route('customer.daily-order-stats') }}',
                method: 'GET',
                data: { type: type },
                success: function(data) {
                    const labels = data.map(item => item.period);
                    const firstTimers = data.map(item => item.first_timer_count);
                    const repeatedOrders = data.map(item => item.repeated_order_count);

                    if (window.customerOrderChart instanceof Chart) {
                        window.customerOrderChart.destroy();
                    }

                    window.customerOrderChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: type === 'daily' ? 'First Timer Orders (Daily)' : 'First Timer Orders (Monthly)',
                                    data: firstTimers,
                                    borderWidth: 1,
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                    tension: 0.4
                                },
                                {
                                    label: type === 'daily' ? 'Repeated Orders (Daily)' : 'Repeated Orders (Monthly)',
                                    data: repeatedOrders,
                                    borderWidth: 1,
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                    tension: 0.4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: type === 'daily' ? 'Date' : 'Month'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Order Count'
                                    },
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });
        }
    
        window.loadCustomerLineChart = loadCustomerLineChart;
        window.loadMultipleLineChart = loadMultipleLineChart;
        $(document).ready(function () {
            loadCustomerLineChart('monthly');
            loadMultipleLineChart('monthly');

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
                    {data: 'tenant_name', name: 'tenant_name'},
                    {data: 'actions', sortable: false, orderable: false}
                ]
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

            $.ajax({
                url: "{{ route('customer.stats') }}", // Update this route to match your API endpoint
                method: 'GET',
                success: function(data) {
                    $('#kpi-churned-customers').text(data.churned_customers);
                    $('#kpi-churn-rate').text(data.churn_rate.toFixed(2) + '%');
                    $('#kpi-avg-lifespan').text(data.average_customer_lifespan_days.toFixed(2) + ' days');
                    $('#kpi-max-lifespan').text(data.max_customer_lifespan_days + ' days');
                    $('#kpi-min-lifespan').text(data.min_customer_lifespan_days + ' days');
                    $('#kpi-avg-clv').text(data.average_customer_lifetime_value.toFixed(2));
                    $('#kpi-repeat-purchase-rate').text(data.repeat_purchase_rate.toFixed(2) + '%');
                },
                error: function(error) {
                    console.error('Error fetching KPI data:', error);
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
