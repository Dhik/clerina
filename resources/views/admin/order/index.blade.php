@extends('adminlte::page')

@section('title', trans('labels.order'))

@section('content_header')
    <h1>{{ trans('labels.order') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-10 col-sm-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" class="form-control rangeDate" id="filterDates" placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control" id="filterChannel">
                                        <option value="" selected>{{ trans('placeholder.select_sales_channel') }}</option>
                                        <option value="">{{ trans('labels.all') }}</option>
                                        @foreach($salesChannels as $salesChannel)
                                            <option value={{ $salesChannel->id }}>{{ $salesChannel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" id="filterQty" placeholder="{{ trans('placeholder.qty') }}" autocomplete="off">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control" id="filterSku" placeholder="{{ trans('placeholder.sku') }}" autocomplete="off">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" id="filterCity">
                                        <option value="" selected>{{ trans('placeholder.select_city') }}</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->city }}">{{ $city->city }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-default" id="resetFilterBtn">{{ trans('buttons.reset_filter') }}</button>
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
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <canvas id="turnoverOrderChart" width="800" height="400"></canvas>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <canvas id="orderChannelPie" width="800" height="200"></canvas>
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
                                <th>{{ trans('labels.date') }}</th>
                                <th>{{ trans('labels.id_order') }}</th>
                                <th>{{ trans('labels.channel') }}</th>
                                <th>{{ trans('labels.customer_name') }}</th>
                                <th>{{ trans('labels.username') }}</th>
                                <th>{{ trans('labels.phone_number') }}</th>
                                <th>{{ trans('labels.sku') }}</th>
                                <th>{{ trans('labels.qty') }}</th>
                                <th>{{ trans('labels.price') }}</th>
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

@section('js')
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

        let turnoverOrderChart;
        let orderPieChart;

        $('#resetFilterBtn').click(function () {
            $('#filterDates').val('')
            $('#filterChannel').val('')
            $('#filterQty').val('')
            $('#filterSku').val('')
            $('#filterCity').val('')
            orderTable.draw()
            updateRecapCount()
        })

        filterDate.change(function () {
            orderTable.draw()
            updateRecapCount()
        });

        $('#filterChannel').change(function () {
            orderTable.draw()
        });

        $('#filterQty').change(function () {
            orderTable.draw()
        });

        $('#filterSku').change(function () {
            orderTable.draw()
        });

        $('#filterCity').change(function () {
            orderTable.draw()
        });

        // datatable
        let orderTable = orderTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('order.get') }}",
                data: function (d) {
                    d.filterDates = $('#filterDates').val()
                    d.filterChannel = $('#filterChannel').val()
                    d.filterQty = $('#filterQty').val()
                    d.filterSku = $('#filterSku').val()
                    d.filterCity = $('#filterCity').val()
                }
            },
            columns: [
                {data: 'date', name: 'date'},
                {data: 'id_order', name: 'id_order', sortable: false},
                {data: 'salesChannel', name: 'salesChannel', sortable: false},
                {data: 'customer_name', name: 'customer_name', sortable: false},
                {data: 'username', name: 'username', sortable: false},
                {data: 'customer_phone_number', name: 'customer_phone_number', sortable: false},
                {data: 'sku', name: 'sku', sortable: false},
                {data: 'qtyFormatted', name: 'qty', sortable: false},
                {data: 'priceFormatted', name:'price'},
                {data: 'actions', sortable: false}
            ],
            columnDefs: [
                { "targets": [6], "className": "text-right" },
                { "targets": [7], "className": "text-right" },
                { "targets": [8], "className": "text-center" }
            ],
            order: [[0, 'desc']]
        });

        // Handle row click event to open modal and fill form
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
                url: '{{ route('sales.get-sales-recap') }}?filterDates=' + filterDate.val(), // Endpoint URL to fetch updated count
                method: 'GET', // You can use 'POST' if required
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
