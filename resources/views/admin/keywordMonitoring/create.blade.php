@extends('adminlte::page')

@section('title', trans('labels.keyword_monitorings'))

@section('content_header')
    <h1>Create Keyword</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('keywordMonitoring.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="keyword">Keyword</label>
                        <input type="text" class="form-control" id="keyword" name="keyword">
                    </div>
                    <button type="submit" class="btn btn-primary">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
