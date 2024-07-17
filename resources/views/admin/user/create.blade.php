@extends('adminlte::page')

@section('title', trans('labels.user'))

@section('content_header')
    <h1>{{ trans('labels.add') }} {{ trans('labels.user') }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('users.store') }}">
                            @include('admin.user._form_input',['edit' => false])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#roles').select2({
                theme: 'bootstrap4',
                placeholder: '{{ trans('placeholder.multiple') }}',
                allowClear: true
            });

            $('#tenants').select2({
                theme: 'bootstrap4',
                placeholder: '{{ trans('placeholder.multiple') }}',
                allowClear: true
            });
        });
    </script>
@endsection
