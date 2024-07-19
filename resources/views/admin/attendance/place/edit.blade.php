@extends('adminlte::page')

@section('title', isset($place) ? 'Edit Place' : 'Create Place')

@section('content_header')
    <h1>{{ isset($place) ? 'Edit Place' : 'Create Place' }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ isset($place) ? route('place.update', $place->id) : route('place.store') }}" method="POST">
                        @csrf
                        @if(isset($place))
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="place">Place</label>
                            <input type="text" name="place" id="place" class="form-control" value="{{ isset($place) ? $place->place : '' }}">
                        </div>

                        <div class="form-group">
                            <label for="employees">Assign Employees</label>
                            <select name="employees[]" id="employees" class="form-control" multiple>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ isset($place) && in_array($employee->id, $place->employees->pluck('id')->toArray()) ? 'selected' : '' }}>
                                        {{ $employee->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">Save</button>
                        <a href="{{ route('place.index') }}" class="btn btn-secondary">Cancel</a>
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
