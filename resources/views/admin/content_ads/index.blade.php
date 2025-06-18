@extends('adminlte::page')

@section('title', 'Content Ads')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Content Ads Management</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Content Ads</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Product Composition</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-responsive">
                        <canvas id="productDonutChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Funneling Composition</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-responsive">
                        <canvas id="funnelingDonutChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Content Ads Management</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addContentAdsModal">
                            <i class="fas fa-plus"></i> Add New Content Ads
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="importFromGoogleSheets()">
                            <i class="fas fa-file-import"></i> Import from Google Sheets
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="loadKpiData()">
                            <i class="fas fa-chart-bar"></i> Refresh KPI
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select id="statusFilter" class="form-control">
                                <option value="">All Status</option>
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="productFilter" class="form-control">
                                <option value="">All Products</option>
                                @foreach($productOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="platformFilter" class="form-control">
                                <option value="">All Platforms</option>
                                @foreach($platformOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button id="filterBtn" class="btn btn-info btn-block">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </div>

                    <!-- Content Ads Table -->
                    <table id="contentAdsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Request Date</th>
                                <th>Status</th>
                                <th>Product</th>
                                <th>Platform</th>
                                <th>Funneling</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Report Modal -->
    <div class="modal fade" id="kpiReportModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">KPI Report</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Per Product Performance</h6>
                            <canvas id="productChart"></canvas>
                        </div>
                        <div class="col-md-6">
                            <h6>Per Funnel Performance</h6>
                            <canvas id="funnelChart"></canvas>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Daily Performance Per Person</h6>
                            <div id="dailyPerformanceTable"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @include('admin.content_ads.modals.add_content_ads_modal')
    @include('admin.content_ads.modals.edit_content_ads_modal')
    @include('admin.content_ads.modals.view_content_ads_modal')
    @include('admin.content_ads.modals.step_modal')
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Global chart variables
let productDonutChart = null;
let funnelingDonutChart = null;

$(document).ready(function() {
    // Initialize DataTables
    var table = $('#contentAdsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('contentAds.data') }}', // Adjust route name as needed
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.product = $('#productFilter').val();
                d.platform = $('#platformFilter').val();
                d.funneling = $('#funnelingFilter').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'request_date_formatted', name: 'request_date' },
            { data: 'status_badge', name: 'status' },
            { data: 'product_button', name: 'product', orderable: true, searchable: true }, // Product column
            { data: 'platform_button', name: 'platform', orderable: true, searchable: true },
            { data: 'funneling_button', name: 'funneling', orderable: true, searchable: true },
            { data: 'created_date', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']]
    });

    // Enhanced Filter functionality - now also updates charts
    $('#filterBtn').on('click', function() {
        table.draw();
        loadFilteredKpiData(); // Load filtered KPI data for charts
    });

    $('#statusFilter, #productFilter, #platformFilter').on('change', function() {
        table.draw();
        loadFilteredKpiData(); // Load filtered KPI data for charts
    });

    // Load KPI data on page load
    loadKpiData();

    // Handle Add Content Ads Form Submit
    $('#addContentAdsForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route('contentAds.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addContentAdsModal').modal('hide');
                    table.draw();
                    loadFilteredKpiData(); // Use filtered data
                    toastr.success(response.message);
                    $('#addContentAdsForm')[0].reset();
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr);
            }
        });
    });

    // Handle Edit Content Ads Form Submit
    $('#editContentAdsForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var url = $(this).attr('action');
        
        $.ajax({
            url: url,
            method: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editContentAdsModal').modal('hide');
                    table.draw();
                    loadFilteredKpiData(); // Use filtered data
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr);
            }
        });
    });

    // Handle Step Form Submit
    $('#stepForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var url = $(this).attr('action');
        
        $.ajax({
            url: url,
            method: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#stepModal').modal('hide');
                    table.draw();
                    loadFilteredKpiData(); // Use filtered data
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr);
            }
        });
    });

    // Handle View button click
    $('#contentAdsTable').on('click', '.viewButton', function() {
        var id = $(this).data('id');
        loadContentAdsDetails(id);
    });

    // Handle Edit button click
    $('#contentAdsTable').on('click', '.editButton', function() {
        var id = $(this).data('id');
        loadContentAdsForEdit(id);
    });

    // Handle Step button click
    $('#contentAdsTable').on('click', '.stepButton', function() {
        var id = $(this).data('id');
        var step = $(this).data('step');
        loadStepForm(id, step);
    });

    // Handle Delete button click
    $('#contentAdsTable').on('click', '.deleteButton', function() {
        var id = $(this).data('id');
        var route = '{{ route('contentAds.destroy', ':id') }}'.replace(':id', id);
        
        deleteAjax(route, id, table);
    });

    // Clear form on modal close
    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        clearValidationErrors();
    });
});

// Enhanced Load KPI Data with Filters
function loadFilteredKpiData() {
    // Get current filter values
    const filters = {
        status: $('#statusFilter').val(),
        product: $('#productFilter').val(),
        platform: $('#platformFilter').val(),
        funneling: $('#funnelingFilter').val()
    };

    $.ajax({
        url: '{{ route('contentAds.kpiData') }}',
        method: 'GET',
        data: filters, // Pass filters to backend
        success: function(response) {
            if (response.success) {
                updateKpiCards(response.data);
                updateDonutCharts(response.data);
            }
        },
        error: function(xhr) {
            console.error('Error loading filtered KPI data:', xhr);
        }
    });
}

// Original Load KPI Data (without filters)
function loadKpiData() {
    $.ajax({
        url: '{{ route('contentAds.kpiData') }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateKpiCards(response.data);
                updateDonutCharts(response.data);
            }
        },
        error: function(xhr) {
            console.error('Error loading KPI data:', xhr);
        }
    });
}

// Update KPI Cards
function updateKpiCards(data) {
    $('#totalCompleted').text(data.total_completed || 0);
    $('#totalPending').text(data.total_pending || 0);
    $('#totalToday').text(data.total_created_today || 0);
    
    // Count active products
    var activeProducts = data.per_product ? Object.keys(data.per_product).length : 0;
    $('#totalProductTypes').text(activeProducts);
}

// Update Donut Charts with enhanced filtering display
function updateDonutCharts(data) {
    // Update Product Donut Chart
    updateProductDonutChart(data.per_product || {});
    
    // Update Funneling Donut Chart
    updateFunnelingDonutChart(data.per_funnel || {});
    
    // Update chart titles to show if filters are applied
    updateChartTitles();
}

// Update chart titles to indicate active filters
function updateChartTitles() {
    const activeFilters = [];
    
    if ($('#statusFilter').val()) {
        activeFilters.push('Status: ' + $('#statusFilter option:selected').text());
    }
    if ($('#productFilter').val()) {
        activeFilters.push('Product: ' + $('#productFilter option:selected').text());
    }
    if ($('#platformFilter').val()) {
        activeFilters.push('Platform: ' + $('#platformFilter option:selected').text());
    }
    if ($('#funnelingFilter').val()) {
        activeFilters.push('Funneling: ' + $('#funnelingFilter option:selected').text());
    }
    
    // Update product chart title
    let productTitle = 'Product Composition';
    if (activeFilters.length > 0) {
        productTitle += ' (Filtered: ' + activeFilters.join(', ') + ')';
    }
    $('.card:has(#productDonutChart) .card-title').text(productTitle);
    
    // Update funneling chart title
    let funnelingTitle = 'Funneling Composition';
    if (activeFilters.length > 0) {
        funnelingTitle += ' (Filtered: ' + activeFilters.join(', ') + ')';
    }
    $('.card:has(#funnelingDonutChart) .card-title').text(funnelingTitle);
}

// Product Donut Chart with enhanced empty state handling
function updateProductDonutChart(productData) {
    const ctx = document.getElementById('productDonutChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (productDonutChart) {
        productDonutChart.destroy();
    }

    // Check if there's data to display
    if (!productData || Object.keys(productData).length === 0) {
        // Show empty state
        productDonutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['No Data'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                cutout: '60%'
            },
            plugins: [{
                id: 'emptyState',
                beforeDraw: function(chart) {
                    const ctx = chart.ctx;
                    const width = chart.width;
                    const height = chart.height;
                    
                    ctx.restore();
                    ctx.font = "16px Arial";
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#6c757d';
                    ctx.fillText('No data available', width / 2, height / 2);
                    ctx.save();
                }
            }]
        });
        return;
    }

    // Prepare data
    const labels = Object.keys(productData);
    const values = Object.values(productData).map(item => item.count || 0);
    
    // Product color mapping based on your existing button colors
    const productColors = {
        'CLE-XFO-008': '#87CEEB',
        'CLE-JB30-001': '#F4D03F',
        'CLE-CLNDLA-025': '#F8A488',
        'CLE-RS-047': '#DC143C',
        'CL-GS': '#4682B4',
        'CL-TNR': '#B0C4DE',
        'CLE-NEG-071': '#90EE90',
        'CLE-ASG-059': '#228B22',
        'CL-JBRS': '#FFB6C1',
        'CLE-BD-XFOJB30-017': '#ADD8E6'
    };

    const backgroundColors = labels.map(label => productColors[label] || '#6c757d');

    productDonutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: backgroundColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.formattedValue + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}

// Funneling Donut Chart with enhanced empty state handling
function updateFunnelingDonutChart(funnelingData) {
    const ctx = document.getElementById('funnelingDonutChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (funnelingDonutChart) {
        funnelingDonutChart.destroy();
    }

    // Check if there's data to display
    if (!funnelingData || Object.keys(funnelingData).length === 0) {
        // Show empty state
        funnelingDonutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['No Data'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                cutout: '60%'
            },
            plugins: [{
                id: 'emptyState',
                beforeDraw: function(chart) {
                    const ctx = chart.ctx;
                    const width = chart.width;
                    const height = chart.height;
                    
                    ctx.restore();
                    ctx.font = "16px Arial";
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#6c757d';
                    ctx.fillText('No data available', width / 2, height / 2);
                    ctx.save();
                }
            }]
        });
        return;
    }

    // Prepare data
    const labels = Object.keys(funnelingData);
    const values = Object.values(funnelingData).map(item => item.count || 0);
    
    // Funneling color mapping based on your existing button colors
    const funnelingColors = {
        'TOFU': '#28a745',  // Green
        'MOFU': '#17a2b8',  // Info blue
        'BOFU': '#007bff'   // Primary blue
    };

    const backgroundColors = labels.map(label => funnelingColors[label] || '#6c757d');

    funnelingDonutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: backgroundColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.formattedValue + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}

// Function to clear all filters and reset charts
function clearAllFilters() {
    $('#statusFilter').val('');
    $('#productFilter').val('');
    $('#platformFilter').val('');
    $('#funnelingFilter').val('');
    
    // Reset table and charts
    table.draw();
    loadKpiData(); // Load unfiltered data
}

// Function to load content ads details
function loadContentAdsDetails(id) {
    $.ajax({
        url: '{{ route('contentAds.details', ':id') }}'.replace(':id', id),
        method: 'GET',
        success: function(response) {
            if (response.success) {
                populateViewModal(response.data);
                $('#viewContentAdsModal').modal('show');
            }
        },
        error: function(xhr) {
            toastr.error('Error loading content ads details');
        }
    });
}

// Function to load content ads for editing
function loadContentAdsForEdit(id) {
    $.ajax({
        url: '{{ route('contentAds.details', ':id') }}'.replace(':id', id),
        method: 'GET',
        success: function(response) {
            if (response.success) {
                populateEditModal(response.data);
                $('#editContentAdsForm').attr('action', '{{ route('contentAds.update', ':id') }}'.replace(':id', id));
                $('#editContentAdsModal').modal('show');
            }
        },
        error: function(xhr) {
            toastr.error('Error loading content ads data');
        }
    });
}

// Function to load step form
function loadStepForm(id, step) {
    $.ajax({
        url: '{{ route('contentAds.details', ':id') }}'.replace(':id', id),
        method: 'GET',
        success: function(response) {
            if (response.success) {
                populateStepModal(response.data, step);
                $('#stepForm').attr('action', '{{ route('contentAds.updateStep', [':id', ':step']) }}'.replace(':id', id).replace(':step', step));
                $('#stepModal').modal('show');
            }
        },
        error: function(xhr) {
            toastr.error('Error loading step data');
        }
    });
}

// Function to populate view modal
function populateViewModal(data) {
    $('#view_link_ref').text(data.link_ref || '-');
    $('#view_desc_request').text(data.desc_request || '-');
    $('#view_product').text(data.product || '-');
    $('#view_platform').text(data.platform || '-');
    $('#view_funneling').text(data.funneling || '-');
    $('#view_editor').text(data.editor || '-');
    $('#view_status').text(data.status_label || '-');
    $('#view_filename').text(data.filename || '-');
    $('#view_link_drive').text(data.link_drive || '-');
}

// Function to populate edit modal
function populateEditModal(data) {
    $('#edit_link_ref').val(data.link_ref);
    $('#edit_desc_request').val(data.desc_request);
    $('#edit_product').val(data.product);
    $('#edit_platform').val(data.platform);
    $('#edit_funneling').val(data.funneling);
    $('#edit_request_date').val(data.request_date);
    $('#edit_link_drive').val(data.link_drive);
    $('#edit_editor').val(data.editor);
    $('#edit_filename').val(data.filename);
}

// Function to populate step modal
function populateStepModal(data, step) {
    $('#stepModalTitle').text('Step ' + step + ' - ' + getStepTitle(step));
    $('#stepModalBody').html(getStepFormFields(data, step));
}

// Function to get step title
function getStepTitle(step) {
    const titles = {
        1: 'Initial Request',
        2: 'Link Drive & Task Completion',
        3: 'File Naming'
    };
    return titles[step] || 'Unknown Step';
}

// Function to get step form fields
function getStepFormFields(data, step) {
    switch(step) {
        case 1:
            return `
                <div class="form-group">
                    <label for="step_link_ref">Link Reference</label>
                    <input type="text" class="form-control" name="link_ref" id="step_link_ref" value="${data.link_ref || ''}">
                </div>
                <div class="form-group">
                    <label for="step_desc_request">Description Request</label>
                    <textarea class="form-control" name="desc_request" id="step_desc_request" rows="4">${data.desc_request || ''}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="step_product">Product</label>
                            <select class="form-control" name="product" id="step_product">
                                <option value="">Select Product</option>
                                @foreach($productOptions as $key => $label)
                                    <option value="{{ $key }}" ${data.product === '{{ $key }}' ? 'selected' : ''}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="step_platform">Platform</label>
                            <select class="form-control" name="platform" id="step_platform">
                                <option value="">Select Platform</option>
                                @foreach($platformOptions as $key => $label)
                                    <option value="{{ $key }}" ${data.platform === '{{ $key }}' ? 'selected' : ''}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="step_funneling">Funneling</label>
                            <select class="form-control" name="funneling" id="step_funneling">
                                <option value="">Select Funneling</option>
                                @foreach($funnelingOptions as $key => $label)
                                    <option value="{{ $key }}" ${data.funneling === '{{ $key }}' ? 'selected' : ''}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="step_request_date">Request Date</label>
                    <input type="date" class="form-control" name="request_date" id="step_request_date" value="${data.request_date || ''}">
                </div>
            `;
        case 2:
            return `
                <div class="form-group">
                    <label for="step_link_drive">Link Drive</label>
                    <input type="text" class="form-control" name="link_drive" id="step_link_drive" value="${data.link_drive || ''}">
                </div>
                <div class="form-group">
                    <label for="step_editor">Editor</label>
                    <select class="form-control" name="editor" id="step_editor">
                        <option value="">Select Editor</option>
                        @foreach($editorOptions as $key => $label)
                            <option value="{{ $key }}" ${data.editor === '{{ $key }}' ? 'selected' : ''}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            `;
        case 3:
            return `
                <div class="form-group">
                    <label for="step_filename">File Naming</label>
                    <input type="text" class="form-control" name="filename" id="step_filename" value="${data.filename || ''}">
                </div>
            `;
        default:
            return '<p>Invalid step</p>';
    }
}

// Helper functions
function handleAjaxError(xhr) {
    var errors = xhr.responseJSON?.errors || {};
    var message = xhr.responseJSON?.message || 'An error occurred';
    
    clearValidationErrors();
    
    if (Object.keys(errors).length > 0) {
        $.each(errors, function(field, messages) {
            var input = $('[name="' + field + '"]');
            input.addClass('is-invalid');
            input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
        });
    } else {
        toastr.error(message);
    }
}

function clearValidationErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

function deleteAjax(route, id, table) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: route,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        table.draw();
                        loadFilteredKpiData(); // Use filtered data
                        Swal.fire('Deleted!', response.message, 'success');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'An error occurred while deleting.', 'error');
                }
            });
        }
    });
}

// Import from Google Sheets function
function importFromGoogleSheets() {
    Swal.fire({
        title: 'Import from Google Sheets?',
        text: 'This will import data from the specified Google Sheet. Continue?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, import now!',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: '{{ route('contentAds.import_gsheet') }}',
                method: 'GET',
                timeout: 60000, // 60 seconds timeout
                success: function(response) {
                    return response;
                },
                error: function(xhr) {
                    Swal.showValidationMessage(
                        `Import failed: ${xhr.responseJSON?.message || 'Unknown error'}`
                    );
                }
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const data = result.value;
            Swal.fire({
                title: 'Import Completed!',
                html: `
                    <div class="text-left">
                        <p><strong>Total Rows:</strong> ${data.total_rows}</p>
                        <p><strong>Processed:</strong> ${data.processed_rows}</p>
                        <p><strong>New Created:</strong> ${data.new_content_ads_created}</p>
                        <p><strong>Updated:</strong> ${data.content_ads_updated}</p>
                        <p><strong>Skipped:</strong> ${data.skipped_rows}</p>
                    </div>
                `,
                icon: 'success'
            }).then(() => {
                // Refresh the table and KPI data
                table.draw();
                loadFilteredKpiData(); // Use filtered data
            });
        }
    });
}
</script>
@stop