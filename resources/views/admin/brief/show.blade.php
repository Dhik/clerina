@extends('adminlte::page')

@section('title', 'View Brief')

@section('content_header')
    <h1>View Brief</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h3><strong>Acc Date:</strong> {{ \Carbon\Carbon::parse($brief->acc_date)->format('d-m-Y') }}</h3>
                <h3><strong>Title:</strong> {{ $brief->title }}</h3>
                <p><strong>Brief:</strong></p>
                <p>{{ $brief->brief }}</p>
                <a href="{{ route('brief.edit', $brief->id) }}" class="btn btn-success">Edit Brief</a>
                <form action="{{ route('brief.destroy', $brief->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Brief</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
