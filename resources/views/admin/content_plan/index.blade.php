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
    min-height: 90px; /* Increased for time display */
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

/* Status colors - Updated for new workflow order */
.content-item.draft { 
    border-left-color: #6c757d; 
    background: #f8f9fa; 
}
.content-item.content_writing { 
    border-left-color: #17a2b8; 
    background: #d1ecf1; 
}
.content-item.admin_support { 
    border-left-color: #007bff; 
    background: #d1ecf1; 
}
.content-item.creative_review { 
    border-left-color: #ffc107; 
    background: #fff3cd; 
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
    line-height: 1.1;
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

/* Notification Styles - Enhanced for datetime display */
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

/* NEW: Time display in notifications */
.notification-time {
    font-size: 0.65rem;
    color: #28a745;
    margin-bottom: 3px;
    font-weight: 500;
}

.notification-status {
    font-size: 0.65rem;
    padding: 2px 6px;
    border-radius: 10px;
    color: white;
    display: inline-block;
}

/* Updated status colors for new workflow order */
.notification-status.draft { background-color: #6c757d; }
.notification-status.content_writing { background-color: #17a2b8; }
.notification-status.admin_support { background-color: #007bff; }
.notification-status.creative_review { background-color: #ffc107; color: #212529; }
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

/* Enhanced modal styles for datetime inputs */
.modal-xl .form-group label {
    font-weight: 600;
    color: #495057;
}

.modal-xl input[type="datetime-local"] {
    font-size: 0.9rem;
}

.modal-xl .form-text {
    font-size: 0.8rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .calendar-day {
        min-height: 70px;
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

    .notification-time {
        font-size: 0.6rem;
    }
}

@media (max-width: 576px) {
    .calendar-day {
        min-height: 60px;
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
    // Complete Updated JavaScript for index.blade.php
// Includes all changes for new workflow and datetime handling

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

    // Platform selection enhancement for add modal
    $('#add_platform').on('change', function() {
        const platform = $(this).val();
        const akunField = $('#add_akun');
        
        // Update placeholder based on platform
        switch(platform) {
            case 'instagram':
                akunField.attr('placeholder', '@username');
                break;
            case 'facebook':
                akunField.attr('placeholder', 'Page Name');
                break;
            case 'tiktok':
                akunField.attr('placeholder', '@username');
                break;
            case 'twitter':
                akunField.attr('placeholder', '@username');
                break;
            case 'linkedin':
                akunField.attr('placeholder', 'Company/Profile Name');
                break;
            case 'youtube':
                akunField.attr('placeholder', 'Channel Name');
                break;
            default:
                akunField.attr('placeholder', 'Enter account name/handle');
        }
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
        
        // Re-bind platform change event for step modal if it's step 1
        if (step === 1) {
            $('#step_platform').on('change', function() {
                const platform = $(this).val();
                const akunField = $('#step_akun');
                
                switch(platform) {
                    case 'instagram':
                        akunField.attr('placeholder', '@username');
                        break;
                    case 'facebook':
                        akunField.attr('placeholder', 'Page Name');
                        break;
                    case 'tiktok':
                        akunField.attr('placeholder', '@username');
                        break;
                    case 'twitter':
                        akunField.attr('placeholder', '@username');
                        break;
                    case 'linkedin':
                        akunField.attr('placeholder', 'Company/Profile Name');
                        break;
                    case 'youtube':
                        akunField.attr('placeholder', 'Channel Name');
                        break;
                    default:
                        akunField.attr('placeholder', 'Enter account name/handle');
                }
            });
        }
    }

    // Clear form on modal close
    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').remove();
    });
});

// Calendar Functions - Updated for datetime handling
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
            console.log('Loaded content plans:', contentPlans); // Debug log
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
    
    // Use local date string to avoid timezone issues
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const dateString = `${year}-${month}-${day}`;
    
    let classes = 'calendar-day';
    if (!isCurrentMonth) classes += ' other-month';
    if (isToday) classes += ' today';
    
    // Find content plans for this date - UPDATED: handling datetime target_posting_date
    const dayPlans = contentPlans.filter(plan => {
        if (!plan.target_posting_date) {
            return false;
        }
        
        // Extract date part from datetime string
        let planDate;
        if (typeof plan.target_posting_date === 'string') {
            // Extract date part from datetime (YYYY-MM-DD HH:MM:SS -> YYYY-MM-DD)
            planDate = plan.target_posting_date.split(' ')[0];
        } else if (plan.target_posting_date instanceof Date) {
            const pYear = plan.target_posting_date.getFullYear();
            const pMonth = String(plan.target_posting_date.getMonth() + 1).padStart(2, '0');
            const pDay = String(plan.target_posting_date.getDate()).padStart(2, '0');
            planDate = `${pYear}-${pMonth}-${pDay}`;
        } else {
            return false;
        }
        
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
            // Show time in tooltip for datetime fields
            const timeInfo = plan.target_posting_date ? 
                (typeof plan.target_posting_date === 'string' && plan.target_posting_date.includes(' ') ?
                    plan.target_posting_date.split(' ')[1].substring(0, 5) : '') : '';
            
            dayHtml += `
                <div class="content-item ${statusClass}" onclick="showContentPlanDetail(${plan.id})" 
                     title="${plan.objektif || 'No Title'}${timeInfo ? ' at ' + timeInfo : ''} - Click to view details">
                    <div class="item-title">${plan.objektif || 'No Title'}</div>
                    <div class="item-talent">${plan.talent_fix || plan.talent || 'No Talent'}${timeInfo ? ' • ' + timeInfo : ''}</div>
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
        'admin_support': 'admin_support',          // Step 3 now
        'creative_review': 'creative_review',       // Step 4 now
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
                        <span class="info-box-text">Target Date & Time</span>
                        <span class="info-box-number">${data.target_posting_date ? formatDateTime(data.target_posting_date) : '-'}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-user"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Talent</span>
                        <span class="info-box-number">${data.talent_fix || data.talent || '-'}</span>
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
        
        ${data.production_date ? `
        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary"><i class="fas fa-video"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Production Date</span>
                        <span class="info-box-number">${formatDateTime(data.production_date)}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-user-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Talent Booking</span>
                        <span class="info-box-number">${data.booking_talent_date ? formatDateTime(data.booking_talent_date) : '-'}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-map-marker-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Venue Booking</span>
                        <span class="info-box-number">${data.booking_venue_date ? formatDateTime(data.booking_venue_date) : '-'}</span>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
        
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
                            
                            <dt class="col-sm-3">Account</dt>
                            <dd class="col-sm-9">${data.akun || '-'}</dd>
                            
                            <dt class="col-sm-3">Venue</dt>
                            <dd class="col-sm-9">${data.venue || '-'}</dd>
                            
                            <dt class="col-sm-3">Caption</dt>
                            <dd class="col-sm-9">${data.caption ? data.caption.substring(0, 200) + (data.caption.length > 200 ? '...' : '') : '-'}</dd>
                            
                            <dt class="col-sm-3">Hook</dt>
                            <dd class="col-sm-9">${data.hook ? data.hook.substring(0, 150) + (data.hook.length > 150 ? '...' : '') : '-'}</dd>
                            
                            ${data.assignee_content_editor ? `
                            <dt class="col-sm-3">Content Editor</dt>
                            <dd class="col-sm-9">${data.assignee_content_editor}</dd>
                            ` : ''}
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

function editContentPlan(id) {
    window.location.href = '{{ route('contentPlan.edit', ':id') }}'.replace(':id', id);
}

function getStatusLabel(status) {
    const statusLabels = {
        'draft': 'Draft',
        'content_writing': 'Content Writing',
        'admin_support': 'Admin Support',
        'creative_review': 'Creative Review',
        'content_editing': 'Content Editing',
        'ready_to_post': 'Ready to Post',
        'posted': 'Posted'
    };
    return statusLabels[status] || status;
}

// Notification Functions - Updated for datetime handling
function loadTodayNotifications() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const todayString = `${year}-${month}-${day}`;
    
    console.log('Loading notifications for today:', todayString); // Debug log
    
    // Filter content plans for today - UPDATED: handling datetime target_posting_date
    const todayPlans = contentPlans.filter(plan => {
        if (!plan.target_posting_date) {
            return false;
        }
        
        // Extract date part from datetime string
        let planDate;
        if (typeof plan.target_posting_date === 'string') {
            planDate = plan.target_posting_date.split(' ')[0];
        } else if (plan.target_posting_date instanceof Date) {
            const pYear = plan.target_posting_date.getFullYear();
            const pMonth = String(plan.target_posting_date.getMonth() + 1).padStart(2, '0');
            const pDay = String(plan.target_posting_date.getDate()).padStart(2, '0');
            planDate = `${pYear}-${pMonth}-${pDay}`;
        } else {
            return false;
        }
        
        return planDate === todayString;
    });

    console.log(`Found ${todayPlans.length} plans for today`); // Debug log

    let notificationHtml = '';
    
    if (todayPlans.length === 0) {
        notificationHtml = '<div class="no-notifications">No content plans scheduled for today</div>';
    } else {
        // Sort by time if available
        todayPlans.sort((a, b) => {
            const timeA = a.target_posting_date && typeof a.target_posting_date === 'string' && a.target_posting_date.includes(' ') ? 
                a.target_posting_date.split(' ')[1] : '23:59';
            const timeB = b.target_posting_date && typeof b.target_posting_date === 'string' && b.target_posting_date.includes(' ') ? 
                b.target_posting_date.split(' ')[1] : '23:59';
            return timeA.localeCompare(timeB);
        });
        
        todayPlans.forEach(plan => {
            const statusClass = getStatusClass(plan.status);
            const timeInfo = plan.target_posting_date && typeof plan.target_posting_date === 'string' && plan.target_posting_date.includes(' ') ? 
                plan.target_posting_date.split(' ')[1].substring(0, 5) : '';
            
            notificationHtml += `
                <div class="notification-item" onclick="showContentPlanDetail(${plan.id})">
                    <div class="notification-title">${plan.objektif || 'No Title'}</div>
                    <div class="notification-talent">👤 ${plan.talent_fix || plan.talent || 'No Talent'}</div>
                    ${timeInfo ? `<div class="notification-time">🕐 ${timeInfo}</div>` : ''}
                    <span class="notification-status ${statusClass}">${getStatusLabel(plan.status)}</span>
                </div>
            `;
        });
    }
    
    $('#todayNotifications').html(notificationHtml);
}

// Updated step functions for new workflow
function getStepTitle(step) {
    const titles = {
        1: 'Social Media Strategist - Strategy & Platform',
        2: 'Content Writer',
        3: 'Admin Support - Booking & Resources',
        4: 'Creative Review',
        5: 'Content Editor',
        6: 'Store to Content Bank'
    };
    return titles[step] || 'Unknown Step';
}

function getStepFormFields(data, step) {
    switch(step) {
        case 1: // Strategy & Platform (moved platform/account from step 3)
            return `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Content Strategy</h6>
                        <div class="form-group">
                            <label for="step_objektif">Objektif <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="objektif" id="step_objektif" value="${data.objektif || ''}" required>
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
                            <label for="step_sub_pillar">Sub Pillar</label>
                            <input type="text" class="form-control" name="sub_pillar" id="step_sub_pillar" value="${data.sub_pillar || ''}">
                        </div>
                        <div class="form-group">
                            <label for="step_talent">Talent</label>
                            <input type="text" class="form-control" name="talent" id="step_talent" value="${data.talent || ''}">
                        </div>
                        <div class="form-group">
                            <label for="step_venue">Venue</label>
                            <input type="text" class="form-control" name="venue" id="step_venue" value="${data.venue || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Platform & Scheduling</h6>
                        <div class="form-group">
                            <label for="step_target_posting_date">Target Posting Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="target_posting_date" id="step_target_posting_date" 
                                   value="${data.target_posting_date ? data.target_posting_date.substring(0, 16) : ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="step_platform">Platform <span class="text-danger">*</span></label>
                            <select class="form-control" name="platform" id="step_platform" required>
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
                            <label for="step_akun">Akun <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="akun" id="step_akun" value="${data.akun || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="step_produk">Produk</label>
                            <input type="text" class="form-control" name="produk" id="step_produk" value="${data.produk || ''}">
                        </div>
                        <div class="form-group">
                            <label for="step_referensi">Referensi</label>
                            <input type="text" class="form-control" name="referensi" id="step_referensi" value="${data.referensi || ''}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="step_hook">Hook</label>
                    <textarea class="form-control" name="hook" id="step_hook" rows="4">${data.hook || ''}</textarea>
                    <small class="form-text text-muted">Describe the main hook or attention-grabbing element for this content.</small>
                </div>
            `;
            
        case 2: // Content Writer (unchanged)
            return `
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Content Brief</h6>
                    <p><strong>Objektif:</strong> ${data.objektif || 'Not set'}</p>
                    <p><strong>Platform:</strong> ${data.platform || 'Not set'} - ${data.akun || 'Not set'}</p>
                    <p><strong>Hook:</strong> ${data.hook || 'Not set'}</p>
                </div>
                <div class="form-group">
                    <label for="step_brief_konten">Brief Konten <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="brief_konten" id="step_brief_konten" rows="6" required>${data.brief_konten || ''}</textarea>
                    <small class="form-text text-muted">Provide detailed instructions for content creation including tone, style, key messages, and any specific requirements.</small>
                </div>
                <div class="form-group">
                    <label for="step_caption">Caption <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="caption" id="step_caption" rows="8" required>${data.caption || ''}</textarea>
                    <small class="form-text text-muted">Write the complete caption that will be used for the social media post. Include hashtags, mentions, and call-to-action.</small>
                </div>
            `;
            
        case 3: // Admin Support (NEW: booking dates + content editor assignment)
            return `
                <div class="alert alert-primary">
                    <h6><i class="fas fa-users-cog"></i> Admin Support</h6>
                    <p>Manage talent booking, venue booking, production scheduling, and content editor assignment.</p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Talent & Production Management</h6>
                        <div class="form-group">
                            <label for="step_talent_fix">Talent Fix <span class="text-danger">*</span></label>
                            <select class="form-control" name="talent_fix" id="step_talent_fix" required>
                                <option value="">Select Final Talent</option>
                                <option value="syifa" ${data.talent_fix === 'syifa' ? 'selected' : ''}>Syifa</option>
                                <option value="zinny" ${data.talent_fix === 'zinny' ? 'selected' : ''}>Zinny</option>
                                <option value="putri" ${data.talent_fix === 'putri' ? 'selected' : ''}>Putri</option>
                                <option value="eksternal" ${data.talent_fix === 'eksternal' ? 'selected' : ''}>Eksternal</option>
                                <option value="no_talent" ${data.talent_fix === 'no_talent' ? 'selected' : ''}>No Talent Required</option>
                            </select>
                            <small class="form-text text-muted">Select the confirmed talent for this content.</small>
                        </div>
                        <div class="form-group">
                            <label for="step_booking_talent_date">Booking Talent Date & Time</label>
                            <input type="datetime-local" class="form-control" name="booking_talent_date" id="step_booking_talent_date" 
                                   value="${data.booking_talent_date ? data.booking_talent_date.substring(0, 16) : ''}">
                            <small class="form-text text-muted">Schedule the talent booking appointment.</small>
                        </div>
                        <div class="form-group">
                            <label for="step_booking_venue_date">Booking Venue Date & Time</label>
                            <input type="datetime-local" class="form-control" name="booking_venue_date" id="step_booking_venue_date" 
                                   value="${data.booking_venue_date ? data.booking_venue_date.substring(0, 16) : ''}">
                            <small class="form-text text-muted">Schedule the venue booking appointment.</small>
                        </div>
                        <div class="form-group">
                            <label for="step_production_date">Production Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="production_date" id="step_production_date" 
                                   value="${data.production_date ? data.production_date.substring(0, 16) : ''}" required>
                            <small class="form-text text-muted">Set the actual content production date and time.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Resource Management</h6>
                        <div class="form-group">
                            <label for="step_kerkun">Kerkun</label>
                            <input type="text" class="form-control" name="kerkun" id="step_kerkun" value="${data.kerkun || ''}">
                        </div>
                        <div class="form-group">
                            <label for="step_assignee_content_editor">Assignee Content Editor <span class="text-danger">*</span></label>
                            <select class="form-control" name="assignee_content_editor" id="step_assignee_content_editor" required>
                                <option value="">Select Content Editor</option>
                                <option value="cleora_azmi" ${data.assignee_content_editor === 'cleora_azmi' ? 'selected' : ''}>Desain Grafis Cleora, Azmi Daffa</option>
                                <option value="azrina_farhan" ${data.assignee_content_editor === 'azrina_farhan' ? 'selected' : ''}>Desain Grafis Azrina, Farhan Ridho</option>
                                <option value="faddal" ${data.assignee_content_editor === 'faddal' ? 'selected' : ''}>Videographer & Editor, Faddal</option>
                                <option value="hendra" ${data.assignee_content_editor === 'hendra' ? 'selected' : ''}>Videographer & Editor, Hendra</option>
                                <option value="rafi" ${data.assignee_content_editor === 'rafi' ? 'selected' : ''}>Videographer & Editor, Rafi</option>
                                <option value="lukman" ${data.assignee_content_editor === 'lukman' ? 'selected' : ''}>Photographer & Editor, Lukman Fajar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="step_link_raw_content">Link Raw Content</label>
                            <textarea class="form-control" name="link_raw_content" id="step_link_raw_content" rows="4">${data.link_raw_content || ''}</textarea>
                            <small class="form-text text-muted">Provide links to raw images, videos, or other content assets.</small>
                        </div>
                        
                        <!-- Content Summary Card -->
                        <div class="card card-outline card-info mt-3">
                            <div class="card-header">
                                <h3 class="card-title">Content Summary</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Platform:</strong> ${data.platform || 'Not specified'}</p>
                                <p><strong>Account:</strong> ${data.akun || 'Not specified'}</p>
                                <p><strong>Target Date:</strong> ${data.target_posting_date ? formatDateTime(data.target_posting_date) : 'Not set'}</p>
                                <p><strong>Venue:</strong> ${data.venue || 'Not specified'}</p>
                                <p><strong>Initial Talent:</strong> ${data.talent || 'Not specified'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
        case 4: // Creative Review (moved from step 3, now step 4)
            return `
                <div class="alert alert-warning">
                    <h6><i class="fas fa-clipboard-check"></i> Creative Review</h6>
                    <p>Review all content elements, production details, and approve for content editing phase.</p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Content Strategy</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Objektif:</strong></td>
                                        <td>${data.objektif || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jenis Konten:</strong></td>
                                        <td>${data.jenis_konten || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pillar:</strong></td>
                                        <td>${data.pillar || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Platform:</strong></td>
                                        <td>${data.platform || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Account:</strong></td>
                                        <td>${data.akun || '-'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">Production Details</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Final Talent:</strong></td>
                                        <td>${data.talent_fix || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Venue:</strong></td>
                                        <td>${data.venue || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Production Date:</strong></td>
                                        <td>${data.production_date ? formatDateTime(data.production_date) : '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Talent Booking:</strong></td>
                                        <td>${data.booking_talent_date ? formatDateTime(data.booking_talent_date) : '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Venue Booking:</strong></td>
                                        <td>${data.booking_venue_date ? formatDateTime(data.booking_venue_date) : '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Target Posting:</strong></td>
                                        <td>${data.target_posting_date ? formatDateTime(data.target_posting_date) : '-'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title">Content Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Hook:</strong>
                                    <p class="text-muted">${data.hook || 'No hook provided'}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Brief Konten:</strong>
                                    <p class="text-muted">${data.brief_konten || 'Brief not yet written'}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Caption:</strong>
                                    <p class="text-muted">${data.caption || 'Caption not yet written'}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">Resources</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Assigned Editor:</strong><br>
                                ${data.assignee_content_editor || 'Not assigned'}</p>
                                
                                <p><strong>Raw Content Links:</strong><br>
                                ${data.link_raw_content ? `<small class="text-muted">${data.link_raw_content.substring(0, 100)}${data.link_raw_content.length > 100 ? '...' : ''}</small>` : '<small class="text-muted">No links provided yet</small>'}</p>

                                <p><strong>Produk:</strong><br>
                                ${data.produk || 'Not specified'}</p>

                                <p><strong>Referensi:</strong><br>
                                ${data.referensi || 'Not specified'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card card-outline card-warning mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Creative Review Checklist</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Content Strategy Review:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Objektif aligned with brand goals</li>
                                    <li><i class="fas fa-check text-success"></i> Content type suitable for platform</li>
                                    <li><i class="fas fa-check text-success"></i> Pillar consistency maintained</li>
                                    <li><i class="fas fa-check text-success"></i> Hook is engaging and relevant</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Production Review:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Talent confirmed and suitable</li>
                                    <li><i class="fas fa-check text-success"></i> Venue booking confirmed</li>
                                    <li><i class="fas fa-check text-success"></i> Production date scheduled</li>
                                    <li><i class="fas fa-check text-success"></i> Content editor assigned</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group mt-3">
                    <label for="step_review_comments">Review Comments (Optional)</label>
                    <textarea class="form-control" name="review_comments" id="step_review_comments" rows="3" 
                              placeholder="Add any review comments or feedback...">${data.review_comments || ''}</textarea>
                    <small class="form-text text-muted">Optional: Add any specific feedback or approval notes.</small>
                </div>
            `;
            
        case 5: // Content Editor (unchanged)
            return `
                <div class="alert alert-dark">
                    <h6><i class="fas fa-edit"></i> Content Editing</h6>
                    <p>Edit and finalize the content, then provide the link to edited materials.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="step_link_hasil_edit">Link Hasil Edit <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="link_hasil_edit" id="step_link_hasil_edit" 
                                   value="${data.link_hasil_edit || ''}" required>
                            <small class="form-text text-muted">Provide the link to the final edited content (images, videos, etc.).</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">Raw Content</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Assigned Editor:</strong><br>
                                ${data.assignee_content_editor || 'Not assigned'}</p>
                                <p><strong>Raw Content Links:</strong><br>
                                <small class="text-muted">${data.link_raw_content || 'No links provided'}</small></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Content Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Platform:</strong> ${data.platform || 'Not specified'}</p>
                                <p><strong>Account:</strong> ${data.akun || 'Not specified'}</p>
                                <p><strong>Content Type:</strong> ${data.jenis_konten || 'Not specified'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Target Date:</strong> ${data.target_posting_date ? formatDateTime(data.target_posting_date) : 'Not set'}</p>
                                <p><strong>Pillar:</strong> ${data.pillar || 'Not specified'}</p>
                                <p><strong>Final Talent:</strong> ${data.talent_fix || data.talent || 'Not specified'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
        case 6: // Store to Content Bank (updated from Final Posting)
            return `
                <div class="alert alert-success">
                    <h6><i class="fas fa-database"></i> Store to Content Bank</h6>
                    <p>Final step: Store the completed content to content bank and provide posting link.</p>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="step_input_link_posting">Content Bank / Posting Link <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="input_link_posting" id="step_input_link_posting" 
                                   value="${data.input_link_posting || ''}" required>
                            <small class="form-text text-muted">Provide the link to the published post or content bank storage location.</small>
                        </div>

                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">Content Bank Guidelines</h3>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Ensure content is properly tagged and categorized</li>
                                    <li><i class="fas fa-check text-success"></i> Include all metadata (date, platform, talent, etc.)</li>
                                    <li><i class="fas fa-check text-success"></i> Verify file quality and format compatibility</li>
                                    <li><i class="fas fa-check text-success"></i> Add content to searchable database</li>
                                    <li><i class="fas fa-check text-success"></i> Create backup copies if required</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title">Final Content Details</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Platform:</strong> ${data.platform || 'Not specified'}</p>
                                <p><strong>Account:</strong> ${data.akun || 'Not specified'}</p>
                                <p><strong>Content Type:</strong> ${data.jenis_konten || 'Not specified'}</p>
                                <p><strong>Final Talent:</strong> ${data.talent_fix || data.talent || 'Not specified'}</p>
                                <p><strong>Production Date:</strong><br>
                                <small>${data.production_date ? formatDateTime(data.production_date) : 'Not set'}</small></p>
                                <p><strong>Target Posting:</strong><br>
                                <small>${data.target_posting_date ? formatDateTime(data.target_posting_date) : 'Not set'}</small></p>
                                <p><strong>Edited Content:</strong><br>
                                ${data.link_hasil_edit ? `<a href="${data.link_hasil_edit}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-external-link-alt"></i> View</a>` : '<small class="text-muted">No edited content</small>'}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Complete Content Journey</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Strategy & Planning:</h6>
                                <p class="text-muted"><strong>Objektif:</strong> ${data.objektif || 'No objective specified'}</p>
                                <p class="text-muted"><strong>Hook:</strong> ${data.hook ? data.hook.substring(0, 100) + (data.hook.length > 100 ? '...' : '') : 'No hook specified'}</p>
                            </div>
                            <div class="col-md-4">
                                <h6>Content Creation:</h6>
                                <p class="text-muted"><strong>Brief:</strong> ${data.brief_konten ? data.brief_konten.substring(0, 100) + (data.brief_konten.length > 100 ? '...' : '') : 'No brief provided'}</p>
                                <p class="text-muted"><strong>Caption:</strong> ${data.caption ? data.caption.substring(0, 100) + (data.caption.length > 100 ? '...' : '') : 'No caption provided'}</p>
                            </div>
                            <div class="col-md-4">
                                <h6>Production & Editing:</h6>
                                <p class="text-muted"><strong>Editor:</strong> ${data.assignee_content_editor || 'Not assigned'}</p>
                                <p class="text-muted"><strong>Production:</strong> ${data.production_date ? formatDateTime(data.production_date) : 'Not scheduled'}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Note:</strong> Once this step is completed, the content plan will be marked as "Posted" and archived in the system.
                </div>
            `;
            
        default:
            return '<p>Invalid step</p>';
    }
}
</script>
@stop