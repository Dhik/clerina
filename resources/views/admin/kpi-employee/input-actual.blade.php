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
                                        <strong>Department:</strong> {{ $kPIEmployee->department }}<br>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Position:</strong> {{ $kPIEmployee->position }}<br>
                                        <strong>Target:</strong> {{ number_format($kPIEmployee->target, 2) }}<br>
                                        <strong>Current Actual:</strong> {{ number_format($kPIEmployee->actual, 2) }}<br>
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
                                    <input type="number" step="0.01" class="form-control @error('actual') is-invalid @enderror" 
                                           id="actual" name="actual" value="{{ old('actual', $kPIEmployee->actual) }}" required>
                                    @error('actual')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Current target: {{ number_format($kPIEmployee->target, 2) }}
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
                                        Calculation method: {{ $kPIEmployee->method_calculation }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Update Actual Value
                            </button>
                            <a href="{{ route('kPIEmployee.show', $kPIEmployee->employee->id) }}" class="btn btn-default">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            const target = {{ $kPIEmployee->target }};
            const method = '{{ $kPIEmployee->method_calculation }}';
            
            function calculateAchievement() {
                const actual = parseFloat($('#actual').val()) || 0;
                let achievement = 0;
                
                if (target > 0) {
                    if (method === 'higher better') {
                        achievement = (actual / target) * 100;
                    } else { // lower better
                        achievement = (target / actual) * 100;
                    }
                }
                
                $('#achievement-preview').val(achievement.toFixed(2));
                
                // Update color based on achievement
                const input = $('#achievement-preview');
                input.removeClass('text-success text-warning text-danger');
                
                if (achievement >= 100) {
                    input.addClass('text-success');
                } else if (achievement >= 75) {
                    input.addClass('text-warning');
                } else {
                    input.addClass('text-danger');
                }
            }
            
            // Calculate on page load
            calculateAchievement();
            
            // Calculate on input change
            $('#actual').on('input', calculateAchievement);
        });
    </script>
@stop