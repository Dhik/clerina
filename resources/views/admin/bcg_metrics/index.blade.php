@extends('adminlte::page')

@section('title', 'BCG Metrics Analysis')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>BCG Traffic-Conversion Analysis</h1>
        <div class="btn-group">
            <button class="btn btn-info" onclick="showRecommendations()">
                <i class="fas fa-lightbulb"></i> View Recommendations
            </button>
            <button class="btn btn-secondary" data-toggle="modal" data-target="#advancedFilterModal">
                <i class="fas fa-filter"></i> Advanced Filters
            </button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('bcg_metrics.export', ['format' => 'csv']) }}">
                        <i class="fas fa-file-csv"></i> CSV Data
                    </a>
                    <a class="dropdown-item" href="{{ route('bcg_metrics.export', ['format' => 'json']) }}">
                        <i class="fas fa-file-code"></i> JSON Data
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Include Content Sections -->
    @include('admin.bcg_metrics.sections.overview_cards')
    @include('admin.bcg_metrics.sections.bcg_matrix_chart')
    @include('admin.bcg_metrics.sections.products_table')
    @include('admin.bcg_metrics.sections.quick_actions')
</div>

<!-- Include Modals -->
@include('admin.bcg_metrics.modals')
@stop

@section('css')
    @include('admin.bcg_metrics.styles')
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    
    @include('admin.bcg_metrics.scripts')
@stop