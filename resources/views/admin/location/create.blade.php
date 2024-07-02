@extends('adminlte::page')

@section('title', 'Create Location')

@section('content_header')
    <h1>Create Location</h1>
@stop

@section('content')
    <form action="{{ route('locations.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="setting_name">Setting Name</label>
            <input type="text" class="form-control" id="setting_name" name="setting_name" required>
        </div>
        <div class="form-group">
            <label for="lat">Latitude</label>
            <input type="text" class="form-control" id="lat" name="lat" required>
        </div>
        <div class="form-group">
            <label for="long">Longitude</label>
            <input type="text" class="form-control" id="long" name="long" required>
        </div>
        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('locations.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@stop
