@extends('adminlte::page')

@section('title', 'Affiliate Program')

@section('content_header')
    <h1>Affiliate Monitor</h1>
@stop

@section('content')
<div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="starterCount">0</h3>
                    <p>Starter Level</p>
                </div>
                <div class="icon">
                    <i class="fas fa-seedling"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="influencerCount">0</h3>
                    <p>Influencer Level</p>
                </div>
                <div class="icon">
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="legendCount">0</h3>
                    <p>Legend Level</p>
                </div>
                <div class="icon">
                    <i class="fas fa-crown"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="bestAffiliateCount">0</h3>
                    <p>Best Affiliate</p>
                </div>
                <div class="icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="kolTable" class="table table-bordered table-striped" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.username') }}</th>
                                <th>Affiliate Status</th>
                                <th>Level</th>
                                <th>{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Level Modal -->
    <div class="modal fade" id="editLevelModal" tabindex="-1" role="dialog" aria-labelledby="editLevelModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editLevelModalLabel">
                        <i class="fas fa-level-up-alt"></i> Set KOL Level
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editLevelForm" method="POST">
                    @csrf
                    
                    <div class="modal-body">
                        <div id="editLevelLoader" class="text-center" style="display: none;">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p>Loading KOL data...</p>
                        </div>
                        
                        <div id="editLevelContent" style="display: none;">
                            <!-- Username Display -->
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">Username:</label>
                                <div class="col-md-8">
                                    <p class="form-control-plaintext" id="display_username"></p>
                                </div>
                            </div>

                            <!-- Current Level Display -->
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">Current Level:</label>
                                <div class="col-md-8">
                                    <p class="form-control-plaintext" id="display_current_level">-</p>
                                </div>
                            </div>

                            <!-- Level Selection -->
                            <div class="form-group row">
                                <label for="edit_level" class="col-md-4 col-form-label text-md-right">New Level <span class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    <select class="form-control" name="level" id="edit_level" required>
                                        <option value="">Select Level</option>
                                        <option value="Starter">Starter</option>
                                        <option value="Influencer">Influencer</option>
                                        <option value="Legend">Legend</option>
                                        <option value="Best Affiliate">Best Affiliate</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveLevelBtn">
                            <i class="fas fa-save"></i> Update Level
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    const kolTableSelector = $('#kolTable');
    const statusAffiliateSelector = $('#filterStatusAffiliate');
    let currentKolId = null;
    let kolTable;

    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function loadKpiDataMonitor() {
        $.get("{{ route('kol.monitor_kpi') }}", function(data) {
            $('#starterCount').text(data.starter_count || 0);
            $('#influencerCount').text(data.influencer_count || 0);
            $('#legendCount').text(data.legend_count || 0);
            $('#bestAffiliateCount').text(data.best_affiliate_count || 0);
        }).fail(function() {
            console.error('Failed to load KPI data');
            $('#starterCount, #influencerCount, #legendCount, #bestAffiliateCount').text('0');
        });
    }

    // Initialize DataTable with proper error handling
    function initializeDataTable() {
        // Destroy existing table if it exists
        if ($.fn.DataTable.isDataTable('#kolTable')) {
            $('#kolTable').DataTable().destroy();
        }
        
        // Clear the table
        $('#kolTable').empty();
        
        kolTable = kolTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('kol.monitor_get') }}",
                data: function (d) {
                    d.statusAffiliate = statusAffiliateSelector.val();
                },
                error: function(xhr, error, code) {
                    console.log('AJAX Error:', error);
                }
            },
            columns: [
                {
                    data: 'username', 
                    name: 'username',
                    title: 'Username'
                },
                {
                    data: 'status_affiliate_display', 
                    name: 'status_affiliate', 
                    title: 'Affiliate Status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'level_display', 
                    name: 'level', 
                    title: 'Level',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'actions', 
                    name: 'actions',
                    title: 'Action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[0, 'desc']],
            drawCallback: function() {
                loadKpiDataMonitor();
            },
            initComplete: function() {
                console.log('DataTable initialized successfully');
            },
            error: function(xhr, error, code) {
                console.log('DataTable error:', error);
            }
        });
    }

    // Initialize the table
    initializeDataTable();

    statusAffiliateSelector.change(function() {
        if (kolTable) {
            kolTable.draw();
        }
    });

    $(function () {
        console.log('Document ready - DataTable should be initialized');
    });

    // Function for editing level only
    function openEditLevelModal(kolId) {
        currentKolId = kolId;
        
        // Reset form and show loader
        $('#editLevelForm')[0].reset();
        $('#editLevelContent').hide();
        $('#editLevelLoader').show();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Open modal
        $('#editLevelModal').modal('show');

        // Load KOL data
        $.get(`{{ route('kol.get-level-data', ':kolId') }}`.replace(':kolId', kolId))
            .done(function(data) {
                populateEditLevelForm(data);
                $('#editLevelLoader').hide();
                $('#editLevelContent').show();
            })
            .fail(function() {
                $('#editLevelModal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load KOL data. Please try again.',
                    confirmButtonColor: '#d33'
                });
            });
    }

    function populateEditLevelForm(data) {
        $('#display_username').text(data.username || '-');
        $('#display_current_level').text(data.level || 'Not Set');
        $('#edit_level').val(data.level || '');
        
        // Set form action using route name
        $('#editLevelForm').attr('action', `{{ route('kol.update-level', ':kolId') }}`.replace(':kolId', data.id));
    }

    // Handle level form submission
    $('#editLevelForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#saveLevelBtn');
        const originalBtnText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Get form data
        const formData = {
            _token: $('input[name="_token"]').val(),
            level: $('#edit_level').val()
        };
        
        console.log('Submitting to:', form.attr('action'));
        console.log('Form data:', formData);
        
        // Submit form
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('Success response:', response);
                
                // Close modal
                $('#editLevelModal').modal('hide');
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'KOL level updated successfully.',
                    confirmButtonColor: '#28a745',
                    timer: 2000,
                    timerProgressBar: true
                });
                
                // Refresh DataTable and KPI
                if (kolTable) {
                    kolTable.ajax.reload(null, false);
                }
                loadKpiDataMonitor();
            },
            error: function(xhr) {
                console.log('Error response:', xhr);
                console.log('Status:', xhr.status);
                console.log('Response text:', xhr.responseText);
                
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON?.errors || {};
                    
                    Object.keys(errors).forEach(function(field) {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(errors[field][0]);
                    });
                    
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Error',
                        text: 'Please select a valid level.',
                        confirmButtonColor: '#ffc107'
                    });
                } else if (xhr.status === 405) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Method Not Allowed',
                        text: 'Route error. URL: ' + form.attr('action'),
                        confirmButtonColor: '#d33'
                    });
                } else {
                    // Other errors
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to update KOL level. Please try again.',
                        confirmButtonColor: '#d33'
                    });
                }
            },
            complete: function() {
                // Restore button state
                submitBtn.prop('disabled', false);
                submitBtn.html(originalBtnText);
            }
        });
    });

    // Modal cleanup on hide
    $('#editLevelModal').on('hidden.bs.modal', function() {
        currentKolId = null;
        $('#editLevelForm')[0].reset();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    });

    // Backward compatibility function
    function openEditModal(kolId) {
        openEditLevelModal(kolId);
    }

    // Make functions globally available
    window.openEditLevelModal = openEditLevelModal;
    window.openEditModal = openEditModal;
</script>
@stop