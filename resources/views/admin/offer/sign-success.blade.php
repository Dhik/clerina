@extends('adminlte::sign')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 offset-md-3 mt-5">
                <div class="card">
                    <h3>
                        {{ trans('messages.success_save', ['model' => 'Sign']) }}
                    </h3>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')

    <script type="text/javascript" src="/vendor/signature/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/vendor/signature/jquery.signature.js"></script>

    <link rel="stylesheet" type="text/css" href="/vendor/signature/jquery.signature.css">

    <style>
        .kbw-signature { width: 100%; height: 200px;}
        #sig canvas{
            width: 100% !important;
            height: auto;
        }
    </style>

    <script>
        let sig = $('#sig').signature({syncField: '#signature64', syncFormat: 'PNG'});
        $('#clear').click(function(e) {
            e.preventDefault();
            sig.signature('clear');
            $("#signature64").val('');
        });
    </script>
@endsection
