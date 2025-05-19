@extends('adminlte::page')

@section('title', 'Ads Monitor')

@section('content_header')
    <h1>Ads Monitor</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="monitorTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="shopee-mall-tab" data-toggle="tab" href="#shopee-mall-content" role="tab" aria-controls="shopee-mall-content" aria-selected="true">
                        <i class="fas fa-store"></i> Meta Ads
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="shopee-ads-tab" data-toggle="tab" href="#shopee-ads-content" role="tab" aria-controls="shopee-ads-content" aria-selected="false">
                        <i class="fas fa-shopping-bag"></i> Shopee Ads
                    </a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" id="shopee3-tab" data-toggle="tab" href="#shopee3-content" role="tab" aria-controls="shopee3-content" aria-selected="false">
                        <i class="fas fa-shopping-cart"></i> Shopee 3
                    </a>
                </li> -->
                <!-- Other tabs -->
                <li class="nav-item">
                    <a class="nav-link" id="tiktok-tab" data-toggle="tab" href="#tiktok-content" role="tab" aria-controls="tiktok-content" aria-selected="false">
                        <i class="fab fa-tiktok"></i> TikTok Ads
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="overall-tab" data-toggle="tab" href="#overall-content" role="tab" aria-controls="overall-content" aria-selected="false">
                        <i class="fas fa-chart-line"></i> Overall Performance
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="monitorTabsContent">
                <!-- Include each tab content -->
                @include('admin.adSpentSocialMedia.tabs.shopee_mall_tab')
                @include('admin.adSpentSocialMedia.tabs.shopee_tab')
                @include('admin.adSpentSocialMedia.tabs.tiktok_tab')
                @include('admin.adSpentSocialMedia.tabs.overall_tab')
            </div>
        </div>
    </div>
    
    <!-- Include modals -->
    @include('admin.adSpentSocialMedia.modals.shopee_mall_modal')
    @include('admin.adSpentSocialMedia.modals.shopee_ads_modal')
    @include('admin.adSpentSocialMedia.modals.details_modals')
    @include('admin.adSpentSocialMedia.modals.details_shopee_modal')
    @include('admin.adSpentSocialMedia.modals.details_tiktok_modals')
    @include('admin.adSpentSocialMedia.modals.tiktok_ads_modal')
@stop

@section('css')
    @include('admin.adSpentSocialMedia.css')
@stop

@section('js')
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.all.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    
    <!-- Include scripts -->
    @include('admin.adSpentSocialMedia.js.common')
    @include('admin.adSpentSocialMedia.js.shopee-mall-ads')
    @include('admin.adSpentSocialMedia.js.shopee-ads')
    @include('admin.adSpentSocialMedia.js.tiktok-ads')
    @include('admin.adSpentSocialMedia.js.overall-performance')
    
    @include('admin.adSpentMarketPlace.script-line-chart')
@stop