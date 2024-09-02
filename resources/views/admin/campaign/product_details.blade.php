@extends('adminlte::page')

@section('title', $product->product . ' Details')

@section('content_header')
    <h1>{{ $product->product }} Details</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalViews }}</h3>
                    <p>Video Views</p>
                </div>
                <div class="icon">
                    <i class="ion ion-eye"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalLikes }}</h3>
                    <p>Likes</p>
                </div>
                <div class="icon">
                    <i class="ion ion-thumbsup"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalComments }}</h3>
                    <p>Comments</p>
                </div>
                <div class="icon">
                    <i class="ion ion-chatbubble"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalInfluencers }}</h3>
                    <p>Influencers</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person"></i>
                </div>
            </div>
        </div>
    </div>

    <h3>Top Engagements</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Username</th>
                <th>Views</th>
                <th>Likes</th>
                <th>Comments</th>
                <th>Engagement</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($statistics as $stat)
                <tr>
                    <td>{{ $stat->username }}</td>
                    <td>{{ $stat->view }}</td>
                    <td>{{ $stat->like }}</td>
                    <td>{{ $stat->comment }}</td>
                    <td>{{ $stat->engagement }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
