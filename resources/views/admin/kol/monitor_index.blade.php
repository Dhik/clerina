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
                    <table id="kolTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="kol-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.username') }}</th>
                            <th width="8%">Affiliate Status</th>
                            <th width="8%">{{ trans('labels.action') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit KOL Modal -->
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
                    @method('PUT')
                    
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

    let kolTable = kolTableSelector.DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: "{{ route('kol.monitor_get') }}",
            data: function (d) {
                d.statusAffiliate = statusAffiliateSelector.val();
            }
        },
        columns: [
            {
                data: 'username', 
                name: 'username',
                orderable: true
            },
            {
                data: 'status_affiliate_display', 
                name: 'status_affiliate', 
                orderable: false,
                searchable: false
            },
            {
                data: 'level_display', 
                name: 'level', 
                orderable: false,
                searchable: false
            },
            {
                data: 'actions', 
                name: 'actions',
                orderable: false,
                searchable: false
            }
        ],
        columnDefs: [
            {
                targets: [1, 2, 3],
                orderable: false
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

    statusAffiliateSelector.change(function() {
        kolTable.draw();
    });

    $(function () {
        kolTable.draw();
    });

    // New function for editing level only
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

        $('#editLevelModal').on('hidden.bs.modal', function() {
            currentKolId = null;
            $('#editLevelForm')[0].reset();
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        });
        
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
        
        // Set form action
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
        
        // Submit form
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
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
                kolTable.ajax.reload(null, false);
                loadKpiDataMonitor();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    
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
                } else {
                    // Other errors
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update KOL level. Please try again.',
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

    // Original edit function for full editing (if you still need it)
    function openEditModal(kolId) {
        currentKolId = kolId;
        
        // Reset form and show loader
        $('#editKolForm')[0].reset();
        $('#editFormContent').hide();
        $('#editFormLoader').show();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Open modal
        $('#editKolModal').modal('show');

        $('#editKolModal').on('hidden.bs.modal', function() {
            currentKolId = null;
            $('#editKolForm')[0].reset();
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        });
        
        // Load KOL data
        $.get(`{{ route('kol.edit-data', ':kolId') }}`.replace(':kolId', kolId))
            .done(function(data) {
                populateEditForm(data);
                $('#editFormLoader').hide();
                $('#editFormContent').show();
            })
            .fail(function() {
                $('#editKolModal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to load KOL data. Please try again.',
                    confirmButtonColor: '#d33'
                });
            });
    }

    function populateEditForm(data) {
        $('#edit_username').val(data.username || '');
        $('#edit_phone_number').val(data.phone_number || '');
        
        // Hidden fields to preserve existing required data
        $('#edit_channel').val(data.channel || '');
        $('#edit_niche').val(data.niche || '');
        $('#edit_average_view').val(data.average_view || '');
        $('#edit_skin_type').val(data.skin_type || '');
        $('#edit_skin_concern').val(data.skin_concern || '');
        $('#edit_content_type').val(data.content_type || '');
        $('#edit_rate').val(data.rate || '');
        $('#edit_pic_contact').val(data.pic_contact || '');
        
        // Set status_affiliate dropdown
        $('#edit_status_affiliate').val(data.status_affiliate || '');
        
        // Set radio buttons for views_last_9_post
        if (data.views_last_9_post === 1 || data.views_last_9_post === '1' || data.views_last_9_post === true) {
            $('#edit_views_yes').prop('checked', true);
        } else if (data.views_last_9_post === 0 || data.views_last_9_post === '0' || data.views_last_9_post === false) {
            $('#edit_views_no').prop('checked', true);
        } else {
            $('#edit_views_null').prop('checked', true);
        }
        
        // Set radio buttons for activity_posting
        if (data.activity_posting === 1 || data.activity_posting === '1' || data.activity_posting === true) {
            $('#edit_activity_active').prop('checked', true);
        } else if (data.activity_posting === 0 || data.activity_posting === '0' || data.activity_posting === false) {
            $('#edit_activity_inactive').prop('checked', true);
        } else {
            $('#edit_activity_null').prop('checked', true);
        }
        
        // Set form action
        $('#editKolForm').attr('action', `{{ route('kol.update', ':kolId') }}`.replace(':kolId', data.id));
    }

    // Handle original edit form submission
    $('#editKolForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#saveKolBtn');
        const originalBtnText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Submit form
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                // Close modal
                $('#editKolModal').modal('hide');
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'KOL information updated successfully.',
                    confirmButtonColor: '#28a745',
                    timer: 2000,
                    timerProgressBar: true
                });
                
                // Refresh DataTable and KPI
                kolTable.ajax.reload(null, false);
                loadKpiDataMonitor();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    
                    Object.keys(errors).forEach(function(field) {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(errors[field][0]);
                    });
                    
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Error',
                        text: 'Please check the form and fix the errors.',
                        confirmButtonColor: '#ffc107'
                    });
                } else {
                    // Other errors
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update KOL information. Please try again.',
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
</script>
@stop