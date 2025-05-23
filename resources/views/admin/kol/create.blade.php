@extends('adminlte::page')

@section('title', trans('labels.key_opinion_leader'))

@section('content_header')
    <h1>{{ trans('labels.add') }} {{ trans('labels.key_opinion_leader') }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('kol.store') }}">
                            @include('admin.kol._form',['edit' => false])
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
            $('#channel, #niche, #contentType, #skinConcern, #skinType, #picContact').select2({
                theme: 'bootstrap4',
                placeholder: '{{ trans('placeholder.select_channel') }}'
            });
        });
    </script>
@endsection
