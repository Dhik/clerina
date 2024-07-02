@extends('adminlte::page')

@section('title', trans('labels.contest'))

@section('content_header')
    <h1>{{ trans('labels.add') }} {{ trans('labels.contest') }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('contest.store') }}">
                            @include('admin.contest._form',['edit' => false])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
