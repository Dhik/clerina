@extends('adminlte::page')

@section('title', 'Content Production')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Content Production</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Content Production</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Content Plan Management</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addContentPlanModal">
                            <i class="fas fa-plus"></i> Add New Content Plan
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select id="statusFilter" class="form-control">
                                <option value="">All Status</option>
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button id="filterBtn" class="btn btn-info btn-block">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </div>

                    <!-- Content Plans Table -->
                    <table id="contentPlanTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Target Date</th>
                                <th>Status</th>
                                <th>Objektif</th>
                                <th>Jenis Konten</th>
                                <th>Pillar</th>
                                <th>Platform</th>
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

    @include('admin.content_plan.modals.add_content_plan_modal')
    @include('admin.content_plan.modals.edit_content_plan_modal')
    @include('admin.content_plan.modals.view_content_plan_modal')
    @include('admin.content_plan.modals.step_modal')
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Initialize DataTables
        var table = $('#contentPlanTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('contentPlan.data') }}',
                data: function(d) {
                    d.status = $('#statusFilter').val();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'target_date', name: 'target_posting_date' },
                { data: 'status_badge', name: 'status' },
                { data: 'objektif', name: 'objektif' },
                { data: 'jenis_konten', name: 'jenis_konten' },
                { data: 'pillar', name: 'pillar' },
                { data: 'platform', name: 'platform' },
                { data: 'created_date', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']]
        });

        // Filter functionality
        $('#filterBtn').on('click', function() {
            table.draw();
        });

        $('#statusFilter').on('change', function() {
            table.draw();
        });

        // Handle Add Content Plan Form Submit
        $('#addContentPlanForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '{{ route('contentPlan.store') }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#addContentPlanModal').modal('hide');
                        table.draw();
                        toastr.success(response.message);
                        $('#addContentPlanForm')[0].reset();
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors || {};
                    var message = xhr.responseJSON.message || 'An error occurred';
                    
                    // Clear previous error messages
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    
                    // Display validation errors
                    $.each(errors, function(field, messages) {
                        var input = $('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                    });
                    
                    if (Object.keys(errors).length === 0) {
                        toastr.error(message);
                    }
                }
            });
        });

        // Handle Edit Content Plan Form Submit
        $('#editContentPlanForm').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            var url = $(this).attr('action');
            
            $.ajax({
                url: url,
                method: 'PUT',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#editContentPlanModal').modal('hide');
                        table.draw();
                        toastr.success(response.message);
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors || {};
                    var message = xhr.responseJSON.message || 'An error occurred';
                    
                    // Clear previous error messages
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    
                    // Display validation errors
                    $.each(errors, function(field, messages) {
                        var input = $('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                    });
                    
                    if (Object.keys(errors).length === 0) {
                        toastr.error(message);
                    }
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
                        toastr.success(response.message);
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors || {};
                    var message = xhr.responseJSON.message || 'An error occurred';
                    
                    // Clear previous error messages
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    
                    // Display validation errors
                    $.each(errors, function(field, messages) {
                        var input = $('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                    });
                    
                    if (Object.keys(errors).length === 0) {
                        toastr.error(message);
                    }
                }
            });
        });

        // Handle View button click
        $('#contentPlanTable').on('click', '.viewButton', function() {
            var id = $(this).data('id');
            loadContentPlanDetails(id);
        });

        // Handle Edit button click
        $('#contentPlanTable').on('click', '.editButton', function() {
            var id = $(this).data('id');
            loadContentPlanForEdit(id);
        });

        // Handle Step button click
        $('#contentPlanTable').on('click', '.stepButton', function() {
            var id = $(this).data('id');
            var step = $(this).data('step');
            loadStepForm(id, step);
        });

        // Handle Delete button click
        $('#contentPlanTable').on('click', '.deleteButton', function() {
            var id = $(this).data('id');
            var route = '{{ route('contentPlan.destroy', ':id') }}'.replace(':id', id);
            
            deleteAjax(route, id, table);
        });

        // Function to load content plan details
        function loadContentPlanDetails(id) {
            $.ajax({
                url: '{{ route('contentPlan.details', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        populateViewModal(response.data);
                        $('#viewContentPlanModal').modal('show');
                    }
                },
                error: function(xhr) {
                    toastr.error('Error loading content plan details');
                }
            });
        }

        // Function to load content plan for editing
        function loadContentPlanForEdit(id) {
            $.ajax({
                url: '{{ route('contentPlan.details', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        populateEditModal(response.data);
                        $('#editContentPlanForm').attr('action', '{{ route('contentPlan.update', ':id') }}'.replace(':id', id));
                        $('#editContentPlanModal').modal('show');
                    }
                },
                error: function(xhr) {
                    toastr.error('Error loading content plan data');
                }
            });
        }

        // Function to load step form
        function loadStepForm(id, step) {
            $.ajax({
                url: '{{ route('contentPlan.details', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        populateStepModal(response.data, step);
                        $('#stepForm').attr('action', '{{ route('contentPlan.updateStep', [':id', ':step']) }}'.replace(':id', id).replace(':step', step));
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
            $('#view_objektif').text(data.objektif || '-');
            $('#view_jenis_konten').text(data.jenis_konten || '-');
            $('#view_pillar').text(data.pillar || '-');
            $('#view_platform').text(data.platform || '-');
            $('#view_status').text(data.status_label || '-');
            $('#view_caption').text(data.caption || '-');
            // Add more fields as needed
        }

        // Function to populate edit modal
        function populateEditModal(data) {
            $('#edit_objektif').val(data.objektif);
            $('#edit_jenis_konten').val(data.jenis_konten);
            $('#edit_pillar').val(data.pillar);
            $('#edit_platform').val(data.platform);
            $('#edit_caption').val(data.caption);
            // Add more fields as needed
        }

        // Function to populate step modal
        function populateStepModal(data, step) {
            $('#stepModalTitle').text('Step ' + step + ' - ' + getStepTitle(step));
            $('#stepModalBody').html(getStepFormFields(data, step));
        }

        // Function to get step title
        function getStepTitle(step) {
            const titles = {
                1: 'Social Media Strategist',
                2: 'Content Writer',
                3: 'Creative Leader',
                4: 'Admin Support',
                5: 'Content Editor',
                6: 'Admin Social Media'
            };
            return titles[step] || 'Unknown Step';
        }

        // Function to get step form fields
        function getStepFormFields(data, step) {
            // This would return different form fields based on the step
            // Implementation depends on your step requirements
            switch(step) {
                case 1:
                    return `
                        <div class="form-group">
                            <label for="step_objektif">Objektif</label>
                            <input type="text" class="form-control" name="objektif" id="step_objektif" value="${data.objektif || ''}">
                        </div>
                        <div class="form-group">
                            <label for="step_jenis_konten">Jenis Konten</label>
                            <select class="form-control" name="jenis_konten" id="step_jenis_konten">
                                <option value="">Select Content Type</option>
                                <option value="image" ${data.jenis_konten === 'image' ? 'selected' : ''}>Image</option>
                                <option value="video" ${data.jenis_konten === 'video' ? 'selected' : ''}>Video</option>
                                <option value="carousel" ${data.jenis_konten === 'carousel' ? 'selected' : ''}>Carousel</option>
                                <option value="reel" ${data.jenis_konten === 'reel' ? 'selected' : ''}>Reel</option>
                                <option value="story" ${data.jenis_konten === 'story' ? 'selected' : ''}>Story</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="step_pillar">Pillar</label>
                            <input type="text" class="form-control" name="pillar" id="step_pillar" value="${data.pillar || ''}">
                        </div>
                        <div class="form-group">
                            <label for="step_hook">Hook</label>
                            <textarea class="form-control" name="hook" id="step_hook" rows="4">${data.hook || ''}</textarea>
                        </div>
                    `;
                case 2:
                    return `
                        <div class="form-group">
                            <label for="step_brief_konten">Brief Konten</label>
                            <textarea class="form-control" name="brief_konten" id="step_brief_konten" rows="6">${data.brief_konten || ''}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="step_caption">Caption</label>
                            <textarea class="form-control" name="caption" id="step_caption" rows="8">${data.caption || ''}</textarea>
                        </div>
                    `;
                case 3:
                    return `
                        <div class="form-group">
                            <label for="step_platform">Platform</label>
                            <select class="form-control" name="platform" id="step_platform">
                                <option value="">Select Platform</option>
                                <option value="instagram" ${data.platform === 'instagram' ? 'selected' : ''}>Instagram</option>
                                <option value="facebook" ${data.platform === 'facebook' ? 'selected' : ''}>Facebook</option>
                                <option value="tiktok" ${data.platform === 'tiktok' ? 'selected' : ''}>TikTok</option>
                                <option value="twitter" ${data.platform === 'twitter' ? 'selected' : ''}>Twitter</option>
                                <option value="linkedin" ${data.platform === 'linkedin' ? 'selected' : ''}>LinkedIn</option>
                                <option value="youtube" ${data.platform === 'youtube' ? 'selected' : ''}>YouTube</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="step_akun">Akun</label>
                            <input type="text" class="form-control" name="akun" id="step_akun" value="${data.akun || ''}">
                        </div>
                    `;
                case 4:
                    return `
                        <div class="form-group">
                            <label for="step_kerkun">Kerkun</label>
                            <input type="text" class="form-control" name="kerkun" id="step_kerkun" value="${data.kerkun || ''}">
                        </div>
                        <div class="form-group">
                            <label for="step_assignee_content_editor">Assignee Content Editor</label>
                            <select class="form-control" name="assignee_content_editor" id="step_assignee_content_editor">
                                <option value="">Select Content Editor</option>
                                <option value="editor1" ${data.assignee_content_editor === 'editor1' ? 'selected' : ''}>Editor 1</option>
                                <option value="editor2" ${data.assignee_content_editor === 'editor2' ? 'selected' : ''}>Editor 2</option>
                                <option value="editor3" ${data.assignee_content_editor === 'editor3' ? 'selected' : ''}>Editor 3</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="step_link_raw_content">Link Raw Content</label>
                            <textarea class="form-control" name="link_raw_content" id="step_link_raw_content" rows="3">${data.link_raw_content || ''}</textarea>
                        </div>
                    `;
                case 5:
                    return `
                        <div class="form-group">
                            <label for="step_link_hasil_edit">Link Hasil Edit</label>
                            <input type="url" class="form-control" name="link_hasil_edit" id="step_link_hasil_edit" value="${data.link_hasil_edit || ''}">
                        </div>
                    `;
                case 6:
                    return `
                        <div class="form-group">
                            <label for="step_input_link_posting">Input Link Posting</label>
                            <input type="url" class="form-control" name="input_link_posting" id="step_input_link_posting" value="${data.input_link_posting || ''}">
                        </div>
                    `;
                default:
                    return '<p>Invalid step</p>';
            }
        }

        // Clear form on modal close
        $('.modal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();
        });
    });

    // Global delete function (similar to your budget example)
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
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            );
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'An error occurred while deleting.',
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>
@stop