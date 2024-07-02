@extends('adminlte::page')

@section('title', trans('labels.contest'))

@section('content_header')
    <h1>{{ trans('labels.edit') }} {{ trans('labels.contest') }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('contest.update', $contest->id) }}">
                            @method('PUT')
                            @include('admin.contest._form', ['edit' => true])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
