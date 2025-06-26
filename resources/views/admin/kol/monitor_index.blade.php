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
    <div class="modal fade" id="editKolModal" tabindex="-1" role="dialog" aria-labelledby="editKolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editKolModalLabel">
                        <i class="fas fa-edit"></i> Edit KOL Information
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editKolForm" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Hidden fields to preserve existing required data -->
                    <input type="hidden" name="channel" id="edit_channel">
                    <input type="hidden" name="niche" id="edit_niche">
                    <input type="hidden" name="average_view" id="edit_average_view">
                    <input type="hidden" name="skin_type" id="edit_skin_type">
                    <input type="hidden" name="skin_concern" id="edit_skin_concern">
                    <input type="hidden" name="content_type" id="edit_content_type">
                    <input type="hidden" name="rate" id="edit_rate">
                    <input type="hidden" name="pic_contact" id="edit_pic_contact">
                    <input type="hidden" name="name" id="edit_name">
                    <input type="hidden" name="address" id="edit_address">
                    <input type="hidden" name="bank_name" id="edit_bank_name">
                    <input type="hidden" name="bank_account" id="edit_bank_account">
                    <input type="hidden" name="bank_account_name" id="edit_bank_account_name">
                    <input type="hidden" name="npwp" id="edit_npwp">
                    <input type="hidden" name="npwp_number" id="edit_npwp_number">
                    <input type="hidden" name="nik" id="edit_nik">
                    <input type="hidden" name="notes" id="edit_notes">
                    <input type="hidden" name="product_delivery" id="edit_product_delivery">
                    <input type="hidden" name="product" id="edit_product">
                    
                    <div class="modal-body">
                        <div id="editFormLoader" class="text-center" style="display: none;">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p>Loading KOL data...</p>
                        </div>
                        
                        <div id="editFormContent" style="display: none;">
                            <!-- Username -->
                            <div class="form-group row">
                                <label for="edit_username" class="col-md-4 col-form-label text-md-right">Username</label>
                                <div class="col-md-8">
                                    <input type="text" 
                                        class="form-control" 
                                        name="username" 
                                        id="edit_username" 
                                        required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group row">
                                <label for="edit_phone_number" class="col-md-4 col-form-label text-md-right">Phone Number</label>
                                <div class="col-md-8">
                                    <input type="text" 
                                        class="form-control" 
                                        name="phone_number" 
                                        id="edit_phone_number">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Status Affiliate -->
                            <div class="form-group row">
                                <label for="edit_status_affiliate" class="col-md-4 col-form-label text-md-right">Affiliate Status</label>
                                <div class="col-md-8">
                                    <select class="form-control" name="status_affiliate" id="edit_status_affiliate">
                                        <option value="">Not Set</option>
                                        <option value="Qualified">Qualified</option>
                                        <option value="Waiting List">Waiting List</option>
                                        <option value="Not Qualified">Not Qualified</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Views Last 9 Posts -->
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">Recent Views</label>
                                <div class="col-md-8">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="views_last_9_post" 
                                            id="edit_views_yes" 
                                            value="1">
                                        <label class="form-check-label" for="edit_views_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="views_last_9_post" 
                                            id="edit_views_no" 
                                            value="0">
                                        <label class="form-check-label" for="edit_views_no">No</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="views_last_9_post" 
                                            id="edit_views_null" 
                                            value="">
                                        <label class="form-check-label" for="edit_views_null">Not Set</label>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Activity Posting -->
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">Activity Status</label>
                                <div class="col-md-8">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="activity_posting" 
                                            id="edit_activity_active" 
                                            value="1">
                                        <label class="form-check-label" for="edit_activity_active">Active</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="activity_posting" 
                                            id="edit_activity_inactive" 
                                            value="0">
                                        <label class="form-check-label" for="edit_activity_inactive">Inactive</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="activity_posting" 
                                            id="edit_activity_null" 
                                            value="">
                                        <label class="form-check-label" for="edit_activity_null">Not Set</label>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveKolBtn">
                            <i class="fas fa-save"></i> Save Changes
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
            ajax: {
                url: "{{ route('kol.monitor_get') }}",
                data: function (d) {
                    d.statusAffiliate = statusAffiliateSelector.val();
                }
            },
            columns: [
                {data: 'username', name: 'username'},
                {
                    data: 'status_affiliate_display', 
                    name: 'status_affiliate', 
                    orderable: false
                },
                {data: 'actions', sortable: false, orderable: false}
            ],
            order: [[0, 'desc']],
            drawCallback: function() {
                loadKpiDataMonitor();
            }
        });

        statusAffiliateSelector.change(function() {
            kolTable.draw();
        });

        $(function () {
            kolTable.draw();
        });

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

        // Handle form submission
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