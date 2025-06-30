@extends('adminlte::page')

@section('title', 'Create KPI Employee')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Create KPI Employee</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kPIEmployee.index') }}">KPI Employee</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New KPI</h3>
                </div>
                <form action="{{ route('kPIEmployee.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kpi">KPI <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kpi') is-invalid @enderror" 
                                           id="kpi" name="kpi" value="{{ old('kpi') }}" required>
                                    @error('kpi')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_id">Employees <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('employee_id') is-invalid @enderror" 
                                            id="employee_id" name="employee_id[]" multiple required>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->employee_id }}" 
                                                {{ in_array($employee->employee_id, old('employee_id', [])) ? 'selected' : '' }}>
                                                {{ $employee->full_name }} ({{ $employee->employee_id }}) - {{ $employee->organization }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department">Department <span class="text-danger">*</span></label>
                                    <select class="form-control @error('department') is-invalid @enderror" 
                                            id="department" name="department" required>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department }}" 
                                                {{ old('department') == $department ? 'selected' : '' }}>
                                                {{ $department }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position">Position <span class="text-danger">*</span></label>
                                    <select class="form-control @error('position') is-invalid @enderror" 
                                            id="position" name="position" required>
                                        <option value="">Select Position</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position }}" 
                                                {{ old('position') == $position ? 'selected' : '' }}>
                                                {{ $position }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('position')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="method_calculation">Method Calculation <span class="text-danger">*</span></label>
                                    <select class="form-control @error('method_calculation') is-invalid @enderror" 
                                            id="method_calculation" name="method_calculation" required>
                                        <option value="">Select Method</option>
                                        @foreach($methods as $method)
                                            <option value="{{ $method }}" 
                                                {{ old('method_calculation') == $method ? 'selected' : '' }}>
                                                {{ ucfirst($method) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('method_calculation')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="perspective">Perspective <span class="text-danger">*</span></label>
                                    <select class="form-control @error('perspective') is-invalid @enderror" 
                                            id="perspective" name="perspective" required>
                                        <option value="">Select Perspective</option>
                                        @foreach($perspectives as $perspective)
                                            <option value="{{ $perspective }}" 
                                                {{ old('perspective') == $perspective ? 'selected' : '' }}>
                                                {{ $perspective }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('perspective')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="data_source">Data Source <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('data_source') is-invalid @enderror" 
                                           id="data_source" name="data_source" value="{{ old('data_source') }}" required>
                                    @error('data_source')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="target">Target <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('target') is-invalid @enderror" 
                                           id="target" name="target" value="{{ old('target') }}" required>
                                    @error('target')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bobot">Weight (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" max="100" class="form-control @error('bobot') is-invalid @enderror" 
                                           id="bobot" name="bobot" value="{{ old('bobot') }}" required>
                                    @error('bobot')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save KPI
                        </button>
                        <a href="{{ route('kPIEmployee.index') }}" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Select employees...',
                allowClear: true
            });
        });
    </script>
@stop