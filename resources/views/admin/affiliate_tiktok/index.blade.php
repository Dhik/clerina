@extends('adminlte::page')

@section('title', 'Affiliate TikTok')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Affiliate TikTok</h1>
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
                                <select class="form-control" id="creatorFilter">
                                    <option value="">All Creators</option>
                                    @foreach($creatorList as $creator)
                                        <option value="{{ $creator }}">{{ $creator }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-default" id="resetFilterBtn">Reset Filter</button>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importAffiliateTiktokModal" id="btnImportAffiliateTiktok">
                                        <i class="fas fa-file-upload"></i> Import Affiliate TikTok (Excel)
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
                                        <h5 class="card-title mb-0">GMV Over Time</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="gmvChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">TikTok Performance Funnel</h5>
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
                <table id="affiliateTiktokTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Creators</th>
                            <th>Total GMV</th>
                            <th>Live GMV</th>
                            <th>Products Sold</th>
                            <th>Items Sold</th>
                            <th>Est. Commission</th>
                            <th>Avg Order Value</th>
                            <th>Total Orders</th>
                            <th>Total Impressions</th>
                            <th>Live Streams</th>
                            <th>Conversion Rate</th>
                            <th>Avg Commission/Creator</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importAffiliateTiktokModal" tabindex="-1" role="dialog" aria-labelledby="importAffiliateTiktokModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importAffiliateTiktokModalLabel">Import Affiliate TikTok Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importAffiliateTiktokForm" enctype="multipart/form-data">
                    @csrf
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Date Extraction:</strong> The import date will be automatically extracted from the filename.
                        <br>Example: <code>Creator_List_20250601-20250601_20250619014737_Shop_Tokopedia (1).xlsx</code> → Date: <strong>2025-06-01</strong>
                    </div>
                    <div class="form-group">
                        <label for="affiliateTiktokExcelFile">Import Excel File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="affiliateTiktokExcelFile" name="affiliate_tiktok_excel_file" accept=".xlsx,.xls" required>
                            <label class="custom-file-label" for="affiliateTiktokExcelFile">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Please upload an Excel file (.xlsx or .xls) with TikTok Affiliate data</small>
                        <small class="form-text text-muted">Expected columns in this order:</small>
                        <small class="form-text text-muted"><strong>A: Creator Username, B: Affiliate GMV, C: Affiliate Live GMV, D: Affiliate Shoppable Video, E: Affiliate Product Card GMV, F: Affiliate Products Sold, G: Items Sold, H: Est Commission, I: Avg Order Value, J: Affiliate Orders, K: CTR, L: Product Impressions, M: Avg Affiliate Customers, N: Affiliate Live Streams, O: Open Collaboration GMV, P: Open Collaboration Est, Q: Affiliate Refunded GMV, R: Affiliate Items Refunded, S: Affiliate Followers</strong></small>
                        <small class="form-text text-muted"><strong>Filename Format:</strong> Creator_List_YYYYMMDD-YYYYMMDD_timestamp_description.xlsx</small>
                    </div>
                    <div class="form-group d-none" id="errorImportAffiliateTiktok"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="dailyDetailsModal" tabindex="-1" role="dialog" aria-labelledby="dailyDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dailyDetailsModalLabel">TikTok Affiliate Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="modalFilterDates" class="form-control rangeDate" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-default" id="modalResetFilterBtn">Reset</button>
                    </div>
                </div>
                
                <table id="affiliateDetailsTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Creator</th>
                            <th>Affiliate GMV</th>
                            <th>Live GMV</th>
                            <th>Shoppable Video</th>
                            <th>Product Card GMV</th>
                            <th>Products Sold</th>
                            <th>Items Sold</th>
                            <th>Est. Commission</th>
                            <th>AOV</th>
                            <th>Orders</th>
                            <th>CTR</th>
                            <th>Impressions</th>
                            <th>Customers</th>
                            <th>Live Streams</th>
                            <th>Open Collab GMV</th>
                            <th>Open Collab Est</th>
                            <th>Refunded GMV</th>
                            <th>Items Refunded</th>
                            <th>Followers</th>
                            <th>Conv. Rate</th>
                            <th>Comm. Rate</th>
                            <th>Refund Rate</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
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

.custom-file-label::after {
    content: "Browse";
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

.modal-xl {
    max-width: 1400px;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
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

@include('admin.affiliate_tiktok.scripts')
@stop