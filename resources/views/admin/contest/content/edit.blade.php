@extends('adminlte::page')

@section('title', trans('labels.content'))

@section('content_header')
    <h1>{{ trans('labels.edit') }} {{ trans('labels.content') }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('contestContent.update', $contestContent) }}">
                            @method('PUT')
                            @include('admin.contest.content._form', ['edit' => true])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
