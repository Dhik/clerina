@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body')
    <div class="wrapper">
        @yield('content')
    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
