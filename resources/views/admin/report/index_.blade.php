@extends('adminlte::page')

@section('title', 'Report Analysis')

@section('content_header')
    <h1>Report Analysis</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-auto">
                                <button class="btn btn-primary" data-toggle="modal" data-target="#addReportModal">
                                    <i class="fas fa-plus"></i> Add Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($reports as $report)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <!-- Thumbnail -->
                                <div class="position-relative">
                                    <img src="{{ $report->thumbnail ? asset('storage/' . $report->thumbnail) : 'https://via.placeholder.com/400x200' }}"
                                        class="card-img-top"
                                        style="height: 200px; object-fit: cover;"
                                        alt="{{ $report->title }}">
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    <!-- Title -->
                                    <h5 class="card-title mb-3">{{ $report->title }}</h5>
                                    
                                    <!-- Type and Month info -->
                                    <div class="d-flex justify-content-between mb-3">
                                        <div>
                                            <small class="text-muted">Type:</small>
                                            <span class="ml-1">{{ ucfirst($report->type) }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted">Month:</small>
                                            <span class="ml-1">{{ \Carbon\Carbon::parse($report->month)->format('M Y') }}</span>
                                        </div>
                                    </div>

                                    <!-- Stats row -->
                                    <div class="d-flex justify-content-start mt-auto">
                                        <!-- Action buttons -->
                                        <div class="ml-auto">
                                            <a href="{{ route('reports.show', $report->id) }}" 
                                            class="btn btn-sm btn-primary">
                                                View
                                            </a>
                                            <button class="btn btn-sm btn-success edit-report" 
                                                    data-id="{{ $report->id }}"
                                                    data-title="{{ $report->title }}"
                                                    data-description="{{ $report->description }}"
                                                    data-type="{{ $report->type }}"
                                                    data-platform="{{ $report->platform }}"
                                                    data-month="{{ $report->month }}"
                                                    data-link="{{ $report->link }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-report" 
                                                    data-id="{{ $report->id }}"
                                                    data-title="{{ $report->title }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Report Modal -->
    <div class="modal fade" id="addReportModal" tabindex="-1" role="dialog" aria-labelledby="addReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addReportForm" action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addReportModalLabel">Add New Report</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="thumbnail">Thumbnail</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="thumbnail" name="thumbnail" accept="image/*">
                                <label class="custom-file-label" for="thumbnail">Choose file</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="type">Type</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="sales">Sales</option>
                                <option value="marketing">Marketing</option>
                                <option value="inventory">Inventory</option>
                                <option value="financial">Financial</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="platform">Platform</label>
                            <select class="form-control" id="platform" name="platform" required>
                                <option value="">Select Platform</option>
                                <option value="shopee">Shopee</option>
                                <option value="lazada">Lazada</option>
                                <option value="tokopedia">Tokopedia</option>
                                <option value="tiktok">Tiktok Shop</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="link">Tableau Embed Code</label>
                            <textarea class="form-control" id="link" name="link" rows="5" required 
                                      placeholder="Paste your Tableau embed code here"></textarea>
                            <small class="form-text text-muted">Paste the complete Tableau embed code including the script tag</small>
                        </div>

                        <div class="form-group">
                            <label for="month">Month</label>
                            <input type="month" class="form-control" id="month" name="month" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Edit Report Modal -->
<div class="modal fade" id="editReportModal" tabindex="-1" role="dialog" aria-labelledby="editReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editReportForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editReportModalLabel">Edit Report</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_title">Title</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_thumbnail">Thumbnail</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="edit_thumbnail" name="thumbnail" accept="image/*">
                            <label class="custom-file-label" for="edit_thumbnail">Choose file</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_type">Type</label>
                        <select class="form-control" id="edit_type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="sales">Sales</option>
                            <option value="marketing">Marketing</option>
                            <option value="inventory">Inventory</option>
                            <option value="financial">Financial</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_platform">Platform</label>
                        <select class="form-control" id="edit_platform" name="platform" required>
                            <option value="">Select Platform</option>
                            <option value="shopee">Shopee</option>
                            <option value="lazada">Lazada</option>
                            <option value="tokopedia">Tokopedia</option>
                            <option value="tiktok">Tiktok Shop</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_link">Tableau Embed Code</label>
                        <textarea class="form-control" id="edit_link" name="link" rows="5" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_month">Month</label>
                        <input type="month" class="form-control" id="edit_month" name="month" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://public.tableau.com/javascripts/api/viz_v1.js"></script>
<script>
$(document).ready(function() {
    // Handle dashboard view button click
    // Handle dashboard view button click
    $('.view-dashboard').on('click', function() {
        const title = $(this).data('title');
        const reportId = $(this).data('report-id');
        
        // Update modal title
        $('#tableauModalLabel').text(title);
        
        // Fetch and display dashboard
        $.ajax({
            url: `/admin/report/${reportId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Clear previous content
                    $('#tableauContainer').empty();
                    
                    // Insert the Tableau embed code
                    $('#tableauContainer').html(response.data.link);
                }
            },
            error: function(xhr) {
                console.error('Error loading dashboard:', xhr);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to load dashboard',
                    icon: 'error'
                });
            }
        });
    });

    // Clean up when modal is closed
    $('#tableauModal').on('hidden.bs.modal', function () {
        // Just empty the container - this will remove all Tableau elements
        $('#tableauContainer').empty();
        
        // Remove any Tableau scripts that might have been added
        $('script[src*="tableau"]').remove();
    });

    // Handle file input change
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Handle form submission
    $('#addReportForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'), // This will use the route from the form action
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Report added successfully',
                        icon: 'success'
                    }).then((result) => {
                        window.location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: 'There was an error adding the report',
                    icon: 'error'
                });
            }
        });
    });

    $('.delete-report').click(function() {
        const reportId = $(this).data('id');
        const reportTitle = $(this).data('title');
        
        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to delete the report "${reportTitle}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send delete request
                $.ajax({
                    url: `/admin/report/${reportId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                'The report has been deleted.',
                                'success'
                            ).then(() => {
                                // Reload page after successful deletion
                                window.location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete the report.',
                                'error'
                            );
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'There was an error deleting the report.',
                            'error'
                        );
                    }
                });
            }
        });
    });
    
    $('.edit-report').click(function() {
        const id = $(this).data('id');
        const title = $(this).data('title');
        const description = $(this).data('description');
        const type = $(this).data('type');
        const platform = $(this).data('platform');
        const month = $(this).data('month');
        const link = $(this).data('link');
        
        // Set form action
        $('#editReportForm').attr('action', `/admin/report/${id}`);
        
        // Fill form fields
        $('#edit_title').val(title);
        $('#edit_description').val(description);
        $('#edit_type').val(type);
        $('#edit_platform').val(platform);
        $('#edit_month').val(month);
        $('#edit_link').val(link);
        
        // Show modal
        $('#editReportModal').modal('show');
    });
    // Handle edit form submission
    $('#editReportForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Report updated successfully',
                        icon: 'success'
                    }).then((result) => {
                        window.location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: 'There was an error updating the report',
                    icon: 'error'
                });
            }
        });
    });
});
</script>
@stop
