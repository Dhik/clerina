@extends('adminlte::page')

@section('title', "Financial Report")

@section('content_header')
    <h1>Financial Report</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <input type="text" id="filterDates" class="form-control daterange" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="small-box bg-gradient-success">
                        <div class="inner">
                            <h4 id="totalGrossRevenue">Rp 0</h4>
                            <p>Total Gross Revenue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="small-box bg-gradient-primary">
                        <div class="inner">
                            <h4 id="totalHpp">Rp 0</h4>
                            <p>Total HPP</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="small-box bg-gradient-info">
                        <div class="inner">
                            <h4 id="totalFeeAdmin">Rp 0</h4>
                            <p>Total Fee Admin, Service and Discount</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="small-box bg-gradient-warning">
                        <div class="inner">
                            <h4 id="netProfit">Rp 0</h4>
                            <p>Net Revenue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Channel Summary Cards Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Revenue by Channel</h3>
                        </div>
                        <div class="card-body">
                            <table class="channel-table" id="channelRevenueCards">
                                <!-- Table rows will be dynamically added here -->
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">HPP by Channel</h3>
                        </div>
                        <div class="card-body">
                            <table class="channel-table" id="channelHppCards">
                                <!-- Table rows will be dynamically added here -->
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Financial Report Details</h3>
                </div>
                <div class="card-body">
                    <!-- Tabs for different report views -->
                    <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="summary-tab" data-toggle="tab" href="#summary" role="tab" aria-controls="summary" aria-selected="true">Summary</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="gross-revenue-tab" data-toggle="tab" href="#gross-revenue" role="tab" aria-controls="gross-revenue" aria-selected="false">Gross Revenue</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="hpp-tab" data-toggle="tab" href="#hpp" role="tab" aria-controls="hpp" aria-selected="false">HPP</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="fee-admin-tab" data-toggle="tab" href="#fee-admin" role="tab" aria-controls="fee-admin" aria-selected="false">Fee Admin</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="reportTabsContent">
                        <!-- Summary Tab -->
                        <div class="tab-pane fade show active" id="summary" role="tabpanel" aria-labelledby="summary-tab">
                            <div class="table-responsive">
                                <table id="summaryTable" class="table table-bordered table-striped dataTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Total Gross Revenue</th>
                                            <th>Total HPP</th>
                                            <th>Total Fee Admin</th>
                                            <th>Net Profit</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Gross Revenue Tab -->
                        <div class="tab-pane fade" id="gross-revenue" role="tabpanel" aria-labelledby="gross-revenue-tab">
                            <div class="table-responsive">
                                <table id="grossRevenueTable" class="table table-bordered table-striped dataTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            @foreach($salesChannels as $channel)
                                            <th>{{ $channel->name }}</th>
                                            @endforeach
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                        <!-- HPP Tab -->
                        <div class="tab-pane fade" id="hpp" role="tabpanel" aria-labelledby="hpp-tab">
                            <div class="table-responsive">
                                <table id="hppTable" class="table table-bordered table-striped dataTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            @foreach($salesChannels as $channel)
                                            <th>{{ $channel->name }}</th>
                                            @endforeach
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                        <!-- Fee Admin Tab -->
                        <div class="tab-pane fade" id="fee-admin" role="tabpanel" aria-labelledby="fee-admin-tab">
                            <div class="table-responsive">
                                <table id="feeAdminTable" class="table table-bordered table-striped dataTable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            @foreach($salesChannels as $channel)
                                            <th>{{ $channel->name }}</th>
                                            @endforeach
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Standard Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="detailTable" class="table table-bordered table-striped">
                            <thead id="detailTableHead">
                                <!-- Dynamic headers will be added here -->
                            </thead>
                            <tbody id="detailTableBody">
                                <!-- Dynamic content will be added here -->
                            </tbody>
                            <tfoot id="detailTableFoot">
                                <!-- Dynamic footer will be added here -->
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- HPP Detail Modal with Tabs -->
    <div class="modal fade" id="hppDetailModal" tabindex="-1" role="dialog" aria-labelledby="hppDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hppDetailModalLabel">HPP Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body position-relative">
                    <!-- Loading overlay positioned inside modal-body with position-relative -->
                    <div id="hpp-loading-overlay" class="loading-overlay">
                        <div class="spinner-border loading-spinner text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    
                    <!-- Channel summary section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card card-primary card-outline card-tabs">
                                <div class="card-header p-0 pt-1 border-bottom-0">
                                    <ul class="nav nav-tabs" id="channel-tabs" role="tablist">
                                        <!-- Channel tabs will be added here -->
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content" id="channel-tab-content">
                                        <!-- Channel tab contents will be added here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@include('admin.laporan_keuangan.css.style')
@stop

@section('js')
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
@include('admin.laporan_keuangan.js.script')
@stop