@extends('adminlte::page')

@section('title', isset($shift) ? 'Edit Shift' : 'Create Shift')

@section('content_header')
    <h1>{{ isset($shift) ? 'Edit Shift' : 'Create Shift' }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ isset($shift) ? route('shift.update', $shift->id) : route('shift.store') }}" method="POST">
                        @csrf
                        @if(isset($shift))
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="shift_name">Shift Name</label>
                            <input type="text" name="shift_name" id="shift_name" class="form-control" value="{{ isset($shift) ? $shift->shift_name : '' }}">
                        </div>

                        <div class="form-group">
                            <label for="schedule_in">Schedule In</label>
                            <input type="time" name="schedule_in" id="schedule_in" class="form-control" value="{{ isset($shift) ? $shift->schedule_in : '' }}">
                        </div>

                        <div class="form-group">
                            <label for="schedule_out">Schedule Out</label>
                            <input type="time" name="schedule_out" id="schedule_out" class="form-control" value="{{ isset($shift) ? $shift->schedule_out : '' }}">
                        </div>

                        <div class="form-group">
                            <label for="employees">Assign Employees</label>
                            <select name="employees[]" id="employees" class="form-control" multiple>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ isset($shift) && in_array($employee->id, $shift->employees->pluck('id')->toArray()) ? 'selected' : '' }}>
                                        {{ $employee->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">Save</button>
                        <a href="{{ route('shift.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#employees').select2();
        });
    </script>
@stop
