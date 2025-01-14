@extends('adminlte::page')

@section('title', $report->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $report->title }}</h1>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="card-title">{{ $report->description }}</h3>
                        </div>
                        <div class="col-auto">
                            <span class="badge badge-primary">{{ ucfirst($report->type) }}</span>
                            <span class="badge badge-info ml-2">{{ ucfirst($report->platform) }}</span>
                            <span class="badge badge-secondary ml-2">{{ $report->month }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="tableauContainer" class="tableau-container">
                        {!! $report->link !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .tableau-container {
        width: 100%;
        height: calc(100vh - 170px); /* Adjust height based on your layout */
        position: relative;
    }

    .tableauPlaceholder {
        width: 100% !important;
        height: 100% !important;
    }

    /* Make tableau viz responsive */
    .tableauViz {
        width: 100% !important;
        height: 100% !important;
    }

    /* Hide tableau elements we don't need */
    .tableauPlaceholder .tab-widget {
        display: none !important;
    }
</style>
@stop