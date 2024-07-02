@extends('adminlte::page')

@section('title', trans('labels.keyword_monitorings'))

@section('content_header')
    <h1>Edit Keyword</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('keywordMonitoring.update', $keywordMonitoring->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="keyword">Keyword</label>
                        <input type="text" class="form-control" id="keyword" name="keyword" value="{{ $keywordMonitoring->keyword }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
