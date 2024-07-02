@extends('adminlte::page')

@section('title', trans('labels.sales'))

@section('content_header')
    <h1>{{ $sales->date }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>{{ trans('labels.date') }}</th>
                                    <td>{{ $sales->date }}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('labels.visit') }}</th>
                                    <td>{{ number_format($sales->visit, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('labels.qty') }}</th>
                                    <td>{{ number_format($sales->qty, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('labels.order') }}</th>
                                    <td>{{ number_format($sales->order, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('labels.closing_rate') }}</th>
                                    <td>{{ number_format($sales->closing_rate, 0, ',', '.') }}%</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('labels.turnover') }}</th>
                                    <td>{{ number_format($sales->turnover, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('labels.ad_spent_social_media') }}</th>
                                    <td>{{ number_format($sales->ad_spent_social_media, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('labels.ad_spent_market_place') }}</th>
                                    <td>{{ number_format($sales->ad_spent_market_place, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('labels.ad_spent_total') }}</th>
                                    <td>{{ number_format($sales->ad_spent_total, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('labels.roas') }}</th>
                                    <td>{{ number_format($sales->roas, 0, ',', '.') }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        <a href="{{ route('sales.sales-sync', $sales) }}" class="btn btn-success">
                            <i class="fa fa-sync-alt"></i> {{ trans('buttons.update') }}
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>{{ trans('labels.visit') }}</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <th>{{ trans('labels.date') }}</th>
                                <th>{{ trans('labels.channel') }}</th>
                                <th>{{ trans('labels.amount') }}</th>
                            </thead>
                            <tbody>
                                @forelse($visits as $visit)
                                    <tr>
                                        <td>{{ $visit->date }}</td>
                                        <td>{{ $visit->salesChannel->name ?? '' }}</td>
                                        <td>{{ number_format($visit->visit_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">{{ trans('messages.no_data')  }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>{{ trans('labels.ad_spent_market_place') }}</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                            <th>{{ trans('labels.date') }}</th>
                            <th>{{ trans('labels.channel') }}</th>
                            <th>{{ trans('labels.amount') }}</th>
                            </thead>
                            <tbody>
                            @forelse($adSpentMarketPlaces as $adSpentMarketPlace)
                                <tr>
                                    <td>{{ $adSpentMarketPlace->date }}</td>
                                    <td>{{ $adSpentMarketPlace->salesChannel->name ?? '' }}</td>
                                    <td>{{ number_format($adSpentMarketPlace->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">{{ trans('messages.no_data')  }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>{{ trans('labels.ad_spent_social_media') }}</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                            <th>{{ trans('labels.date') }}</th>
                            <th>{{ trans('labels.channel') }}</th>
                            <th>{{ trans('labels.amount') }}</th>
                            </thead>
                            <tbody>
                            @forelse($adSpentSocialMedia as $socialMedia)
                                <tr>
                                    <td>{{ $socialMedia->date }}</td>
                                    <td>{{ $socialMedia->socialMedia->name ?? '' }}</td>
                                    <td>{{ number_format($socialMedia->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">{{ trans('messages.no_data')  }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
