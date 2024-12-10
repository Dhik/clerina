@extends('adminlte::page')

@section('title', 'Spent Targets')

@section('content_header')
    <h1 class="d-flex justify-content-between align-items-center">
        Spent Targets
        <button class="btn btn-success" id="refreshDataButton">
            <i class="fas fa-sync-alt"></i> Refresh Data
        </button>
    </h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.spent_target.recap_card')
            <div class="card">
                <div class="card-header">
                    <a href="#" class="btn btn-primary" id="addSpentTargetButton">
                        <i class="fas fa-plus"></i> Add Spent Target
                    </a>
                </div>
                
                <div class="card-body">
                    <table id="spentTargetTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Budget</th>
                                <th>KOL Percentage</th>
                                <th>Ads Percentage</th>
                                <th>Creative Percentage</th>
                                <th>Activation Percentage</th>
                                <th>Free Product Percentage</th>
                                <th>Month</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('admin.spent_target.modals.add_spent_target_modal')
    @include('admin.spent_target.modals.edit_spent_target_modal')
    @include('admin.spent_target.modals.view_spent_target_modal')
    @include('admin.spent_target.script_chart')
@stop

@section('css')
    <style>
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 80%;
        }
    </style>
    <style>
    .gauge {
        width: 100%;
        height: 300px;
        position: relative;
    }

    .gauge-background {
        fill: #f0f0f0;
        stroke: #ccc;
        stroke-width: 2px;
    }

    .gauge-value.green {
        fill: #4CAF50;
        stroke: #357a38;
        stroke-width: 2px;
        transition: all 1s ease-in-out;
    }

    .gauge-value.orange {
        fill: orange;
        stroke: #FFA500;
        stroke-width: 2px;
        transition: all 1s ease-in-out;
    }

    .gauge-label.green {
        font-size: 36px;
        font-weight: bold;
        text-anchor: middle;
        dominant-baseline: central;
        fill: #4CAF50;
    }

    .gauge-label.orange {
        font-size: 36px;
        font-weight: bold;
        text-anchor: middle;
        dominant-baseline: central;
        fill: orange;
    }
</style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/6.7.0/d3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            $('#spentTargetTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('spentTarget.data') }}',
                columns: [
                    { data: 'id', name: 'id', visible: false },
                    { data: 'budget', name: 'budget' },
                    { data: 'kol_percentage', name: 'kol_percentage' },
                    { data: 'ads_percentage', name: 'ads_percentage' },
                    { data: 'creative_percentage', name: 'creative_percentage' },
                    { data: 'activation_percentage', name: 'activation_percentage' },
                    { data: 'free_product_percentage', name: 'free_product_percentage' },
                    { data: 'month', name: 'month' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                order: [[0, 'desc']]
            });

            $('#refreshDataButton').on('click', function() {
                // Show a loading indicator or message to inform the user
                Swal.fire({
                    title: 'Refreshing data...',
                    text: 'Please wait while the data is being refreshed.',
                    icon: 'info',
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send AJAX request to refresh data
                $.ajax({
                    url: '{{ route('spentTarget.importOtherSpent') }}',  // Make sure this matches your route
                    method: 'GET',
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'Data imported successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });

                        // Optionally, reload the DataTable or other relevant parts of the page
                        $('#spentTargetTable').DataTable().ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to refresh data. Please try again later.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            $('#addSpentTargetButton').on('click', function() {
                $('#addSpentTargetForm')[0].reset();
                $('#addSpentTargetModal').modal('show');
            });

            @if (session('success'))
                Swal.fire({
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            @endif


            // Handle View, Edit, and Delete actions
            $('#spentTargetTable').on('click', '.viewButton', function (e) {
                e.preventDefault();
                const id = $(this).data('id');

                $.ajax({
                    url: '{{ route('spentTarget.show', ':id') }}'.replace(':id', id),
                    method: 'GET',
                    success: function (response) {
                        $('#view_budget').val(response.budget);
                        $('#view_kol_percentage').val(response.kol_percentage + '%');
                        $('#view_ads_percentage').val(response.ads_percentage + '%');
                        $('#view_creative_percentage').val(response.creative_percentage + '%');
                        $('#view_activation_percentage').val(response.activation_percentage + '%');
                        $('#view_free_product_percentage').val(response.free_product_percentage + '%');
                        $('#view_month').val(response.month);
                        $('#view_tenant_id').val(response.tenant_id);
                        $('#viewSpentTargetModal').modal('show');
                    },
                    error: function () {
                        Swal.fire('Error', 'Unable to fetch data for view.', 'error');
                    },
                });
            });

            $('#spentTargetTable').on('click', '.editButton', function (e) {
                e.preventDefault();
                const id = $(this).data('id');

                $.ajax({
                    url: '{{ route('spentTarget.edit', ':id') }}'.replace(':id', id),
                    method: 'GET',
                    success: function (response) {
                        $('#editSpentTargetForm').attr(
                            'action',
                            '{{ route('spentTarget.update', ':id') }}'.replace(':id', id)
                        );
                        $('#edit_budget').val(response.budget);
                        $('#edit_kol_percentage').val(response.kol_percentage);
                        $('#edit_ads_percentage').val(response.ads_percentage);
                        $('#edit_creative_percentage').val(response.creative_percentage);
                        $('#edit_activation_percentage').val(response.activation_percentage);
                        $('#edit_free_product_percentage').val(response.free_product_percentage);
                        $('#edit_month').val(response.month);
                        $('#edit_tenant_id').val(response.tenant_id);
                        $('#editSpentTargetModal').modal('show');
                    },
                    error: function () {
                        Swal.fire('Error', 'Unable to fetch data for editing.', 'error');
                    },
                });
            });

            $('#editSpentTargetForm').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: form.serialize(),
                    success: function () {
                        Swal.fire('Success', 'Spent target updated successfully.', 'success');
                        $('#editSpentTargetModal').modal('hide');
                        $('#spentTargetTable').DataTable().ajax.reload();
                    },
                    error: function () {
                        Swal.fire('Error', 'Failed to update spent target.', 'error');
                    },
                });
            });

            $('#spentTargetTable').on('click', '.deleteButton', function (e) {
                e.preventDefault();
                const rowData = $('#spentTargetTable').DataTable().row($(this).closest('tr')).data();
                const route = '{{ route('spentTarget.destroy', ':id') }}'.replace(':id', rowData.id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: route,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}',
                            },
                            success: function () {
                                Swal.fire('Deleted!', 'Spent target has been deleted.', 'success');
                                $('#spentTargetTable').DataTable().ajax.reload();
                            },
                            error: function () {
                                Swal.fire('Error!', 'There was an issue deleting the spent target.', 'error');
                            },
                        });
                    }
                });
            });


            function loadSpentTargets() {
                fetch('{{ route('spentTarget.thisMonth') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            document.getElementById('kol-content').innerHTML = 'No data available';
                            document.getElementById('ads-content').innerHTML = 'No data available';
                            document.getElementById('creative-content').innerHTML = 'No data available';
                            document.getElementById('others-content').innerHTML = 'No data available';
                            return;
                        }

                        const target = data[0];
                        const kolTarget = target.kol_target_today;
                        const kolTargetMonth = target.kol_target_spent;
                        const talentShouldGet = target.talent_should_get_total;
                        const daysInMonth = new Date().getDate(); 
                        const remainingDays = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).getDate() - daysInMonth;

                        const kolProgressPercentage = Math.min(
                            Math.round((talentShouldGet / kolTarget) * 100),
                            100
                        );

                        document.getElementById('percentage').textContent = `${kolProgressPercentage}%`;
                        const kolRemainingTarget = kolTarget - talentShouldGet;

                        const kolProgressBar = document.getElementById('kol-progress-bar');
                        const kolProgressLabel = document.getElementById('kol-progress-label');

                        kolProgressBar.style.width = `${kolProgressPercentage}%`;
                        kolProgressBar.setAttribute('aria-valuenow', kolProgressPercentage);
                        kolProgressBar.classList.toggle('bg-success', kolProgressPercentage === 100);
                        kolProgressBar.classList.toggle('bg-info', kolProgressPercentage < 100);
                        kolProgressLabel.textContent = `${kolProgressPercentage}%`;

                        document.getElementById('kol-content').innerHTML = `
                            <p>Target: Rp ${kolTargetMonth.toLocaleString()}</p>
                            <p>Realisasi: Rp ${talentShouldGet.toLocaleString()}</p>
                        `;

                        document.getElementById('remainingKOL').textContent = `Rp ${Math.abs(kolRemainingTarget).toLocaleString()}`;
                        document.getElementById('statusKOL').textContent = kolRemainingTarget >= 0 ? 'Sisa yang harus di Spent' : 'Over Spent';

                        document.getElementById('ads-content').innerHTML = `
                            <p>Target: Rp ${target.ads_target_spent.toLocaleString()}</p>
                            <p>Percentage: ${target.ads_percentage}%</p>
                        `;

                        // Calculate Ads progress
                        const adsProgressPercentage = Math.min(
                            Math.round((target.ads_spent / target.ads_target_spent) * 100),
                            100
                        );

                        // Update Ads progress bar
                        document.getElementById('ads-percentage').textContent = `${adsProgressPercentage}%`;

                        const adsProgressBar = document.getElementById('ads-progress-bar');
                        const adsProgressLabel = document.getElementById('ads-progress-label');

                        adsProgressBar.style.width = `${adsProgressPercentage}%`;
                        adsProgressBar.setAttribute('aria-valuenow', adsProgressPercentage);
                        adsProgressBar.classList.toggle('bg-success', adsProgressPercentage === 100);
                        adsProgressBar.classList.toggle('bg-info', adsProgressPercentage < 100);
                        adsProgressLabel.textContent = `${adsProgressPercentage}%`;

                        const remainingAdsTarget = target.ads_target_spent - target.ads_spent;

                        document.getElementById('remainingAds').textContent = `Rp ${Math.abs(remainingAdsTarget).toLocaleString()}`;
                        const statusAds = remainingAdsTarget >= 0 ? 'Remaining to be spent' : 'Over Spent';
                        document.getElementById('statusAds').textContent = statusAds;

                        // Set Ads Content
                        document.getElementById('ads-content').innerHTML = `
                            <p>Target: Rp ${target.ads_target_spent.toLocaleString()}</p>
                            <p>Realized: Rp ${target.ads_spent.toLocaleString()}</p>
                        `;

                        document.getElementById('creative-content').innerHTML = `
                            <p>Target: Rp ${target.creative_target_spent.toLocaleString()}</p>
                            <p>Percentage: ${target.creative_percentage}%</p>
                        `;

                        document.getElementById('activation-content').innerHTML = `
                            <p>Target: Rp ${target.activation_target_spent.toLocaleString()}</p>
                            <p>Percentage: ${target.activation_percentage}%</p>
                        `;

                        document.getElementById('free-product-content').innerHTML = `
                            <p>Target: Rp ${target.free_product_target_spent.toLocaleString()}</p>
                            <p>Percentage: ${target.free_product_percentage}%</p>
                        `;

                        document.getElementById('others-content').innerHTML = `
                            <p>Target (Other): Rp ${target.other_target_spent?.toLocaleString() || 0}</p>
                            <p>Target (Affiliate): Rp ${target.affiliate_target_spent?.toLocaleString() || 0}</p>
                            <p>Other Percentage: ${target.other_percentage || '0'}%</p>
                            <p>Affiliate Percentage: ${target.affiliate_percentage || '0'}%</p>
                        `;
                    })
                    .catch(error => {
                        console.error('Error fetching spent target data:', error);
                        document.getElementById('kol-content').innerHTML = 'Error loading data';
                        document.getElementById('ads-content').innerHTML = 'Error loading data';
                        document.getElementById('creative-content').innerHTML = 'Error loading data';
                        document.getElementById('others-content').innerHTML = 'Error loading data';
                    });
            }

            loadSpentTargets();
            function fetchAndRenderChart() {
                fetch('{{ route("spentTarget.byDay") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (!data.labels || !data.datasets) {
                            console.error('Invalid data format from API.');
                            return;
                        }
                        const ctx = document.getElementById('kolLineChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.labels,
                                datasets: data.datasets
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                    },
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Dates'
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Amount (in Thousands)'
                                        }
                                    }
                                },
                            },
                        });
                    })
                    .catch(error => console.error('Error fetching chart data:', error));
            }
            fetchAndRenderChart();

            function fetchAndRenderAdsChart() {
                fetch('{{ route("spentTarget.adsByDay") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (!data.labels || !data.datasets) {
                            console.error('Invalid data format from API.');
                            return;
                        }

                        const ctx = document.getElementById('adsLineChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.labels,
                                datasets: data.datasets
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                    },
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Dates'
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Amount (in Thousands)'
                                        }
                                    }
                                },
                            },
                        });
                    })
                    .catch(error => console.error('Error fetching chart data:', error));
            }
            fetchAndRenderAdsChart();
        });
        
    </script>
@stop
