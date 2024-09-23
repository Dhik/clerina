@extends('adminlte::page')

@section('title', 'Create Brief')

@section('content_header')
    <h1>Create Brief</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('brief.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="acc_date">Acc Date</label>
                        <input type="date" class="form-control" id="acc_date" name="acc_date" required>
                    </div>
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="brief">Brief</label>
                        <textarea class="form-control" id="brief" name="brief" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
