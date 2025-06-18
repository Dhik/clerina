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
    <!-- Calendar and Notifications Row -->
    <div class="row mb-4">
        <!-- Calendar Section (col-10) -->
        <div class="col-lg-10 col-md-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt"></i> Content Calendar
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" id="toggleCalendar" title="Toggle Calendar">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                
                <div class="card-body p-0" id="calendarBody">
                    <!-- Calendar Header -->
                    <div class="calendar-header">
                        <div class="month-navigation">
                            <button class="btn btn-outline-primary btn-sm" onclick="changeMonth(-1)">
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                            <h4 class="current-month mb-0" id="currentMonth"></h4>
                            <button class="btn btn-outline-primary btn-sm" onclick="changeMonth(1)">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="calendar-container">
                        <div class="calendar-grid" id="calendarGrid">
                            <!-- Calendar will be generated here -->
                        </div>
                    </div>

                    <!-- Status Legend -->
                    <div class="status-legend">
                        <div class="legend-item">
                            <div class="legend-color badge-secondary"></div>
                            <span class="legend-text">Draft</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color badge-info"></div>
                            <span class="legend-text">Content Writing</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color badge-warning"></div>
                            <span class="legend-text">Creative Review</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color badge-primary"></div>
                            <span class="legend-text">Admin Support</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color badge-dark"></div>
                            <span class="legend-text">Content Editing</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color badge-success"></div>
                            <span class="legend-text">Ready/Posted</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Section (col-2) -->
        <div class="col-lg-2 col-md-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell"></i> Today's Tasks
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div id="todayNotifications">
                        <!-- Today's notifications will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Row -->
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

    {{-- Content Plan Detail Modal --}}
    <div class="modal fade" id="contentPlanDetailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Content Plan Details</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="modalContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="editPlanBtn">Edit</button>
                </div>
            </div>
        </div>
    </div>

    @include('admin.content_plan.modals.add_content_plan_modal')
    @include('admin.content_plan.modals.edit_content_plan_modal')
    @include('admin.content_plan.modals.view_content_plan_modal')
    @include('admin.content_plan.modals.step_modal')
@stop

@section('css')
<style>
    /* Calendar Styles */
    .calendar-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
    }

    .month-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .current-month {
        color: #495057;
        font-weight: 600;
        text-align: center;
        flex: 1;
    }

    .calendar-container {
        padding: 0;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #dee2e6;
    }

    .day-header {
        background: #6c757d;
        color: white;
        padding: 10px 5px;
        text-align: center;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
    }

    .calendar-day {
        background: white;
        min-height: 80px;
        padding: 5px;
        position: relative;
        border: none;
        transition: background-color 0.2s ease;
    }

    .calendar-day:hover {
        background: #f8f9fa;
    }

    .calendar-day.other-month {
        background: #f8f9fa;
        color: #6c757d;
    }

    .calendar-day.other-month .day-number {
        color: #adb5bd;
    }

    .calendar-day.today {
        background: #fff3cd;
        border: 2px solid #ffc107;
    }

    .day-number {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 4px;
        color: #495057;
    }

    .content-item {
        background: white;
        border-radius: 3px;
        padding: 2px 4px;
        margin-bottom: 2px;
        font-size: 0.65rem;
        border-left: 3px solid;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .content-item:hover {
        transform: translateX(1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }

    /* Status colors matching AdminLTE badges */
    .content-item.draft { 
        border-left-color: #6c757d; 
        background: #f8f9fa; 
    }
    .content-item.content_writing { 
        border-left-color: #17a2b8; 
        background: #d1ecf1; 
    }
    .content-item.creative_review { 
        border-left-color: #ffc107; 
        background: #fff3cd; 
    }
    .content-item.admin_support { 
        border-left-color: #007bff; 
        background: #d1ecf1; 
    }
    .content-item.content_editing { 
        border-left-color: #343a40; 
        background: #f8f9fa; 
    }
    .content-item.ready_to_post { 
        border-left-color: #28a745; 
        background: #d4edda; 
    }
    .content-item.posted { 
        border-left-color: #20c997; 
        background: #d1ecf1; 
    }

    .item-title {
        font-weight: 600;
        margin-bottom: 1px;
        color: #495057;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }

    .item-talent {
        color: #6c757d;
        font-size: 0.6rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .status-legend {
        padding: 10px 15px;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
    }

    .legend-color.badge-secondary { background-color: #6c757d; }
    .legend-color.badge-info { background-color: #17a2b8; }
    .legend-color.badge-warning { background-color: #ffc107; }
    .legend-color.badge-primary { background-color: #007bff; }
    .legend-color.badge-dark { background-color: #343a40; }
    .legend-color.badge-success { background-color: #28a745; }

    .legend-text {
        font-size: 0.75rem;
        color: #495057;
        font-weight: 500;
    }

    /* Notification Styles */
    .notification-item {
        padding: 10px;
        border-bottom: 1px solid #dee2e6;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .notification-item:hover {
        background: #f8f9fa;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 2px;
        line-height: 1.2;
    }

    .notification-talent {
        font-size: 0.7rem;
        color: #6c757d;
        margin-bottom: 3px;
    }

    .notification-status {
        font-size: 0.65rem;
        padding: 2px 6px;
        border-radius: 10px;
        color: white;
        display: inline-block;
    }

    .notification-status.draft { background-color: #6c757d; }
    .notification-status.content_writing { background-color: #17a2b8; }
    .notification-status.creative_review { background-color: #ffc107; color: #212529; }
    .notification-status.admin_support { background-color: #007bff; }
    .notification-status.content_editing { background-color: #343a40; }
    .notification-status.ready_to_post { background-color: #28a745; }
    .notification-status.posted { background-color: #20c997; }

    .no-notifications {
        padding: 20px;
        text-align: center;
        color: #6c757d;
        font-style: italic;
        font-size: 0.8rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .calendar-day {
            min-height: 60px;
            padding: 3px;
        }
        
        .content-item {
            font-size: 0.6rem;
            padding: 1px 3px;
        }
        
        .month-navigation {
            flex-direction: column;
            gap: 10px;
        }

        .legend-item {
            padding: 2px 6px;
        }
        
        .legend-text {
            font-size: 0.7rem;
        }

        .notification-title {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .calendar-day {
            min-height: 50px;
            padding: 2px;
        }
        
        .day-number {
            font-size: 0.8rem;
            margin-bottom: 2px;
        }
        
        .content-item {
            font-size: 0.55rem;
            padding: 1px 2px;
            margin-bottom: 1px;
        }
    }
</style>
@stop

@section('js')
<script>
    let currentDate = new Date();
    let contentPlans = [];
    let table;

    $(document).ready(function() {
        // Initialize DataTables
        table = $('#contentPlanTable').DataTable({
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

        // Load calendar and notifications
        loadContentPlans();
        generateCalendar();
        loadTodayNotifications();

        // Filter functionality
        $('#filterBtn').on('click', function() {
            table.draw();
        });

        $('#statusFilter').on('change', function() {
            table.draw();
        });

        // Toggle calendar visibility
        $('#toggleCalendar').on('click', function() {
            $('#calendarBody').slideToggle();
            $(this).find('i').toggleClass('fa-minus fa-plus');
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
                        loadContentPlans(); // Reload calendar
                        loadTodayNotifications(); // Reload notifications
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
                        loadContentPlans(); // Reload calendar
                        loadTodayNotifications(); // Reload notifications
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
                        loadContentPlans(); // Reload calendar
                        loadTodayNotifications(); // Reload notifications
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
                            <label for="step_talent">Talent</label>
                            <input type="text" class="form-control" name="talent" id="step_talent" value="${data.talent || ''}">
                        </div>
                        <div class="form-group">
                            <label for="step_venue">Venue</label>
                            <input type="text" class="form-control" name="venue" id="step_venue" value="${data.venue || ''}">
                        </div>
                        <div class="form-group">
                            <label for="step_hook">Hook</label>
                            <textarea class="form-control" name="hook" id="step_hook" rows="4">${data.hook || ''}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="step_target_posting_date">Target Posting Date</label>
                            <input type="date" class="form-control" name="target_posting_date" id="step_target_posting_date" value="${data.target_posting_date || ''}">
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

    // Calendar Functions
    function loadContentPlans() {
        $.ajax({
            url: '{{ route('contentPlan.data') }}',
            method: 'GET',
            data: {
                start: 0,
                length: -1 // Get all records
            },
            success: function(response) {
                contentPlans = response.data || [];
                generateCalendar();
                loadTodayNotifications();
            },
            error: function(xhr) {
                console.error('Error loading content plans:', xhr);
                toastr.error('Error loading content plans');
            }
        });
    }

    function generateCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
        // Update month display
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $('#currentMonth').text(`${monthNames[month]} ${year}`);
        
        // Clear calendar
        $('#calendarGrid').empty();
        
        // Add day headers
        const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayHeaders.forEach(day => {
            $('#calendarGrid').append(`<div class="day-header">${day}</div>`);
        });
        
        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());
        
        // Generate calendar days
        const today = new Date();
        for (let i = 0; i < 42; i++) { // 6 weeks
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + i);
            
            const dayElement = createDayElement(date, month, today);
            $('#calendarGrid').append(dayElement);
        }
    }

    function createDayElement(date, currentMonth, today) {
        const isCurrentMonth = date.getMonth() === currentMonth;
        const isToday = date.toDateString() === today.toDateString();
        const dateString = date.toISOString().split('T')[0];
        
        let classes = 'calendar-day';
        if (!isCurrentMonth) classes += ' other-month';
        if (isToday) classes += ' today';
        
        // Find content plans for this date
        const dayPlans = contentPlans.filter(plan => {
            if (!plan.target_posting_date) return false;
            const planDate = plan.target_posting_date.split(' ')[0]; // Handle both date and datetime formats
            return planDate === dateString;
        });
        
        let dayHtml = `
            <div class="${classes}" data-date="${dateString}">
                <div class="day-number">${date.getDate()}</div>
        `;
        
        // Add content plans for this day
        if (dayPlans.length > 0) {
            dayPlans.forEach(plan => {
                const statusClass = getStatusClass(plan.status);
                dayHtml += `
                    <div class="content-item ${statusClass}" onclick="showContentPlanDetail(${plan.id})" title="Click to view details">
                        <div class="item-title">${plan.objektif || 'No Title'}</div>
                        <div class="item-talent">${plan.talent || 'No Talent'}</div>
                    </div>
                `;
            });
        }
        
        dayHtml += '</div>';
        return dayHtml;
    }

    function getStatusClass(status) {
        const statusMap = {
            'draft': 'draft',
            'content_writing': 'content_writing',
            'creative_review': 'creative_review',
            'admin_support': 'admin_support',
            'content_editing': 'content_editing',
            'ready_to_post': 'ready_to_post',
            'posted': 'posted'
        };
        return statusMap[status] || 'draft';
    }

    function changeMonth(direction) {
        currentDate.setMonth(currentDate.getMonth() + direction);
        generateCalendar();
    }

    function showContentPlanDetail(id) {
        $.ajax({
            url: '{{ route('contentPlan.details', ':id') }}'.replace(':id', id),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    displayContentPlanModal(response.data);
                }
            },
            error: function(xhr) {
                toastr.error('Error loading content plan details');
            }
        });
    }

    function displayContentPlanModal(data) {
        const modalContent = `
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-bullseye"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Objektif</span>
                            <span class="info-box-number">${data.objektif || '-'}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Target Date</span>
                            <span class="info-box-number">${data.target_posting_date || '-'}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Talent</span>
                            <span class="info-box-number">${data.talent || '-'}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-tags"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Status</span>
                            <span class="info-box-number">${getStatusLabel(data.status)}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Content Details</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-3">Jenis Konten</dt>
                                <dd class="col-sm-9">${data.jenis_konten || '-'}</dd>
                                
                                <dt class="col-sm-3">Pillar</dt>
                                <dd class="col-sm-9">${data.pillar || '-'}</dd>
                                
                                <dt class="col-sm-3">Platform</dt>
                                <dd class="col-sm-9">${data.platform || '-'}</dd>
                                
                                <dt class="col-sm-3">Venue</dt>
                                <dd class="col-sm-9">${data.venue || '-'}</dd>
                                
                                <dt class="col-sm-3">Caption</dt>
                                <dd class="col-sm-9">${data.caption ? data.caption.substring(0, 200) + (data.caption.length > 200 ? '...' : '') : '-'}</dd>
                                
                                <dt class="col-sm-3">Hook</dt>
                                <dd class="col-sm-9">${data.hook ? data.hook.substring(0, 150) + (data.hook.length > 150 ? '...' : '') : '-'}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#modalContent').html(modalContent);
        $('#editPlanBtn').attr('onclick', `editContentPlan(${data.id})`);
        $('#contentPlanDetailModal').modal('show');
    }

    function getStatusLabel(status) {
        const statusLabels = {
            'draft': 'Draft',
            'content_writing': 'Content Writing',
            'creative_review': 'Creative Review',
            'admin_support': 'Admin Support',
            'content_editing': 'Content Editing',
            'ready_to_post': 'Ready to Post',
            'posted': 'Posted'
        };
        return statusLabels[status] || status;
    }

    function editContentPlan(id) {
        window.location.href = '{{ route('contentPlan.edit', ':id') }}'.replace(':id', id);
    }

    // Notification Functions
    function loadTodayNotifications() {
        const today = new Date().toISOString().split('T')[0];
        
        // Filter content plans for today
        const todayPlans = contentPlans.filter(plan => {
            if (!plan.target_posting_date) return false;
            const planDate = plan.target_posting_date.split(' ')[0];
            return planDate === today;
        });

        let notificationHtml = '';
        
        if (todayPlans.length === 0) {
            notificationHtml = '<div class="no-notifications">No content plans scheduled for today</div>';
        } else {
            todayPlans.forEach(plan => {
                const statusClass = getStatusClass(plan.status);
                notificationHtml += `
                    <div class="notification-item" onclick="showContentPlanDetail(${plan.id})">
                        <div class="notification-title">${plan.objektif || 'No Title'}</div>
                        <div class="notification-talent">👤 ${plan.talent || 'No Talent'}</div>
                        <span class="notification-status ${statusClass}">${getStatusLabel(plan.status)}</span>
                    </div>
                `;
            });
        }
        
        $('#todayNotifications').html(notificationHtml);
    }

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
                            loadContentPlans(); // Reload calendar
                            loadTodayNotifications(); // Reload notifications
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