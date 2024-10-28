@extends('adminlte::page')

@section('title', trans('labels.key_opinion_leader'))

@section('content_header')
    <h1>Key Opinion Leader</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-1">
                <div style="width: 70px; height: 70px; border-radius: 50%; border: 1px solid #000;"></div>
            </div>
            <div class="col-md-5">
                <h3>{{ $keyOpinionLeader->username }}</h3>
            </div>
            <div class="col-md-2 text-center">  
                <h2>{{ $keyOpinionLeader->followers }}</h2>
                <p>Followers</p>
            </div>
            <div class="col-md-2 text-center">
                <h2>{{ $keyOpinionLeader->following }}</h2>
                <p>Following</p>
            </div>
            <div class="col-md-2 text-center">
                <h2>{{ $keyOpinionLeader->rate }}</h2>
                <p>Rate Card</p>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 text-right">
                <button type="button" id="refresh-followers-following" class="btn btn-success">
                <i class="fas fa-sync-alt"></i> Refresh Profile
                </button>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ trans('labels.recap') }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <tbody>
                            <tr>
                                <th>{{ trans('labels.cpm') }}</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.average_view') }}</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.slot_rate') }}</th>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ trans('labels.general_info') }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <tbody>
                            <tr>
                                <th>{{ trans('labels.channel') }}</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.username') }}</th>
                                <td>
                                    <a href="#" target="_blank">
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.niche') }}</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.skin_concern') }}</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.skin_type') }}</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.pic_contact') }}</th>
                                <td>
                                    <a href="#" target="_blank">
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.created_by') }}</th>
                                <td>
                                    <a href="#" target="_blank">
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-primary">{{ trans('buttons.edit') }}</a>
                        <button class="btn btn-danger delete-user">{{ trans('buttons.delete') }}</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ trans('labels.biodata') }}</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <tbody>
                                    <tr>
                                        <th>{{ trans('labels.name') }}</th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.address') }}</th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.phone_number') }}</th>
                                        <td>
                                            <a href="#" target="_blank">
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.npwp') }}</th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.npwp_number') }}</th>
                                        <td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.nik') }}</th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.notes') }}</th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.product_delivery') }}</th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.product') }}</th>
                                        <td></td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ trans('labels.bank_info') }}</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <tbody>
                                    <tr>
                                        <th>{{ trans('labels.bank_name') }}</th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.bank_account') }}</th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.bank_account_name') }}</th>
                                        <td></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
