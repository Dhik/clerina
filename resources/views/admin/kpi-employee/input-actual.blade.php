@extends('adminlte::page')

@section('title', 'Input Actual KPI')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Input Actual KPI</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kPIEmployee.index') }}">KPI Employee</a></li>
                <li class="breadcrumb-item active">Input Actual</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Update Actual KPI Value</h3>
                </div>
                <div class="card-body">
                    <!-- KPI Information -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info"></i> KPI Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>KPI:</strong> {{ $kPIEmployee->kpi }}<br>
                                        <strong>Employee:</strong> {{ $kPIEmployee->employee->full_name }}<br>
                                        <strong>Employee ID:</strong> {{ $kPIEmployee->employee->employee_id }}<br>
                                        <strong>Department:</strong> {{ $kPIEmployee->department }}<br>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Position:</strong> {{ $kPIEmployee->position }}<br>
                                        <strong>Method:</strong> {{ $kPIEmployee->method_calculation }}<br>
                                        <strong>Target:</strong> {{ number_format($kPIEmployee->target, 2) }}<br>
                                        <strong>Current Actual:</strong> {{ number_format($kPIEmployee->actual, 2) }}<br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Achievement -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">Current Performance</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-target"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Target</span>
                                                    <span class="info-box-number">{{ number_format($kPIEmployee->target, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-chart-line"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Current Actual</span>
                                                    <span class="info-box-number">{{ number_format($kPIEmployee->actual, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                @php
                                                    $currentAchievement = 0;
                                                    if ($kPIEmployee->target > 0) {
                                                        if ($kPIEmployee->method_calculation === 'higher better') {
                                                            $currentAchievement = ($kPIEmployee->actual / $kPIEmployee->target) * 100;
                                                        } else {
                                                            $currentAchievement = ($kPIEmployee->target / $kPIEmployee->actual) * 100;
                                                        }
                                                    }
                                                    $achievementClass = $currentAchievement >= 100 ? 'success' : ($currentAchievement >= 75 ? 'warning' : 'danger');
                                                @endphp
                                                <span class="info-box-icon bg-{{ $achievementClass }}"><i class="fas fa-percentage"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Achievement</span>
                                                    <span class="info-box-number">{{ number_format($currentAchievement, 2) }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Form -->
                    <form action="{{ route('kPIEmployee.updateActual', $kPIEmployee->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="actual">New Actual Value <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control @error('actual') is-invalid @enderror" 
                                               id="actual" name="actual" value="{{ old('actual', $kPIEmployee->actual) }}" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-edit"></i></span>
                                        </div>
                                    </div>
                                    @error('actual')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Target: {{ number_format($kPIEmployee->target, 2) }} | 
                                        Method: {{ $kPIEmployee->method_calculation }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Achievement Preview</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="achievement-preview" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-calculator"></i> Real-time calculation based on your input
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-light">
                                    <h6><i class="fas fa-lightbulb"></i> Calculation Method Information:</h6>
                                    <ul class="mb-0">
                                        @if($kPIEmployee->method_calculation === 'higher better')
                                            <li><strong>Higher Better:</strong> Achievement = (Actual ÷ Target) × 100%</li>
                                            <li>The higher the actual value compared to target, the better the performance</li>
                                        @else
                                            <li><strong>Lower Better:</strong> Achievement = (Target ÷ Actual) × 100%</li>
                                            <li>The lower the actual value compared to target, the better the performance</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Update Actual Value
                            </button>
                            <a href="{{ route('kPIEmployee.show', $kPIEmployee->employee->id) }}" class="btn btn-default btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .info-box {
            margin-bottom: 0;
        }
        .achievement-high {
            background-color: #d4edda !important;
            border-color: #c3e6cb !important;
            color: #155724 !important;
        }
        .achievement-medium {
            background-color: #fff3cd !important;
            border-color: #ffeaa7 !important;
            color: #856404 !important;
        }
        .achievement-low {
            background-color: #f8d7da !important;
            border-color: #f5c6cb !important;
            color: #721c24 !important;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            const target = {{ $kPIEmployee->target }};
            const method = '{{ $kPIEmployee->method_calculation }}';
            
            function calculateAchievement() {
                const actual = parseFloat($('#actual').val()) || 0;
                let achievement = 0;
                
                if (target > 0 && actual > 0) {
                    if (method === 'higher better') {
                        achievement = (actual / target) * 100;
                    } else { // lower better
                        achievement = (target / actual) * 100;
                    }
                }
                
                $('#achievement-preview').val(achievement.toFixed(2));
                
                // Update color based on achievement
                const input = $('#achievement-preview');
                input.removeClass('achievement-high achievement-medium achievement-low');
                
                if (achievement >= 100) {
                    input.addClass('achievement-high');
                } else if (achievement >= 75) {
                    input.addClass('achievement-medium');
                } else {
                    input.addClass('achievement-low');
                }
                
                // Add visual feedback
                if (achievement >= 100) {
                    input.css('color', '#155724');
                    input.css('font-weight', 'bold');
                } else if (achievement >= 75) {
                    input.css('color', '#856404');
                    input.css('font-weight', 'bold');
                } else {
                    input.css('color', '#721c24');
                    input.css('font-weight', 'bold');
                }
            }
            
            // Calculate on page load
            calculateAchievement();
            
            // Calculate on input change
            $('#actual').on('input', calculateAchievement);
            
            // Add real-time feedback
            $('#actual').on('input', function() {
                const value = parseFloat($(this).val()) || 0;
                const currentActual = {{ $kPIEmployee->actual }};
                
                if (value > currentActual) {
                    $(this).css('border-color', '#28a745');
                } else if (value < currentActual) {
                    $(this).css('border-color', '#dc3545');
                } else {
                    $(this).css('border-color', '#ced4da');
                }
            });
        });
    </script>
@stop