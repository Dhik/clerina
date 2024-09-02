@extends('adminlte::page')

@section('title', $product->product . ' Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $product->product }} Details</h1>
        <div>
            <!-- Add any additional buttons or controls if needed -->
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="statistic">
                                <div class="row">
                                    <div class="col-lg-3 col-6">
                                        <div class="small-box bg-info">
                                            <div class="inner">
                                                <h4>{{ number_format($totalViews) }}</h4>
                                                <p>Video Views</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-eye"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-6">
                                        <div class="small-box bg-success">
                                            <div class="inner">
                                                <h4>{{ number_format($totalLikes) }}</h4>
                                                <p>Likes</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-thumbs-up"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-6">
                                        <div class="small-box bg-maroon">
                                            <div class="inner">
                                                <h4>{{ number_format($totalComments) }}</h4>
                                                <p>Comments</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-comment"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-6">
                                        <div class="small-box bg-purple">
                                            <div class="inner">
                                                <h4>{{ number_format($totalInfluencers) }}</h4>
                                                <p>Influencers</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-users"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                @include('admin.campaign.product.topStatistic')
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Add footer content here if needed -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop