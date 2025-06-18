@extends('adminlte::page')

@section('title', 'Content Production Calendar')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Content Production Calendar</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contentPlan.index') }}">Content Production</a></li>
                <li class="breadcrumb-item active">Calendar View</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Content Plan Calendar</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="switchToTableView()">
                            <i class="fas fa-table"></i> Table View
                        </button>
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addContentPlanModal">
                            <i class="fas fa-plus"></i> Add New Content Plan
                        </button>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <!-- Calendar Header -->
                    <div class="calendar-header">
                        <div class="month-navigation">
                            <button class="btn btn-outline-primary" onclick="changeMonth(-1)">
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                            <h2 class="current-month" id="currentMonth"></h2>
                            <button class="btn btn-outline-primary" onclick="changeMonth(1)">
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
                            <span class="legend-text">Ready to Post</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color badge-success-alt"></div>
                            <span class="legend-text">Posted</span>
                        </div>
                    </div>
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
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
    }

    .month-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .current-month {
        margin: 0;
        font-weight: 600;
        color: #495057;
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
        padding: 15px 10px;
        text-align: center;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
    }

    .calendar-day {
        background: white;
        min-height: 120px;
        padding: 8px;
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
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 8px;
        color: #495057;
    }

    .content-item {
        background: white;
        border-radius: 4px;
        padding: 4px 6px;
        margin-bottom: 3px;
        font-size: 0.75rem;
        border-left: 3px solid;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .content-item:hover {
        transform: translateX(2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
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
        margin-bottom: 2px;
        color: #495057;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .item-talent {
        color: #6c757d;
        font-size: 0.7rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .item-platform {
        color: #007bff;
        font-size: 0.65rem;
        font-weight: 500;
        text-transform: uppercase;
    }

    .status-legend {
        padding: 15px 20px;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 5px 10px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 2px;
    }

    .legend-color.badge-secondary { background-color: #6c757d; }
    .legend-color.badge-info { background-color: #17a2b8; }
    .legend-color.badge-warning { background-color: #ffc107; }
    .legend-color.badge-primary { background-color: #007bff; }
    .legend-color.badge-dark { background-color: #343a40; }
    .legend-color.badge-success { background-color: #28a745; }
    .legend-color.badge-success-alt { background-color: #20c997; }

    .legend-text {
        font-size: 0.85rem;
        color: #495057;
        font-weight: 500;
    }

    .empty-day {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
        font-style: italic;
        font-size: 0.8rem;
        height: 100%;
    }

    @media (max-width: 768px) {
        .calendar-day {
            min-height: 100px;
            padding: 4px;
        }
        
        .content-item {
            font-size: 0.7rem;
            padding: 3px 4px;
        }
        
        .month-navigation {
            flex-direction: column;
            gap: 10px;
        }

        .current-month {
            order: -1;
        }

        .legend-item {
            padding: 3px 8px;
        }
        
        .legend-text {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .calendar-day {
            min-height: 80px;
            padding: 2px;
        }
        
        .day-number {
            font-size: 1rem;
            margin-bottom: 4px;
        }
        
        .content-item {
            font-size: 0.65rem;
            padding: 2px 4px;
            margin-bottom: 2px;
        }
        
        .item-title {
            font-size: 0.65rem;
        }
        
        .item-talent {
            font-size: 0.6rem;
        }
    }
</style>
@stop

@section('js')
<script>
    let currentDate = new Date();
    let contentPlans = [];

    $(document).ready(function() {
        loadContentPlans();
        generateCalendar();

        // Handle Add Content Plan Form Submit (reuse from your existing code)
        $('#addContentPlanForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '{{ route('contentPlan.store') }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#addContentPlanModal').modal('hide');
                        loadContentPlans(); // Reload calendar data
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

        // Clear form on modal close
        $('.modal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();
        });
    });

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
        const dayHeaders = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
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
                        ${plan.platform ? `<div class="item-platform">${plan.platform}</div>` : ''}
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

    function switchToTableView() {
        window.location.href = '{{ route('contentPlan.index') }}';
    }
</script>
@stop