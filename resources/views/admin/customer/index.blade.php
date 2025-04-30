@extends('adminlte::page')

@section('title', trans('labels.customer'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ trans('labels.customer') }}</h1>
        <button class="btn btn-primary" id="refreshStats">
            <i class="fas fa-sync-alt mr-1"></i> {{ trans('labels.refresh') }}
        </button>
    </div>
@stop

@section('content')
    <!-- KPI Cards Row -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-info elevation-3">
                <div class="inner">
                    <h3 id="totalCustomersKPI">0</h3>
                    <p>{{ trans('labels.total_customers') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-success elevation-3">
                <div class="inner">
                    <h3 id="newCustomersKPI">0</h3>
                    <p>{{ trans('labels.new_customers') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-warning elevation-3">
                <div class="inner">
                    <h3 id="repeatedCustomersKPI">0</h3>
                    <p>{{ trans('labels.repeated_customers') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-redo"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-danger elevation-3">
                <div class="inner">
                    <h3 id="currentMonthCustomersKPI">0</h3>
                    <p>{{ trans('labels.this_month') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-1"></i> {{ trans('labels.filters') }}
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filterCountOrders">
                                    <i class="fas fa-shopping-cart mr-1"></i> {{ trans('labels.total_order') }}
                                </label>
                                <input type="number" id="filterCountOrders" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filterMonth">
                                    <i class="fas fa-calendar-day mr-1"></i> {{ trans('labels.order_date') }}
                                </label>
                                <input type="month" class="form-control" id="filterMonth" placeholder="{{ trans('placeholder.select_month') }}" autocomplete="off">
                                <small class="form-text text-muted">{{ trans('labels.leave_empty_for_current_month') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filterType">
                                    <i class="fas fa-tag mr-1"></i> {{ trans('labels.type') }}
                                </label>
                                <select id="filterType" class="form-control">
                                    <option value="">{{ trans('labels.all_types') }}</option>
                                    <option value="New Customer">{{ trans('labels.new_customer') }}</option>
                                    <option value="Repeated">{{ trans('labels.repeated_customer') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button id="applyFilterBtn" class="btn btn-primary mr-2">
                                <i class="fas fa-search mr-1"></i> {{ trans('labels.apply') }}
                            </button>
                            <button id="resetFilterBtn" class="btn btn-secondary">
                                <i class="fas fa-redo mr-1"></i> {{ trans('labels.reset') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-1"></i> {{ trans('labels.customer_list') }}
                    </h3>
                </div>
                <div class="card-body">
                    <table id="customerTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="customerTable-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.name') }}</th>
                                <th width="15%">{{ trans('labels.type') }}</th>
                                <th>{{ trans('labels.phone_number') }}</th>
                                <th>{{ trans('labels.total_order') }}</th>
                                <th>{{ trans('labels.last_order_date') }}</th>
                                <th width="10%">{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.socialMedia.modal')
    @include('admin.socialMedia.modal-update')
@stop

@section('css')
<style>
    /* KPI Card Styles */
    .small-box {
        transition: transform 0.2s;
        border-radius: 8px;
        overflow: hidden;
    }
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .small-box .inner {
        padding: 20px;
    }
    .small-box h3 {
        font-size: 38px;
        font-weight: 700;
        margin: 0;
        white-space: nowrap;
    }
    .small-box p {
        font-size: 15px;
        margin-bottom: 0;
    }
    .small-box .icon {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 70px;
        color: rgba(255, 255, 255, 0.15);
    }
    
    /* Customer Type Badge Styles */
    .customer-type {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 12px;
        text-align: center;
        line-height: 1;
        white-space: nowrap;
    }
    .customer-type.new {
        background-color: #28a745;
        color: white;
    }
    .customer-type.repeated {
        background-color: #ffc107;
        color: #212529;
    }
    
    /* Table Styles */
    #customerTable thead th {
        background-color: #f4f6f9;
        font-weight: 600;
    }
    
    /* Action Button Styles */
    .btn-action {
        margin-right: 5px;
        border-radius: 4px;
    }
    
    /* Make filters card collapsible */
    .card-outline {
        border-top: 3px solid;
    }
    
    /* Add animation to refresh button */
    .fa-sync-alt {
        transition: transform 0.5s ease;
    }
    .fa-sync-alt.spin {
        transform: rotate(360deg);
    }
</style>
@stop

@section('js')
    <script>
        $(document).ready(function () {
            // Initialize variables
            const customerTableSelector = $('#customerTable');
            const filterCountOrders = $('#filterCountOrders');
            const filterType = $('#filterType');
            const filterMonth = $('#filterMonth');
            
            // Set default month value to current month
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            filterMonth.val(`${year}-${month}`);
            
            // Initialize DataTable
            let customerTable = customerTableSelector.DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('customer.get') }}",
                    data: function (d) {
                        d.filterCountOrders = filterCountOrders.val();
                        d.filterMonth = filterMonth.val();
                        d.filterType = filterType.val();
                    }
                },
                columns: [
                    {data: 'name', name: 'name'},
                    {
                        data: 'type', 
                        name: 'type',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                if (data === 'New Customer') {
                                    return '<span class="customer-type new"><i class="fas fa-user-plus mr-1"></i> ' + data + '</span>';
                                } else if (data === 'Repeated') {
                                    return '<span class="customer-type repeated"><i class="fas fa-redo mr-1"></i> ' + data + '</span>';
                                } else {
                                    return data;
                                }
                            }
                            return data;
                        }
                    },
                    {data: 'phone_number', name: 'phone_number'},
                    {data: 'count_orders', name: 'count_orders'},
                    {
                        data: 'last_order_date', 
                        name: 'last_order_date',
                        render: function(data) {
                            if (data) {
                                const date = new Date(data);
                                return date.toLocaleDateString('en-GB', {
                                    day: '2-digit', 
                                    month: 'short', 
                                    year: 'numeric'
                                });
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'actions', 
                        orderable: false, 
                        searchable: false,
                        render: function(data) {
                            // This assumes 'actions' contains HTML like in your original code
                            // We're returning it as-is to preserve your action buttons
                            return data;
                        }
                    }
                ],
                order: [[4, 'desc']], // Sort by last_order_date column in descending order
                drawCallback: function() {
                    // Load KPI data after table is drawn
                    loadKPIData();
                }
            });

            // Apply filters button
            $('#applyFilterBtn').click(function() {
                customerTable.draw();
            });

            // Reset filters button
            $('#resetFilterBtn').click(function () {
                filterCountOrders.val('');
                filterType.val('');
                
                // Reset to current month instead of clearing it
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                filterMonth.val(`${year}-${month}`);
                
                customerTable.draw();
            });
            
            // Refresh stats button
            $('#refreshStats').click(function() {
                $(this).find('.fa-sync-alt').addClass('spin');
                loadKPIData();
                setTimeout(() => {
                    $(this).find('.fa-sync-alt').removeClass('spin');
                }, 1000);
            });
            
            function loadKPIData() {
                // Get current filter month value
                const filterMonthValue = $('#filterMonth').val();
                
                // Show loading state
                $('.small-box h3').text('...');
                
                // Make AJAX request to get current stats
                $.ajax({
                    url: '{{ route("customer.kpi") }}',
                    method: 'GET',
                    data: {
                        filterMonth: filterMonthValue
                    },
                    success: function(response) {
                        // Update KPI values with animation
                        animateCounter($('#totalCustomersKPI'), 0, response.total);
                        animateCounter($('#newCustomersKPI'), 0, response.new);
                        animateCounter($('#repeatedCustomersKPI'), 0, response.repeated);
                        animateCounter($('#currentMonthCustomersKPI'), 0, response.currentMonth);
                    },
                    error: function() {
                        // In case of error, show error indicator
                        $('.small-box h3').text('!');
                    }
                });
            }
            
            // Function to animate counter
            function animateCounter($element, start, end) {
                $({ Counter: start }).animate({
                    Counter: end
                }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function() {
                        $element.text(Math.ceil(this.Counter));
                    }
                });
            }
            
            // Load KPI data on initial page load
            loadKPIData();
            
            // Handle KPI card click for detailed information
            $('.small-box').click(function() {
                const type = $(this).find('p').text();
                // Filter by type when clicking KPI card
                if (type.includes('New')) {
                    filterType.val('New Customer');
                } else if (type.includes('Repeated')) {
                    filterType.val('Repeated');
                } else {
                    filterType.val('');
                }
                customerTable.draw();
            });
        });
    </script>
@stop