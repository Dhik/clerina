@extends('adminlte::page')

@section('title', 'Customers Analysis')

@section('content_header')
    <h1>Customers Analysis</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-4">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-auto">
                            <div class="btn-group">
                            <input type="month" class="form-control mr-2" id="filterMonth" placeholder="Select Month" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                            <button id="refreshButton" class="btn btn-primary">Refresh Data</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4 id="totalOrder">0</h4>
                                    <p>Total Order</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
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
        
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="customerAnalysisTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nama Penerima</th>
                                <th>Nomor Telepon</th>
                                <th>Total Quantity</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
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
    <script>
        $(document).ready(function() {
            var table = $('#customerAnalysisTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('customer_analysis.data') }}',
                    data: function(d) {
                        d.month = $('#filterMonth').val(); // Add the selected month to the request
                    }
                },
                columns: [
                    { data: 'nama_penerima', name: 'nama_penerima' },
                    { data: 'nomor_telepon', name: 'nomor_telepon' },
                    { data: 'total_qty', name: 'total_qty' },
                ],
                order: [[1, 'asc']]
            });

            function fetchTotalUniqueOrders() {
                const selectedMonth = $('#filterMonth').val();
                fetch(`{{ route('customer_analysis.total') }}?month=${selectedMonth}`)
                    .then(response => response.json())
                    .then(data => {
                        $('#totalOrder').text(data.unique_customer_count); // Update the total order count
                    })
                    .catch(error => console.error('Error fetching total unique orders:', error));
            }
            fetchTotalUniqueOrders();

            $('#filterMonth').change(function() {
                table.ajax.reload();
                fetchTotalUniqueOrders();
                fetchProductCounts();
            });

            $('#refreshButton').click(function() {
                Swal.fire({
                    title: 'Refreshing...',
                    text: 'Importing customer data from Google Sheets. Please wait.',
                    didOpen: () => {
                        Swal.showLoading(); // Show loading animation while request is in progress
                    }
                });

                fetch('{{ route('customer_analysis.import') }}')
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();

                        if (data.error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.error,
                            });
                        } else {
                            table.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            fetchProductCounts();
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        console.error('Error:', error);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while refreshing data. Please try again later.',
                        });
                    });
            });

            // Fetch and render the product counts for the pie chart
            function fetchProductCounts() {
                fetch('{{ route('customer_analysis.product_counts') }}')
                    .then(response => response.json())
                    .then(data => {
                        const productLabels = data.map(item => item.short_name);
                        const productCounts = data.map(item => item.total_count);

                        // Render the pie chart
                        const ctx = document.getElementById('productPieChart').getContext('2d');
                        new Chart(ctx, {
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

            // Fetch product counts initially on page load
            fetchProductCounts();
        });
    </script>
@endsection
