@extends('adminlte::page')

@section('title', trans('labels.campaign'))

@section('content_header')
    <h1>{{ trans('labels.edit') }} {{ trans('labels.campaign') }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('campaign.update', $campaign->id) }}">
                            @method('PUT')
                            @include('admin.campaign._form', ['edit' => true])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
