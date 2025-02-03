@extends('adminlte::page')

@section('title', trans('labels.order'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ trans('labels.order') }}</h1>
        <div class="btn-group">
            <a href="{{ route('order.sku_detail_qty') }}" class="btn btn-outline-primary">Count Quantity by SKU</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                <div class="row">
    <div class="col-md-12">
        <div class="row mb-3">
            <!-- Order Date Filter -->
            <div class="col-md-2">
                <div class="form-group">
                    <label for="filterDates">Order Date</label>
                    <input type="text" class="form-control rangeDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                </div>
            </div>

            <!-- Process Date Filter -->
            <div class="col-md-2">
                <div class="form-group">
                    <label for="filterProcessDates">Process Date</label>
                    <input type="text" class="form-control rangeDate" id="filterProcessDates" placeholder="Select Process Date" autocomplete="off">
                </div>
            </div>

            <!-- Sales Channel Filter -->
            <div class="col-md-2">
                <div class="form-group">
                    <label for="filterChannel">Sales Channel</label>
                    <select class="form-control" id="filterChannel">
                        <option value="" selected>{{ trans('placeholder.select_sales_channel') }}</option>
                        <option value="">{{ trans('labels.all') }}</option>
                        @foreach($salesChannels as $salesChannel)
                            <option value={{ $salesChannel->id }}>{{ $salesChannel->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <div class="form-group">
                    <label for="filterStatus">Status</label>
                    <select class="form-control" id="filterStatus">
                        <option value="" selected>Pilih status</option>
                        @foreach($status as $stat)
                            <option value="{{ $stat->status }}">{{ $stat->status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Booking Filter -->
            <div class="col-md-2">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="icheck-primary mt-2">
                        <input type="checkbox" id="filterBooking">
                        <label for="filterBooking">Is Booking</label>
                    </div>
                </div>
            </div>

            <!-- Reset Button -->
            <div class="col-md-2">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button class="btn btn-default w-100" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
                </div>
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
                    <div class="small-box bg-maroon">
                        <div class="inner">
                            <h4 id="newQtyCount">0</h4>
                            <p>Qty</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Trend Count Order per Channel</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="dailyOrdersChart" width="800" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Count Order per Channel</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChannelChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#orderModal" id="btnAddOrder">
                                    <i class="fas fa-plus"></i> {{ trans('labels.add') }}
                                </button>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#orderExportModal">
                                    <i class="fas fa-file-download"></i> {{ trans('labels.export') }}
                                </button>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#orderImportModal">
                                    <i class="fas fa-file-upload"></i> {{ trans('labels.import') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="orderTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="order-info" width="100%">
                        <thead>
                            <tr>
                                <th>Order At</th>
                                <th>Process At</th>
                                <th>{{ trans('labels.id_order') }}</th>
                                <th>{{ trans('labels.channel') }}</th>
                                <th>{{ trans('labels.customer_name') }}</th>
                                <th>{{ trans('labels.username') }}</th>
                                <th>{{ trans('labels.phone_number') }}</th>
                                <th>{{ trans('labels.sku') }}</th>
                                <th>{{ trans('labels.qty') }}</th>
                                <th>{{ trans('labels.price') }}</th>
                                <th>Status</th>
                                <th>Is Booking</th>
                                <th width="10%">{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.order.modal-export')
    @include('admin.order.modal-import')
    @include('admin.order.modal-order')
    @include('admin.order.modal-order-update')
@stop

@section('css')
<style>
    .channel-logo {
    height: 34px;
    width: auto;
    vertical-align: middle;
    object-fit: contain;
}

td.text-center .channel-logo {
    margin: 0 auto;
    padding: 2px 0;
}
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}
.dataTables_wrapper {
        overflow-x: auto;
        margin: 0 auto;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .dataTable {
        width: 100% !important;
        margin: 0 auto;
    }
</style>
@stop

@section('js')
<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script>
        $(function () {
            orderTable.draw();
            bsCustomFileInput.init();
            updateRecapCount();
        });

        const orderTableSelector = $('#orderTable');
        const errorSubmitOrder = $('#errorSubmitOrder');
        const errorUpdateSubmitOrder = $('#errorUpdateSubmitOrder');
        const filterDate = $('#filterDates');
        const filterProcessDates = $('#filterProcessDates');
        const filterBooking = $('#filterBooking');
        const filterStatus = $('#filterStatus');

        let turnoverOrderChart;
        let orderPieChart;

        $('#resetFilterBtn').click(function () {
            $('#filterDates').val('')
            $('#filterChannel').val('')
            $('#filterQty').val('')
            $('#filterSku').val('')
            $('#filterCity').val('')
            $('#filterBooking').val('')
            $('#filterStatus').val('')
            orderTable.draw()
            updateRecapCount()
        })

        filterDate.change(function () {
            orderTable.draw()
            updateRecapCount()
        });
        filterProcessDates.change(function () {
            orderTable.draw()
            updateRecapCount()
        });

        $('#filterChannel').change(function () {
            orderTable.draw()
            updateRecapCount()
            loadDailyOrdersChart();
            loadSalesChannelChart();
        });

        $('#filterQty').change(function () {
            orderTable.draw()
            updateRecapCount()
        });

        $('#filterSku').change(function () {
            orderTable.draw()
            updateRecapCount()
        });

        $('#filterCity').change(function () {
            orderTable.draw()
            updateRecapCount()
        });
        $('#filterBooking').change(function () {
            orderTable.draw()
            updateRecapCount()
        });
        $('#filterStatus').change(function () {
            orderTable.draw()
            updateRecapCount()
            loadDailyOrdersChart();
            loadSalesChannelChart();
        });

        // datatable
        let orderTable = orderTableSelector.DataTable({
            responsive: false,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('order.get') }}",
                data: function (d) {
                    d.filterDates = $('#filterDates').val()
                    d.filterProcessDates = $('#filterProcessDates').val()
                    d.filterChannel = $('#filterChannel').val()
                    d.filterQty = $('#filterQty').val()
                    d.filterSku = $('#filterSku').val()
                    d.filterCity = $('#filterCity').val()
                    d.filterBooking = $('#filterBooking').prop('checked') ? '1' : null
                    d.filterStatus = $('#filterStatus').val()
                }
            },
            columns: [
                {data: 'date', name: 'date'},
                {
                    data: 'process_at', 
                    name: 'process_at',
                    render: function(data, type, row) {
                        return data || null;
                    }
                },
                {data: 'id_order', name: 'id_order', sortable: false},
                {
                    data: 'salesChannel', 
                    name: 'salesChannel', 
                    sortable: false,
                    className: 'text-center', // Add this line to center-align the content
                    render: function(data, type, row) {
                        if (type === 'display') {
                            switch(data) {
                                case 'Shopee':
                                    return '<img src="{{ asset("img/shopee.png") }}" alt="Shopee" class="channel-logo">';
                                case 'Tiktok Shop':
                                    return '<img src="{{ asset("img/tiktok_shop.png") }}" alt="Tiktok Shop" class="channel-logo">';
                                case 'Tokopedia':
                                    return '<img src="{{ asset("img/tokopedia.png") }}" alt="Tokopedia" class="channel-logo">';
                                case 'Lazada':
                                    return '<img src="{{ asset("img/lazada.png") }}" alt="Lazada" class="channel-logo">';
                                default:
                                    return data;
                            }
                        }
                        return data;
                    }
                },
                {data: 'customer_name', name: 'customer_name', sortable: false},
                {data: 'username', name: 'username', sortable: false},
                {data: 'customer_phone_number', name: 'customer_phone_number', sortable: false},
                {data: 'sku', name: 'sku', sortable: false},
                {data: 'qtyFormatted', name: 'qty', sortable: false},
                {data: 'priceFormatted', name:'price'},
                {
                    data: 'status', 
                    name: 'status',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (!data) return '<span class="btn btn-sm btn-secondary">NULL</span>';
                            
                            const statusColors = {
                                'sent': 'bg-teal',
                                'completed': 'bg-success',
                                'cancelled': 'bg-danger',
                                'request_return': 'bg-warning',
                                'request_cancel': 'bg-warning',
                                'sent_booking': 'bg-info',
                                'packing': 'bg-purple',
                                'paid': 'bg-primary',
                                'process': 'bg-info',
                                'pending': 'bg-secondary'
                            };

                            const color = statusColors[data.toLowerCase()] || 'bg-secondary';
                            return `<button class="btn btn-sm ${color}">${data}</button>`;
                        }
                        return data;
                    }
                },
                {data: 'is_booking', name:'is_booking'},
                {data: 'actions', sortable: false}
            ],
            columnDefs: [
                { "targets": [6], "className": "text-right" },
                { "targets": [7], "className": "text-right" },
                { "targets": [8], "className": "text-center" },
                { 
                    "targets": [10],
                    "render": function(data, type, row) {
                        return data === '1' ? 'is booking' : null;
                    }
                }
            ],
            order: [[0, 'desc']]
        });

        function loadDailyOrdersChart() {
            const filterChannel = $('#filterChannel').val();
            const filterStatus = $('#filterStatus').val();

            fetch(`{{ route("orders.daily-by-channel") }}?filterChannel=${filterChannel}&filterStatus=${filterStatus}`)
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('dailyOrdersChart').getContext('2d');
                    
                    if (window.dailyOrdersChart instanceof Chart) {
                        window.dailyOrdersChart.destroy();
                    }
                    
                    window.dailyOrdersChart = new Chart(ctx, {
                        type: 'line',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'nearest',
                                axis: 'x',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    position: 'right',
                                    align: 'center',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: {
                                            size: 12
                                        },
                                        boxWidth: 8
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Daily Orders by Channel',
                                    font: {
                                        size: 14
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        title: function(context) {
                                            return new Date(context[0].raw.x).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'short',
                                                year: 'numeric'
                                            });
                                        },
                                        label: function(context) {
                                            return `${context.dataset.label}: ${context.raw.y.toLocaleString('id-ID')} orders`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    type: 'time',
                                    time: {
                                        unit: 'day',
                                        displayFormats: {
                                            day: 'd MMM'
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        drawBorder: true,
                                        drawOnChartArea: true,
                                    },
                                    ticks: {
                                        stepSize: 1000,  // Changed from 1 to 1000
                                        callback: function(value) {
                                            return value.toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error loading daily orders chart:', error));
        }
        loadDailyOrdersChart();

        function loadSalesChannelChart() {
            const filterChannel = $('#filterChannel').val();
            const filterStatus = $('#filterStatus').val();

            fetch(`{{ route("orders.by-channel") }}?filterChannel=${filterChannel}&filterStatus=${filterStatus}`)
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('salesChannelChart').getContext('2d');
                    
                    if (window.salesChannelChart instanceof Chart) {
                        window.salesChannelChart.destroy();
                    }
                    
                    window.salesChannelChart = new Chart(ctx, {
                        type: 'pie',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    align: 'center',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true,
                                        font: {
                                            size: 12
                                        },
                                        boxWidth: 15
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Orders by Sales Channel',
                                    font: {
                                        size: 14
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.raw || 0;
                                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                            const percentage = ((value * 100) / total).toFixed(1);
                                            return `${label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error loading chart data:', error));
        }
        loadSalesChannelChart();

        orderTable.on('draw.dt', function() {
            const tableBodySelector =  $('#orderTable tbody');

            tableBodySelector.on('click', '.updateButton', function() {
                let rowData = orderTable.row($(this).closest('tr')).data();

                let dateObject = moment(rowData.date, "DD MMM YYYY");
                let formattedDate = dateObject.format("DD/MM/YYYY");

                if (rowData.sales_channel_id !== null) {
                    $('#salesChannelIdUpdate').val(rowData.sales_channel_id)
                }

                $('#dateUpdate').val(formattedDate);
                $('#idOrderUpdate').val(rowData.id_order);
                $('#receiptNumberUpdate').val(rowData.receipt_number);
                $('#shipmentUpdate').val(rowData.shipment);
                $('#paymentMethodUpdate').val(rowData.payment_method);
                $('#variantUpdate').val(rowData.variant);
                $('#customerNameUpdate').val(rowData.customer_name);
                $('#customerPhoneNumberUpdate').val(rowData.customer_phone_number);
                $('#productUpdate').val(rowData.product);
                $('#qtyUpdate').val(rowData.qty);
                $('#priceUpdate').val(rowData.price);
                $('#usernameUpdate').val(rowData.username);
                $('#shippingAddressUpdate').val(rowData.shipping_address);
                $('#cityUpdate').val(rowData.city);
                $('#provinceUpdate').val(rowData.province);
                $('#skuUpdate').val(rowData.sku);
                $('#orderId').val(rowData.id);
                $('#orderUpdateModal').modal('show');
            });
        });

        function updateRecapCount() {
            $.ajax({
                url: '{{ route('sales.get-sales-recap') }}',
                method: 'GET',
                data: {
                    filterDates: filterDate.val(),
                    filterProcessDates: $('#filterProcessDates').val(),
                    filterBooking: filterBooking.prop('checked') ? '1' : '0',  // Add this line
                    filterStatus: filterStatus.val()
                },
                success: function(response) {
                    // Update the count with the retrieved value
                    $('#newSalesCount').text(response.total_sales);
                    $('#newOrderCount').text(response.total_order);
                    $('#newQtyCount').text(response.total_qty);

                    // Extracting data
                    const salesData = response.sales;
                    const dates = salesData.map(data => data.date);
                    const orders = salesData.map(data => data.order);
                    const turnovers = salesData.map(data => data.turnover);
                    const qty = salesData.map(data => data.qty);

                    // Clear existing chart if it exists
                    if (turnoverOrderChart) {
                        turnoverOrderChart.destroy();
                    }

                    // Create a bar chart
                    const ctx = document.getElementById('turnoverOrderChart').getContext('2d');
                    turnoverOrderChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: dates,
                            datasets: [{
                                label: 'Sales',
                                data: turnovers,
                                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1
                            }, {
                                label: 'Orders',
                                data: orders,
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }, {
                                label: 'Qty',
                                data: qty,
                                backgroundColor: 'rgba(255, 206, 86, 0.5)',
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            tooltips: {
                                enabled: true, // Always display tooltips
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        let label = data.datasets[tooltipItem.datasetIndex].label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                        return label;
                                    }
                                }
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(value, index, values) {
                                            if (parseInt(value) >= 1000) {
                                                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                            } else {
                                                return value;
                                            }
                                        }
                                    }
                                }]
                            }
                        }
                    });

                    // Clear existing chart if it exists
                    if (orderPieChart) {
                        orderPieChart.destroy();
                    }

                    const pieData = response.pie_chart;
                    const labels = Object.keys(pieData);
                    const values = Object.values(pieData);

                    // Get the canvas element
                    const ctxPie = document.getElementById('orderChannelPie').getContext('2d');

                    // Create the pie chart
                    orderPieChart = new Chart(ctxPie, {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Total Sales By Sales Channel',
                                data: values,
                                backgroundColor: generatePredefinedColors(labels.length),
                                borderColor: generatePredefinedColors(labels.length),
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            title: {
                                display: true,
                                text: 'Total Sales By Sales Channel'
                            },
                            legend: {
                                position: 'right'
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        let dataset = data.datasets[tooltipItem.datasetIndex];
                                        let value = dataset.data[tooltipItem.index];
                                        return data.labels[tooltipItem.index] + ': ' + value + '%';
                                    }
                                }
                            }
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching new orders count:', error);
                }
            });
        }
    </script>

    @include('admin.order.script-create')
    @include('admin.order.script-update')
    @include('admin.order.script-export')
    @include('admin.order.script-import')
@stop
