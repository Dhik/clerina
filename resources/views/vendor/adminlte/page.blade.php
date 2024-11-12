@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@if(Auth::user())
    @section('content_top_nav_left')
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ Auth::user()->currentTenant->name ?? trans('messages.no_brand_assigned') }}
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                @if(Auth::user()->hasRole('superadmin'))
                    @foreach($brandList as $brand)
                        <a class="dropdown-item @if($brand->id === Auth::user()->current_tenant_id) active @endif" href={{ route('tenant.change', $brand->id) }}>
                            {{ $brand->name }}
                        </a>
                    @endforeach
                @else
                    @foreach(Auth::user()->tenants as $tenant)
                        <a class="dropdown-item @if($tenant->id === Auth::user()->current_tenant_id) active @endif" href={{ route('tenant.change', $tenant->id) }}>
                            {{ $tenant->name }}
                        </a>
                    @endforeach
                @endif
            </div>
        </li>
    @stop
@endif

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation --}}
        @if(config('adminlte.preloader.enabled', false))
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
