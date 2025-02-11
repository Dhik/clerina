@extends('adminlte::page')

@section('title', 'Customers Analysis')

@section('content_header')
    <h1>Customers Analysis</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-2">
                            <input type="month" class="form-control mr-2" id="filterMonth" placeholder="Select Month" autocomplete="off">
                        </div>
                        <div class="col-3" style="display: none;">
                            <select id="filterProduk" class="form-control select2">
                                <option value="">All Produk</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <select id="filterStatus" class="form-control select2">
                                <option value="">All Status</option>
                                @foreach($customer as $status)
                                    <option value="{{ $status->status_customer }}">{{ $status->status_customer }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                                <button id="exportButton" class="btn btn-success"><i class="fas fa-file-excel"></i> Export to Excel</button>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                                <button id="refreshButton" class="btn btn-primary"><i class="fas fa-sync-alt"></i> Refresh Data</button>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                                <button id="importWhichHpButton" class="btn bg-maroon"><i class="fas fa-upload"></i> Assign HP</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <div class="small-box bg-gradient-teal">
                                <div class="inner">
                                    <h4 id="totalOrder">0</h4>
                                    <p>Total Unique Customers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="small-box bg-gradient-success">
                                <div class="inner">
                                    <h4 id="prioritasCount">0</h4>
                                    <p>Prioritas Customers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-award"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="small-box bg-gradient-primary">
                                <div class="inner">
                                    <h4 id="loyalisCount">0</h4>
                                    <p>Loyalis Customers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="small-box bg-gradient-info">
                                <div class="inner">
                                    <h4 id="newCount">0</h4>
                                    <p>New Customers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts in Tabs -->
        <div class="col-12">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#customerDistributionTab" data-toggle="tab">Customer Distribution</a></li>
                        <li class="nav-item"><a class="nav-link" href="#customerTrendTab" data-toggle="tab">Customer Trend</a></li>
                        <li class="nav-item"><a class="nav-link" href="#productDistributionTab" data-toggle="tab">Product Distribution</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="customerDistributionTab">
                            <div class="row">
                                <div class="col-12">
                                    <div style="height: 350px;">
                                        <canvas id="customerDistributionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane" id="customerTrendTab">
                            <div style="height: 350px;">
                                <canvas id="customerTrendChart"></canvas>
                            </div>
                        </div>

                        <div class="tab-pane" id="productDistributionTab">
                            <div style="height: 350px;">
                                <canvas id="productPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="customerAnalysisTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nama Penerima</th>
                                <th>Nomor Telepon</th>
                                <th>Total Orders</th>
                                <th>Status</th>
                                <th>HP</th>
                                <th>Details</th>
                                <th>Sudah Bergabung</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('admin.customers_analysis.modals.detail')
    @include('admin.customers_analysis.modals.edit')
    @include('admin.customers_analysis.modals.export')
@stop

@section('css')
    <style>
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 80%;
        }
        .bg-gradient-teal {
            background: linear-gradient(45deg, #20c997, #17a2b8);
            color: white;
        }
        .bg-gradient-success {
            background: linear-gradient(45deg, #28a745, #34ce57);
            color: white;
        }
        .bg-gradient-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
        }
        .bg-gradient-info {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
        }
        .nav-pills .nav-link.active {
            background-color: #007bff;
            color: #fff;
        }
        .tab-content {
            padding-top: 20px;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@^2"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#filterProduk, #filterStatus').select2({
                placeholder: "All Product",
                allowClear: true,
                width: '100%',
                theme: 'bootstrap4'
            });

            // Initialize Charts
            let productChart = null;
            let trendChart = null;
            let distributionChart = null;

            // Event handler for tab changes
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                const targetId = $(e.target).attr("href");
                if (targetId === "#productDistributionTab") {
                    fetchProductCounts();
                } else if (targetId === "#customerTrendTab") {
                    fetchAndRenderCustomerTrend();
                } else if (targetId === "#customerDistributionTab") {
                    fetchCustomerDistribution();
                }
            });

            // Existing DataTable initialization
            var table = $('#customerAnalysisTable').DataTable({
                // ... your existing DataTable configuration ...
            });

            // Filter change handlers
            $('#filterMonth, #filterProduk, #filterStatus').change(function() {
                table.ajax.reload();
                fetchTotalUniqueOrders();
                
                // Update active chart based on current tab
                const activeTab = $('.nav-pills .nav-link.active').attr('href');
                if (activeTab === "#productDistributionTab") {
                    fetchProductCounts();
                } else if (activeTab === "#customerTrendTab") {
                    fetchAndRenderCustomerTrend();
                } else if (activeTab === "#customerDistributionTab") {
                    fetchCustomerDistribution();
                }
            });

            // Product Distribution Chart
            function fetchProductCounts() {
                const selectedMonth = $('#filterMonth').val();
                const selectedProduk = $('#filterProduk').val();
                const ctx = document.getElementById('productPieChart').getContext('2d');

                if (productChart) {
                    productChart.destroy();
                }

                fetch(`{{ route('customer_analysis.product_counts') }}?month=${selectedMonth}&produk=${selectedProduk}`)
                    .then(response => response.json())
                    .then(data => {
                        const productLabels = data.map(item => item.short_name);
                        const productCounts = data.map(item => item.total_count);
                        
                        productChart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: productLabels,
                                datasets: [{
                                    data: productCounts,
                                    backgroundColor: [
                                        'rgba(75, 192, 192, 0.2)',
                                        'rgba(255, 99, 132, 0.2)',
                                        'rgba(255, 206, 86, 0.2)',
                                        'rgba(54, 162, 235, 0.2)',
                                        'rgba(153, 102, 255, 0.2)',
                                        'rgba(255, 159, 64, 0.2)',
                                    ],
                                    borderColor: [
                                        'rgba(75, 192, 192, 1)',
                                        'rgba(255, 99, 132, 1)',
                                        'rgba(255, 206, 86, 1)',
                                        'rgba(54, 162, 235, 1)',
                                        'rgba(153, 102, 255, 1)',
                                        'rgba(255, 159, 64, 1)',
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'right'
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Error fetching product counts:', error));
            }

            // Customer Trend Chart
            function fetchAndRenderCustomerTrend() {
                const selectedStatus = $('#filterStatus').val();
                
                fetch(`{{ route('customer_analysis.daily_status') }}?status=${selectedStatus}`)
                    .then(response => response.json())
                    .then(data => {
                        if (trendChart) {
                            trendChart.destroy();
                        }

                        const ctx = document.getElementById('customerTrendChart').getContext('2d');
                        trendChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.labels,
                                datasets: data.datasets.map(dataset => ({
                                    label: dataset.label,
                                    data: dataset.data,
                                    borderColor: dataset.borderColor,
                                    backgroundColor: dataset.backgroundColor,
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4
                                }))
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return value.toLocaleString();
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'top'
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Customer Distribution Chart (New)
            function fetchCustomerDistribution() {
                const selectedMonth = $('#filterMonth').val();
                
                if (distributionChart) {
                    distributionChart.destroy();
                }

                const ctx = document.getElementById('customerDistributionChart').getContext('2d');
                distributionChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['New', 'Loyalis', 'Prioritas'],
                        datasets: [{
                            label: 'Customer Distribution',
                            data: [
                                parseInt($('#newCount').text()),
                                parseInt($('#loyalisCount').text()),
                                parseInt($('#prioritasCount').text())
                            ],
                            backgroundColor: [
                                'rgba(23, 162, 184, 0.5)',
                                'rgba(0, 123, 255, 0.5)',
                                'rgba(40, 167, 69, 0.5)'
                            ],
                            borderColor: [
                                'rgba(23, 162, 184, 1)',
                                'rgba(0, 123, 255, 1)',
                                'rgba(40, 167, 69, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString();
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            // Initialize the first tab's chart
            fetchCustomerDistribution();

            // Refresh button handler
            $('#refreshButton').click(function() {
                Swal.fire({
                    title: 'Refreshing Data',
                    text: 'Importing customer data from Google Sheets. Please wait.',
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('{{ route('order.import_customer') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.error,
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                html: `
                                    Import Complete<br>
                                    Total Rows: ${data.total_rows}<br>
                                    Processed Rows: ${data.processed_rows}
                                `,
                                showConfirmButton: false,
                                timer: 2000
                            });

                            // Reload tables and update widgets
                            table.ajax.reload(null, false);
                            fetchTotalUniqueOrders();
                            
                            // Update active chart
                            const activeTab = $('.nav-pills .nav-link.active').attr('href');
                            if (activeTab === "#productDistributionTab") {
                                fetchProductCounts();
                            } else if (activeTab === "#customerTrendTab") {
                                fetchAndRenderCustomerTrend();
                            } else if (activeTab === "#customerDistributionTab") {
                                fetchCustomerDistribution();
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while refreshing data. Please try again later.',
                        });
                    });
            });

            // Export button handler
            $('#exportButton').click(function() {
                $('#exportModal').modal('show');
            });

            $('#doExport').click(function() {
                let month = $('#exportMonth').val();
                let status = $('#exportStatus').val(); 
                let whichHp = $('#exportWhichHp').val();
                
                window.location.href = `{{ route('customer_analysis.export') }}?month=${month}&status=${status}&which_hp=${whichHp}`;
                $('#exportModal').modal('hide');
            });

            // Import Which HP button handler
            $('#importWhichHpButton').click(function() {
                Swal.fire({
                    title: 'Importing Which HP Data',
                    text: 'Importing data from Google Sheets. Please wait.',
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('{{ route('customer_analysis.import_which_hp') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Import Complete',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to import data',
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while importing data',
                        });
                    });
            });

            // Fetch total unique orders
            function fetchTotalUniqueOrders() {
                const selectedMonth = $('#filterMonth').val();
                const selectedProduk = $('#filterProduk').val();

                fetch(`{{ route('customer_analysis.total') }}?month=${selectedMonth}&produk=${selectedProduk}`)
                    .then(response => response.json())
                    .then(data => {
                        $('#totalOrder').text(data.unique_customer_count);
                        $('#loyalisCount').text(data.loyalis_count);
                        $('#prioritasCount').text(data.prioritas_count);
                        $('#newCount').text(data.new_count);
                        
                        // Update customer distribution chart if it's active
                        const activeTab = $('.nav-pills .nav-link.active').attr('href');
                        if (activeTab === "#customerDistributionTab") {
                            fetchCustomerDistribution();
                        }
                    })
                    .catch(error => console.error('Error fetching customer counts:', error));
            }

            // Initial fetch of unique orders
            fetchTotalUniqueOrders();

            // Populate product filter
            function populateProdukFilter() {
                fetch('{{ route('customer_analysis.get_products') }}')
                    .then(response => response.json())
                    .then(data => {
                        const produkSelect = $('#filterProduk');
                        produkSelect.empty();
                        produkSelect.append('<option value="">All Produk</option>');
                        data.forEach(produk => {
                            produkSelect.append(`<option value="${produk.short_name}">${produk.short_name}</option>`);
                        });
                    })
                    .catch(error => console.error('Error fetching produk list:', error));
            }

            // Initialize product filter
            populateProdukFilter();
        });
    </script>
@endsection