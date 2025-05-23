@extends('adminlte::page')

@section('title', trans('labels.key_opinion_leader'))

@section('content_header')
    <h1>{{ trans('labels.edit') }} {{ trans('labels.key_opinion_leader') }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Edit KOL Information</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('kol.update', $keyOpinionLeader->id) }}">
                            @csrf
                            @method('put')
                            @include('admin.kol._form', ['edit' => true])
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
            // No Select2 initialization needed for this simple form
        });
    </script>
@endsection