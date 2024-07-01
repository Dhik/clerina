@extends('adminlte::page')

@section('title', trans('labels.funnel'))

@section('content_header')
    <h1>{{ trans('labels.recap') }} {{ trans('labels.funnel') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto">
                                    <input type="text" class="form-control monthYear" id="filterDates"
                                           placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#tofuTab" role="tab">
                                {{ trans('labels.tofu') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#mofuTab" role="tab">
                                {{ trans('labels.mofu') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#bofuTab" role="tab">
                                {{ trans('labels.bofu') }}
                            </a>
                        </li>
                    </ul>
                    <div class="mt-3"></div>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tofuTab">
                            @include('admin.funnel.recap.tofu')

                        </div>
                        <div class="tab-pane" id="mofuTab">
                            @include('admin.funnel.recap.mofu')
                        </div>
                        <div class="tab-pane" id="bofuTab">
                            @include('admin.funnel.recap.bofu')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        filterDate = $('#filterDates');
    </script>

    @include('admin.funnel.recap.script.scriptTofu')
    @include('admin.funnel.recap.script.scriptMofu')
    @include('admin.funnel.recap.script.scriptBofu')

    <script>
        filterDate.change(function () {
            tofuTable.draw();
            mofuTable.draw();
            bofuTable.draw();
        });

        $(function () {
            tofuTable.draw();
            mofuTable.draw();
            bofuTable.draw();
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection
