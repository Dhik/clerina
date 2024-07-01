<div class="row">
    <div class="col-md-4">
        <h5>{{ trans('labels.offer') }}</h5>

        <table class="table table-striped">
            <tbody>
            <tr>
                <th>{{ trans('labels.created_by') }}</th>
                <td>
                    @if(!empty($offer->createdBy))
                        <a href="{{ route('users.show', $offer->createdBy->id) }}" target="_blank">
                            {{ $offer->createdBy->name }}
                        </a>
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ trans('labels.username') }}</th>
                <td>
                    @if(!empty($offer->keyOpinionLeader))
                        <a href="{{ route('users.show', $offer->keyOpinionLeader->id) }}" target="_blank">
                            {{ $offer->keyOpinionLeader->username }}
                        </a>
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ trans('labels.campaign') }}</th>
                <td>
                    @if(!empty($offer->campaign))
                        <a href="{{ route('campaign.show', $offer->campaign->id) }}" target="_blank">
                            {{ $offer->campaign->title }}
                        </a>
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ trans('labels.slot_rate') }}</th>
                <td>{{ number_format($offer->rate, '0', ',', '.') }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.benefit') }}</th>
                <td>{{ $offer->benefit }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.negotiate') }}</th>
                <td>{{ $offer->negotiate }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.bank_name') }}</th>
                <td>{{ $offer->bank_name }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.bank_account') }}</th>
                <td>{{ $offer->bank_account }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.bank_account_name') }}</th>
                <td>{{ $offer->bank_account_name }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.nik') }}</th>
                <td>{{ $offer->nik }}</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="col-md-4">
        <h5>{{ trans('labels.acc_slot') }}</h5>

        <table class="table table-striped">
            <tbody>
            <tr>
                <th>{{ trans('labels.status') }}</th>
                <td>
                    @if($offer->status === \App\Domain\Campaign\Enums\OfferEnum::Approved)
                        <span class="badge bg-success">{{ ucfirst($offer->status) }}</span>
                    @elseif($offer->status === \App\Domain\Campaign\Enums\OfferEnum::Rejected)
                        <span class="badge bg-danger">{{ ucfirst($offer->status) }}</span>
                    @else
                        <span class="badge bg-warning">{{ ucfirst($offer->status) }}</span>
                    @endif
                </td>
            </tr>
            @if($offer->status !== \App\Domain\Campaign\Enums\OfferEnum::Pending)
                <tr>
                    <th>{{ trans('labels.acc_slot') }}</th>
                    <td>{{ number_format($offer->acc_slot, '0', ',', '.') }}</td>
                </tr>
                <tr>
                    <th>{{ trans('labels.updated_by') }}</th>
                    <td>
                        @if(!empty($offer->approvedBy))
                            <a href="{{ route('users.show', $offer->approvedBy->id) }}" target="_blank">
                                {{ $offer->approvedBy->name }}
                            </a>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ trans('labels.updated_at') }}</th>
                    <td>{{ $offer->approved_at }}</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    <div class="col-md-4">
        <h5>{{ trans('labels.review') }} {{ trans('labels.offering') }}</h5>

        <table class="table table-striped">
            <tbody>
            <tr>
                <th>{{ trans('labels.rate_total_slot') }}</th>
                <td>{{ number_format($offer->rate_total_slot, '0', ',', '.') }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.rate_final_slot') }}</th>
                <td>{{ number_format($offer->rate_final_slot, '0', ',', '.') }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.discount') }}</th>
                <td>{{ number_format($offer->discount, '0', ',', '.') }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.npwp') }}</th>
                <td>{{ $offer->npwp ? trans('labels.have') : trans('labels.dont_have') }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.pph') }}</th>
                <td>{{ number_format($offer->pph, '0', ',', '.') }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.final_amount') }}</th>
                <td>{{ number_format($offer->final_amount, '0', ',', '.') }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
