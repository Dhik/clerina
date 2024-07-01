@extends('adminlte::page')

@section('title', trans('labels.marketing_category'))

@section('content_header')
    <h1>{{ $marketingCategory->name }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <p><strong>{{ trans('labels.name') }} :</strong> {{ $marketingCategory->name }}</p>
                        <p><strong>{{ trans('labels.type') }}:</strong> {{ ucfirst($marketingCategory->type) }}</p>
                    </div>
                </div>
            </div>
        </div>
        @include('admin.marketingCategory.subCategory.subCategoryMarketing')
    </div>
@endsection

