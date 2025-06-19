@extends('adminlte::page')

@section('title', 'Live TikTok')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Live TikTok</h1>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filter Controls Card -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <input type="text" id="filterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-default" id="resetFilterBtn">Reset Filter</button>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addRecordModal" id="btnAddRecord">
                                        <i class="fas fa-plus"></i> Add New Record
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">GMV Live Over Time</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="gmvChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Live TikTok Performance Funnel</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="funnelChart"></div>
                                        <div id="funnelMetrics" class="mt-4"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- DataTable -->
        <div class="card">
            <div class="card-body">
                <table id="liveTiktokTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>GMV Live</th>
                            <th>Pesanan</th>
                            <th>Tayangan</th>
                            <th>GPM</th>
                            <th>Conversion Rate</th>
                            <th>AOV</th>
                            <th>Performance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Record Modal -->
<div class="modal fade" id="addRecordModal" tabindex="-1" role="dialog" aria-labelledby="addRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRecordModalLabel">Add New Live TikTok Record</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="recordForm">
                    @csrf
                    <input type="hidden" id="recordId" name="recordId">
                    <input type="hidden" id="formMethod" value="POST">
                    
                    <div class="form-group">
                        <label for="date">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="gmv_live">GMV Live <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" class="form-control" id="gmv_live" name="gmv_live" min="0" step="1" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="pesanan">Pesanan <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="pesanan" name="pesanan" min="0" step="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tayangan">Tayangan <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="tayangan" name="tayangan" min="0" step="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="gpm">GPM <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" class="form-control" id="gpm" name="gpm" min="0" step="1" required>
                        </div>
                    </div>
                    
                    <div class="form-group d-none" id="errorMessage">
                        <div class="alert alert-danger" id="errorText"></div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Save Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.card .card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.badge {
    font-size: 0.75em;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #5a5c69;
    background-color: #f8f9fc;
}

.btn-group .btn {
    border-radius: 0.35rem;
    margin-right: 5px;
}

.daterangepicker {
    z-index: 9999 !important;
}

.table-responsive {
    overflow-x: auto;
}

.dataTables_wrapper .dataTables_processing {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    margin-left: -100px;
    margin-top: -26px;
    text-align: center;
    padding: 1em 0;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #ddd;
    border-radius: 4px;
}

.apexcharts-canvas {
    margin: 0 auto;
}

#funnelMetrics {
    max-height: 370px;
    overflow-y: auto;
}

.form-group label {
    font-weight: 600;
}

.text-danger {
    color: #dc3545 !important;
}

.input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
}

/* Custom scrollbar for tables */
.dataTables_scrollBody::-webkit-scrollbar {
    height: 8px;
}

.dataTables_scrollBody::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body .row .col-auto {
        margin-bottom: 10px;
    }
    
    .btn-group {
        width: 100%;
    }
    
    .btn-group .btn {
        width: 100%;
        margin-bottom: 5px;
    }
}
</style>
@stop

@section('js')
<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

@include('admin.live_tiktok.scripts')
@stop