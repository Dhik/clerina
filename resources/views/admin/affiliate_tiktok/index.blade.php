@extends('adminlte::page')

@section('title', 'Affiliate Shopee')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Affiliate Shopee</h1>
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
                                <select class="form-control" id="affiliateFilter">
                                    <option value="">All Affiliates</option>
                                    @foreach($affiliateList as $affiliate)
                                        <option value="{{ $affiliate }}">{{ $affiliate }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-default" id="resetFilterBtn">Reset Filter</button>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importAffiliateShopeeModal" id="btnImportAffiliateShopee">
                                        <i class="fas fa-file-upload"></i> Import Affiliate Shopee (CSV)
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
                                        <h5 class="card-title mb-0">Commission Over Time</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="commissionChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Affiliate Performance Funnel</h5>
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
                <table id="affiliateShopeeTable" class="table table-bordered table-striped dataTable responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Affiliates</th>
                            <th>Total Omzet</th>
                            <th>Products Sold</th>
                            <th>Total Orders</th>
                            <th>Total Clicks</th>
                            <th>Est. Commission</th>
                            <th>Avg ROI</th>
                            <th>Total Buyers</th>
                            <th>New Buyers</th>
                            <th>CTR</th>
                            <th>Avg Commission/Affiliate</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importAffiliateShopeeModal" tabindex="-1" role="dialog" aria-labelledby="importAffiliateShopeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importAffiliateShopeeModalLabel">Import Affiliate Shopee Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importAffiliateShopeeForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="importDate">Import Date</label>
                        <input type="date" class="form-control" id="importDate" name="import_date" required>
                        <small class="form-text text-muted">Select the date for this affiliate data import</small>
                    </div>
                    <div class="form-group">
                        <label for="affiliateShopeeCSVFile">Import CSV File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="affiliateShopeeCSVFile" name="affiliate_shopee_csv_file" accept=".csv" required>
                            <label class="custom-file-label" for="affiliateShopeeCSVFile">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Please upload a CSV file with Affiliate Shopee data</small>
                        <small class="form-text text-muted">Expected columns in this order:</small>
                        <small class="form-text text-muted"><strong>Column A: Affiliate ID, Column B: Nama Affiliate, Column C: Username Affiliate, Column D: Omzet Penjualan, Column E: Produk Terjual, Column F: Pesanan, Column G: Clicks, Column H: Estimasi Komisi, Column I: ROI, Column J: Total Pembeli, Column K: Pembeli Baru</strong></small>
                        <small class="form-text text-muted">Numbers can contain dots as thousand separators (e.g., 1.234.567)</small>
                    </div>
                    <div class="form-group d-none" id="errorImportAffiliateShopee"></div>
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
                <h5 class="modal-title" id="dailyDetailsModalLabel">Affiliate Details</h5>
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
                            <th>Affiliate ID</th>
                            <th>Nama Affiliate</th>
                            <th>Username</th>
                            <th>Omzet Penjualan</th>
                            <th>Produk Terjual</th>
                            <th>Pesanan</th>
                            <th>Clicks</th>
                            <th>Est. Komisi</th>
                            <th>ROI</th>
                            <th>Total Pembeli</th>
                            <th>Pembeli Baru</th>
                            <th>CTR</th>
                            <th>Commission Rate</th>
                            <th>New Buyer Rate</th>
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
    max-width: 1200px;
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
@include('admin.affiliate_shopee.scripts')
@stop