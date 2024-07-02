@extends('adminlte::page')

@section('title', isset($location) ? 'Edit Location' : 'Create Location')

@section('content_header')
    <h1>{{ isset($location) ? 'Edit Location' : 'Create Location' }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ isset($location) ? route('location.update', $location->id) : route('location.store') }}" method="POST">
                        @csrf
                        @if(isset($location))
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="setting_name">Setting Name</label>
                            <input type="text" name="setting_name" id="setting_name" class="form-control" value="{{ isset($location) ? $location->setting_name : '' }}">
                        </div>

                        <div class="form-group">
                            <label for="lat">Latitude</label>
                            <input type="text" name="lat" id="lat" class="form-control" value="{{ isset($location) ? $location->lat : '' }}">
                        </div>

                        <div class="form-group">
                            <label for="long">Longitude</label>
                            <input type="text" name="long" id="long" class="form-control" value="{{ isset($location) ? $location->long : '' }}">
                        </div>

                        <button type="submit" class="btn btn-success">Save</button>
                        <a href="{{ route('location.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
