@extends('adminlte::page')

@section('title', trans('labels.key_opinion_leader'))

@section('content_header')
    <h1>{{ $keyOpinionLeader->username }}</h1>
@stop

@section('content')
    <div class="container-fluid">
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
                                <td>{{ number_format($keyOpinionLeader->cpm, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.average_view') }}</th>
                                <td>{{ number_format($keyOpinionLeader->average_view, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.slot_rate') }}</th>
                                <td>{{ number_format($keyOpinionLeader->rate, 0, ',', '.') }}</td>
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
                                <td>{{ ucfirst($keyOpinionLeader->channel) }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.username') }}</th>
                                <td>
                                    <a href="{{ $keyOpinionLeader->social_media_link }}" target="_blank">
                                        {{ $keyOpinionLeader->username }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.niche') }}</th>
                                <td>{{ ucfirst($keyOpinionLeader->niche) }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.skin_concern') }}</th>
                                <td>{{ ucfirst($keyOpinionLeader->skin_concern) }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.skin_type') }}</th>
                                <td>{{ ucfirst($keyOpinionLeader->skin_type) }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.pic_contact') }}</th>
                                <td>
                                    <a href="{{ route('users.show', $keyOpinionLeader->picContact->id) }}" target="_blank">
                                        {{ $keyOpinionLeader->picContact->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.created_by') }}</th>
                                <td>
                                    <a href="{{ route('users.show', $keyOpinionLeader->createdBy->id) }}" target="_blank">
                                        {{ $keyOpinionLeader->createdBy->name }}
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('kol.edit', $keyOpinionLeader->id) }}" class="btn btn-primary">{{ trans('buttons.edit') }}</a>
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
                                        <td>{{ $keyOpinionLeader->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.address') }}</th>
                                        <td>{{ $keyOpinionLeader->address }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.phone_number') }}</th>
                                        <td>
                                            <a href="{{ $keyOpinionLeader->wa_link }}" target="_blank">
                                                {{ $keyOpinionLeader->phone_number }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.npwp') }}</th>
                                        <td>{{ $keyOpinionLeader->product_delivery ? trans('labels.have') : trans('labels.dont_have') }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.npwp_number') }}</th>
                                        <td>
                                            {{ $keyOpinionLeader->npwp_number }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.nik') }}</th>
                                        <td>{{ $keyOpinionLeader->nik }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.notes') }}</th>
                                        <td>{{ $keyOpinionLeader->notes }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.product_delivery') }}</th>
                                        <td>{{ $keyOpinionLeader->product_delivery ? trans('labels.yes') : trans('labels.no') }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.product') }}</th>
                                        <td>{{ $keyOpinionLeader->product }}</td>
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
                                        <td>{{ $keyOpinionLeader->bank_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.bank_account') }}</th>
                                        <td>{{ $keyOpinionLeader->bank_account }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('labels.bank_account_name') }}</th>
                                        <td>{{ $keyOpinionLeader->bank_account_name }}</td>
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
