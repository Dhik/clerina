@extends('adminlte::page')

@section('title', trans('labels.reset_password'))

@section('content_header')
    <h1>{{ trans('labels.reset_password') }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('users.updatePasswordReset', $user->id) }}">
                            @include('admin.user._form-reset-password')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
