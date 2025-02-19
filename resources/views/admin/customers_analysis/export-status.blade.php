<!-- resources/views/customer/export-status.blade.php -->
@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Export Status</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('customer_analysis.index') }}">Customer Analysis</a></li>
                        <li class="breadcrumb-item active">Export Status</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Export Status</h3>
                        </div>
                        
                        <div class="card-body text-center p-5">
                            @if(session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif
                            
                            @if ($completed)
                                <div class="alert alert-success p-4">
                                    <h4><i class="fas fa-check-circle"></i> Export Complete!</h4>
                                    <p class="mt-3">Your export has been successfully processed and is ready to download.</p>
                                </div>
                                
                                <a href="{{ $downloadUrl }}" class="btn btn-primary btn-lg mt-4">
                                    <i class="fas fa-download"></i> Download Export
                                </a>
                                
                                <p class="text-muted mt-4">
                                    <small>Large exports are split into multiple Excel files and packaged as a ZIP archive.</small>
                                </p>
                            @else
                                <div class="export-processing p-4">
                                    <div class="spinner-border text-primary mb-4" style="width: 4rem; height: 4rem;" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    
                                    <h3 class="mt-4">Processing Your Export</h3>
                                    <p class="text-muted mt-3">This may take several minutes for large datasets.</p>
                                    <p>Please don't close this window. The page will automatically refresh when the export is complete.</p>
                                    
                                    <div class="progress mt-4">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        @if ($completed)
                        <div class="card-footer text-center">
                            <a href="{{ route('customer_analysis.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Customer Analysis
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@if (!$completed)
<script>
    // Refresh the page every 10 seconds to check if export is complete
    setTimeout(function() {
        window.location.reload();
    }, 10000);
</script>
@endif
@endsection