@extends('adminlte::page')

@section('title', trans('labels.funnel'))

@section('content_header')
    <h1>{{ trans('labels.funnel') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-auto">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                        data-target="#tofuModal" id="btnTofuModal">
                                    <i class="fas fa-plus"></i> {{ trans('labels.tofu') }}
                                </button>
                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                        data-target="#mofuModal" id="btnMofuModal">
                                    <i class="fas fa-plus"></i> {{ trans('labels.mofu') }}
                                </button>
                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                        data-target="#bofuModal" id="btnBofuModal">
                                    <i class="fas fa-plus"></i> {{ trans('labels.bofu') }}
                                </button>
                                <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#adSpentSocialMediaModal" id="btnAddAdSpentSM">
                                    <i class="fas fa-plus"></i> {{ trans('labels.ad_spent_social_media') }}
                                </button> -->
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto">
                                    <input type="text" class="form-control monthYear" id="filterDates"
                                           placeholder="{{ trans('placeholder.select_date') }}" autocomplete="off">
                                </div>
                                <div class="col-auto">
                                    <select class="form-control" id="filterSocialMedia">
                                        @foreach($socialMedia as $media)
                                            <option value={{ $media->id }}>{{ $media->name }}</option>
                                        @endforeach
                                    </select>
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
                        <!-- <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#bofuTab" role="tab">
                                {{ trans('labels.remarket') }}
                            </a>
                        </li> -->
                    </ul>
                    <div class="mt-3"></div>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tofuTab">
                            @include('admin.funnel.input.tofu')
                        </div>
                        <div class="tab-pane" id="mofuTab">
                            @include('admin.funnel.input.mofu')
                        </div>
                        <div class="tab-pane" id="bofuTab">
                            @include('admin.funnel.input.bofu')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.funnel.input.modal.tofu')
    @include('admin.funnel.input.modal.mofu')
    @include('admin.funnel.input.modal.bofu')
@endsection

@section('js')
    <script>
        filterDate = $('#filterDates');
        filterSocialMedia = $('#filterSocialMedia');
    </script>

    @include('admin.funnel.input.script.scriptTofu')
    @include('admin.funnel.input.script.scriptMofu')
    @include('admin.funnel.input.script.scriptBofu')

    <script>
        filterDate.change(function () {
            console.log(filterDate.val())
            tofuTable.draw();
            mofuTable.draw();
            bofuTable.draw();
        });

        filterSocialMedia.change(function () {
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
