@extends('adminlte::page')

@section('title', trans('labels.customer'))

@section('content_header')
    <h1>{{ trans('labels.customer') }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <!-- <div class="btn-group">
            <form id="orderExportForm" action="{{ route('customer.export') }}" method="post">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-file-download"></i> {{ trans('labels.export') }}
                </button>
            </form>
        </div> -->
        <a href="{{ route('customer_analysis.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Customers Data from January 2024</a>
        <!-- <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button> -->
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
            <div class="col-lg-2 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h4 id="kpi-churned-customers">Loading...</h4>
                        <p>Churned Customers</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h4 id="kpi-churn-rate">Loading...</h4>
                        <p>Churn Rate</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-maroon">
                    <div class="inner">
                        <h4 id="kpi-avg-lifespan">Loading...</h4>
                        <p>Avg. Customer Lifespan</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h4 id="kpi-max-lifespan">Loading...</h4>
                        <p>Max Customer Lifespan</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-pink">
                    <div class="inner">
                        <h4 id="kpi-avg-clv">Loading...</h4>
                        <p>Avg. Customer Lifespan Value</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h4 id="kpi-repeat-purchase-rate">Loading...</h4>
                        <p>Repeat Purchase Rate</p>
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
                            <p>Ini menunjukkan jumlah pelanggan yang berhenti membeli selama 6 bulan ada <span class="info-box-number current-value"></span> pelanggan.</p>
                        </div>

                        <!-- Churn Rate Details -->
                        <div class="kpi-detail-content" id="kpi-churn-rate-detail" style="display: none;">
                            <p>Ini adalah persentase pelanggan yang melakukan churn terhadap total basis pelanggan anda dalam periode 6 bulan. Tingkat churn sebesar <span class="info-box-number current-value"></span> berarti sekitar <span class="info-box-number current-value"></span> pelanggan anda telah berhenti membeli dalam jangka waktu tersebut.</p>
                        </div>

                        <div class="kpi-detail-content" id="kpi-avg-lifespan-detail" style="display: none;">
                            <p>Ini adalah jumlah hari rata-rata pelanggan bertahan dengan bisnis anda sebelum mereka berpindah. Rata-rata, pelanggan bertahan selama <span class="info-box-number current-value"></span> hari sebelum menghentikan pembelian mereka.</p>
                        </div>

                        <div class="kpi-detail-content" id="kpi-max-lifespan-detail" style="display: none;">
                            <p>Durasi terlama seorang pelanggan bertahan dengan bisnis Anda. Dalam hal ini, pelanggan dengan jangka waktu terpanjang bertahan selama <span class="info-box-number current-value"></span>. Hal ini penting karena menunjukkan bahwa beberapa pelanggan tetap bertahan dalam jangka waktu yang relatif lama.</p>
                        </div>

                        <div class="kpi-detail-content" id="kpi-avg-clv-detail" style="display: none;">
                            <p>Ini adalah jumlah rata-rata pendapatan yang dihasilkan bisnis Anda per pelanggan selama hubungan mereka dengan bisnis Anda. CLV rata-rata adalah <span class="info-box-number current-value"></span>.</p>
                        </div>

                        <div class="kpi-detail-content" id="kpi-repeat-purchase-rate-detail" style="display: none;">
                            <p>Metrik ini menunjukkan persentase pelanggan yang telah melakukan lebih dari satu kali pembelian. Tingkat pembelian berulang sebesar <span class="info-box-number current-value"></span> berarti sekitar <span class="info-box-number current-value"></span> pelanggan anda yang telah melakukan lebih dari satu kali pembelian.</p>
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
        $(document).ready(function () {
            Swal.fire({
                title: 'Loading Dashboard',
                html: 'Please wait while we prepare your data...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            let loadingCounter = 0;
            const totalLoads = 3;

            function checkAllLoaded() {
                loadingCounter++;
                if (loadingCounter === totalLoads) {
                    Swal.close();
                }
            }
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
                        checkAllLoaded();
                    },
                    error: function(error) {
                        console.error('Error fetching data:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to load customer chart data. Please try again.',
                        });
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
                        checkAllLoaded();
                    },
                    error: function(error) {
                        console.error('Error fetching data:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to load order statistics. Please try again.',
                        });
                    }
                });
            }
            loadCustomerLineChart('monthly');
            loadMultipleLineChart('monthly');

            $.ajax({
                url: "{{ route('customer.stats') }}", // Update this route to match your API endpoint
                method: 'GET',
                success: function(data) {
                    $('#kpi-churned-customers').text(data.churned_customers);
                    $('#kpi-churn-rate').text(data.churn_rate.toFixed(2) + '%');
                    $('#kpi-avg-lifespan').text(data.average_customer_lifespan_days.toFixed(2) + ' days');
                    $('#kpi-max-lifespan').text(data.max_customer_lifespan_days + ' days');
                    $('#kpi-min-lifespan').text(data.min_customer_lifespan_days + ' days');
                    $('#kpi-avg-clv').text('Rp. ' + data.average_customer_lifetime_value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                    $('#kpi-repeat-purchase-rate').text(data.repeat_purchase_rate.toFixed(2) + '%');
                    checkAllLoaded();
                },
                error: function(error) {
                    console.error('Error fetching KPI data:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to load KPI statistics. Please try again.',
                    });
                }
            });

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
