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
                    </div>
                    <div class="card-body">
                        <canvas id="customerOrderChart" width="400" height="200"></canvas>
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

@section('js')
    <script>
        $(function () {
            const customerTableSelector = $('#customerTable');
            const filterCountOrders = $('#filterCountOrders');
            const filterTenant = $('#filterTenant');

            let customerTable = customerTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                pageLength: 25,
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
            function loadCustomerLineChart(chartId, endpoint) {
                const ctx = document.getElementById(chartId).getContext('2d');

                fetch(endpoint)
                    .then(response => response.json())
                    .then(data => {
                        const labels = data.map(item => item.date);
                        const counts = data.map(item => item.customer_count);

                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Daily Customer Count',
                                    data: counts,
                                    borderWidth: 1,
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Date'
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
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }
            loadCustomerLineChart('customerLineChart', '{{ route('customer.daily-count') }}');
            function loadMultipleLineChart(chartId, endpoint) {
                const ctx = document.getElementById(chartId).getContext('2d');

                fetch(endpoint)
                    .then(response => response.json())
                    .then(data => {
                        const labels = data.map(item => item.date);
                        const firstTimers = data.map(item => item.first_timer_count);
                        const repeatedOrders = data.map(item => item.repeated_order_count);

                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'First Timer Orders',
                                        data: firstTimers,
                                        borderWidth: 1,
                                        borderColor: 'rgba(54, 162, 235, 1)',
                                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                        tension: 0.4
                                    },
                                    {
                                        label: 'Repeated Orders',
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
                                            text: 'Date'
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
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }
            loadMultipleLineChart('customerOrderChart', '{{ route('customer.daily-order-stats') }}');

        });
    </script>
@stop
