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
                        <!-- <div class="col-auto">
                            <div class="btn-group">
                                <button id="refreshButton" class="btn btn-primary"><i class="fas fa-sync-alt"></i> Refresh Data</button>
                            </div>
                        </div> -->
                        <!-- <div class="col-auto">
                            <div class="btn-group">
                                <button id="importButton" class="btn btn-info"><i class="fas fa-upload"></i> Import Status</button>
                            </div>
                        </div> -->
                        <div class="col-auto">
                            <div class="btn-group">
                                <button id="importWhichHpButton" class="btn bg-maroon"><i class="fas fa-upload"></i> Assign HP</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <div class="small-box bg-teal">
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
                            <div class="small-box bg-primary">
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
                            <div class="small-box bg-success">
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
                            <div class="small-box bg-info">
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
        <div class="col-4">
            <div class="card">
                <div class="card-body">
                    <h5>Distribusi per Produk</h5>
                    <div style="height: 350px;">
                    <canvas id="productPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-8">
            <div class="card">
                <div class="card-body">
                    <h5>Jumlah Customer per Hari</h5>
                    <div style="height: 350px;">
                        <!-- <canvas id="dailyCustomersChart"></canvas> -->
                        <canvas id="customerTrendChart"></canvas>
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
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@^2"></script>
    <script>
        $(document).ready(function() {
            $('#filterProduk').select2({
                placeholder: "All Product",
                allowClear: true,
                width: '100%',
                theme: 'bootstrap4'
            });

            var table = $('#customerAnalysisTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('customer_analysis.data') }}',
                    data: function(d) {
                        d.month = $('#filterMonth').val();
                        d.produk = $('#filterProduk').val();
                        d.status = $('#filterStatus').val();
                    }
                },
                columns: [
                    { data: 'nama_penerima', name: 'nama_penerima' },
                    { data: 'nomor_telepon', name: 'nomor_telepon' },
                    { data: 'total_orders', name: 'total_orders' },
                    {
                        data: 'status_customer',
                        name: 'status_customer',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                if (!data) return '<span class="btn btn-sm btn-secondary">NULL</span>';
                                
                                const statusColors = {
                                    'prioritas': 'bg-success',
                                    'loyalis': 'bg-primary',
                                    'new customer': 'bg-info'
                                };

                                const color = statusColors[data.toLowerCase()] || 'bg-secondary';
                                return `<button class="btn btn-sm ${color}">${data}</button>`;
                            }
                            return data;
                        }
                    },
                    { data: 'which_hp', name: 'which_hp' },
                    { data: 'details', name: 'details' },
                    { data: 'is_joined', name: 'is_joined' },
                ],
                order: [[1, 'asc']]
            });

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
                    })
                    .catch(error => console.error('Error fetching customer counts:', error));
            }

            fetchTotalUniqueOrders();

            $('#filterMonth, #filterProduk, #filterStatus').change(function() {
                table.ajax.reload();
                fetchTotalUniqueOrders();
                fetchProductCounts();
                fetchAndRenderCustomerTrend();
            });

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

            // Fetch produk list initially
            populateProdukFilter();

            $('#refreshButton').click(function() {

                Swal.fire({
                    title: 'Refreshing Data',
                    text: 'Importing customer data from Google Sheets. Please wait.',
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // fetch('{{ route('customer_analysis.import') }}')
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
                            fetchProductCounts();
                            fetchDailyUniqueCustomers();
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
            $('#importButton').click(function() {
                    Swal.fire({
                        title: 'Refreshing Data',
                        text: 'Importing customer data from Google Sheets. Please wait.',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('{{ route('customer_analysis.import_join') }}')
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
                                        Import Complete
                                    `,
                                    showConfirmButton: false,
                                    timer: 2000
                                });

                                // Reload tables and update widgets
                                table.ajax.reload(null, false);
                                fetchTotalUniqueOrders();
                                fetchProductCounts();
                                fetchDailyUniqueCustomers();
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

            function fetchProductCounts() {
                const selectedMonth = $('#filterMonth').val();
                const selectedProduk = $('#filterProduk').val(); // Get the selected produk
                const ctx = document.getElementById('productPieChart').getContext('2d');

                if (window.productChart) {
                    window.productChart.destroy();
                }

                fetch(`{{ route('customer_analysis.product_counts') }}?month=${selectedMonth}&produk=${selectedProduk}`)
                    .then(response => response.json())
                    .then(data => {
                        const productLabels = data.map(item => item.short_name);
                        const productCounts = data.map(item => item.total_count);
                        window.productChart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: productLabels,
                                datasets: [{
                                    label: 'Product Orders',
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
                                plugins: {
                                    legend: {
                                        display: false,
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                return tooltipItem.label + ': ' + tooltipItem.raw;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Error fetching product counts:', error));
            }
            fetchProductCounts();

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

            // function fetchDailyUniqueCustomers() {
            //     const selectedMonth = $('#filterMonth').val();
            //     const selectedProduk = $('#filterProduk').val(); // Get the selected produk
            //     const ctx = document.getElementById('dailyCustomersChart').getContext('2d');

            //     // Destroy existing chart if it exists
            //     if (window.dailyChart) {
            //         window.dailyChart.destroy();
            //     }

            //     fetch(`{{ route('customer_analysis.daily_unique') }}?month=${selectedMonth}&produk=${selectedProduk}`)
            //         .then(response => response.json())
            //         .then(data => {
            //             const dates = data.map(item => item.date);
            //             const counts = data.map(item => item.unique_count);

            //             window.dailyChart = new Chart(ctx, {
            //                 type: 'line',
            //                 data: {
            //                     labels: dates,
            //                     datasets: [{
            //                         label: 'Unique Customers',
            //                         data: counts,
            //                         borderColor: 'rgb(75, 192, 192)',
            //                         tension: 0.1,
            //                         fill: false
            //                     }]
            //                 },
            //                 options: {
            //                     responsive: true,
            //                     maintainAspectRatio: false,
            //                     scales: {
            //                         y: {
            //                             beginAtZero: true,
            //                             ticks: {
            //                                 precision: 0
            //                             }
            //                         },
            //                         x: {
            //                             ticks: {
            //                                 maxRotation: 45,
            //                                 minRotation: 45
            //                             }
            //                         }
            //                     },
            //                     plugins: {
            //                         legend: {
            //                             display: false
            //                         }
            //                     }
            //                 }
            //             });
            //         })
            //         .catch(error => console.error('Error fetching daily unique customers:', error));
            // }


            // // Add these lines to your existing document.ready function
            // fetchDailyUniqueCustomers();

            $('#customerAnalysisTable').on('click', '.joinButton', function() {
                var id = $(this).data('id');
                var url = '{{ route('customer_analysis.join', ':id') }}'.replace(':id', id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will mark the customer as joined.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, join them!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Joined!', 'Customer has been marked as joined.', 'success');
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('Error!', 'There was an issue marking the customer as joined.', 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'There was an issue marking the customer as joined.', 'error');
                            }
                        });
                    }
                });
            });

            // Handle unjoin button click
            $('#customerAnalysisTable').on('click', '.unJoinButton', function() {
                var id = $(this).data('id');
                var url = '{{ route('customer_analysis.unjoin', ':id') }}'.replace(':id', id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will unmark the customer as joined.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, unjoin them!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Unjoined!', 'Customer has been unmarked as joined.', 'success');
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('Error!', 'There was an issue unmarking the customer as joined.', 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'There was an issue unmarking the customer as joined.', 'error');
                            }
                        });
                    }
                });
            });

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

            var ordersTable = $('#ordersTable').DataTable({
                searching: false,
                paging: true,
                info: false,
                lengthChange: false,
                pageLength: 5
            });

            $('#customerAnalysisTable').on('click', '.editButton', function() {
                var id = $(this).data('id');

                $.ajax({
                    url: '{{ route('customer_analysis.edit', ':id') }}'.replace(':id', id),
                    method: 'GET',
                    success: function(response) {
                        $('#editCustomerForm').attr('action', '{{ route('talent.update', ':id') }}'.replace(':id', id));
                        
                        $('#edit_nama_penerima').val(response.customer.nama_penerima);
                        $('#edit_produk').val(response.customer.produk);
                        $('#edit_qty').val(response.customer.qty);
                        $('#editTalentModal').modal('show');
                    },
                    error: function(response) {
                        console.error('Error fetching talent data:', response);
                    }
                });
            });

            $('#customerAnalysisTable').on('click', '.viewButton', function() {
                var customerId = $(this).data('id');

                $.ajax({
                    url: `{{ route('customer_analysis.show', ':id') }}`.replace(':id', customerId),
                    method: 'GET',
                    success: function(response) {
                        // Populate the customer name and phone number
                        $('#view_customer_name').val(response.nama_penerima);
                        $('#view_phone_number').val(response.nomor_telepon);
                        $('#view_alamat').val(response.alamat);
                        $('#view_kota_kabupaten').val(response.kota_kabupaten);
                        $('#view_provinsi').val(response.provinsi);
                        $('#view_quantity').val(response.quantity);

                        // Clear the existing orders from DataTable
                        ordersTable.clear();

                        // Add new orders to the DataTable
                        response.orders.forEach(function(order) {
                            ordersTable.row.add([
                                order.produk,
                                order.tanggal_pesanan_dibuat,
                                order.qty
                            ]).draw();
                        });

                        fetch(`{{ route('customer_analysis.product_distribution', ':id') }}`.replace(':id', customerId))
                            .then(response => response.json())
                            .then(data => {
                                var productLabels = data.map(item => item.produk);
                                var productCounts = data.map(item => item.count);

                                // Destroy previous chart instance if it exists
                                if (productChart) {
                                    productChart.destroy();
                                }

                                // Create the pie chart with new data
                                var ctx = document.getElementById('productPieChartDetail').getContext('2d');
                                productChart = new Chart(ctx, {
                                    type: 'pie',
                                    data: {
                                        labels: productLabels,
                                        datasets: [{
                                            label: 'Product Count',
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
                                                display: false,
                                            }
                                        }
                                    }
                                });
                            })
                            .catch(error => console.error('Error fetching product distribution data:', error));

                        // Open the modal
                        $('#viewCustomerModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error fetching customer data:', xhr);
                        Swal.fire('Error', 'Could not fetch customer data. Please try again later.', 'error');
                    }
                });
            });
            $('#viewCustomerModal').on('hidden.bs.modal', function () {
                fetchProductCounts();
                fetchDailyUniqueCustomers();
            });

            // function fetchCityCounts() {
            //     const selectedMonth = $('#filterMonth').val();
            //     const selectedProduk = $('#filterProduk').val(); // Get the selected produk
            //     const ctx = document.getElementById('cityPieChart').getContext('2d');

            //     if (window.productChart) {
            //         window.productChart.destroy();
            //     }

            //     fetch(`{{ route('customer_analysis.city_counts') }}?month=${selectedMonth}&produk=${selectedProduk}`)
            //         .then(response => response.json())
            //         .then(data => {
            //             const productLabels = data.map(item => item.kota_kabupaten);
            //             const productCounts = data.map(item => item.total_count);
            //             window.productChart = new Chart(ctx, {
            //                 type: 'pie',
            //                 data: {
            //                     labels: productLabels,
            //                     datasets: [{
            //                         label: 'Product Orders',
            //                         data: productCounts,
            //                         backgroundColor: [
            //                             'rgba(75, 192, 192, 0.2)',
            //                             'rgba(255, 99, 132, 0.2)',
            //                             'rgba(255, 206, 86, 0.2)',
            //                             'rgba(54, 162, 235, 0.2)',
            //                             'rgba(153, 102, 255, 0.2)',
            //                             'rgba(255, 159, 64, 0.2)',
            //                         ],
            //                         borderColor: [
            //                             'rgba(75, 192, 192, 1)',
            //                             'rgba(255, 99, 132, 1)',
            //                             'rgba(255, 206, 86, 1)',
            //                             'rgba(54, 162, 235, 1)',
            //                             'rgba(153, 102, 255, 1)',
            //                             'rgba(255, 159, 64, 1)',
            //                         ],
            //                         borderWidth: 1
            //                     }]
            //                 },
            //                 options: {
            //                     responsive: true,
            //                     plugins: {
            //                         legend: {
            //                             display: false,
            //                         },
            //                         tooltip: {
            //                             callbacks: {
            //                                 label: function(tooltipItem) {
            //                                     return tooltipItem.label + ': ' + tooltipItem.raw;
            //                                 }
            //                             }
            //                         }
            //                     }
            //                 }
            //             });
            //         })
            //         .catch(error => console.error('Error fetching product counts:', error));
            // }
            // fetchCityCounts();

            let lineChart;

            function fetchAndRenderCustomerTrend() {
                const selectedStatus = $('#filterStatus').val();
                
                fetch(`{{ route('customer_analysis.daily_status') }}?status=${selectedStatus}`)
                    .then(response => response.json())
                    .then(data => {
                        const lineChartData = {
                            labels: data.labels,
                            datasets: data.datasets.map(dataset => ({
                                label: dataset.label,
                                data: dataset.data, 
                                borderColor: dataset.borderColor,
                                backgroundColor: dataset.backgroundColor,
                                borderWidth: 2,
                                fill: true,
                                tension: dataset.tension
                            }))
                        };

                        if (lineChart) {
                            lineChart.destroy();
                        }

                        const ctx = document.getElementById('customerTrendChart').getContext('2d');
                        lineChart = new Chart(ctx, {
                            type: 'line',
                            data: lineChartData,
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

            fetchAndRenderCustomerTrend();
            
        });
    </script>
@endsection
