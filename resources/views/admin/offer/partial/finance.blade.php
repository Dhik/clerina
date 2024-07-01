@can('financeOffer', $offer)
    @if($offer->transfer_status === \App\Domain\Campaign\Enums\OfferEnum::Unpaid or is_null($offer->transfer_status))
        <div class="row">
            <div class="card">
                <button class="btn btn-info" id="btnFinanceOffer">
                    {{ trans("labels.update") }} {{ trans("labels.payment_proof") }}
                </button>
            </div>
        </div>
    @endif
@endcan

<div class="row">
    <div class="col-md-6">
        <table class="table table-striped">
            <tbody>
            <tr>
                <th>{{ trans('labels.transfer_status') }}</th>
                <td>
                    @if($offer->transfer_status === \App\Domain\Campaign\Enums\OfferEnum::Paid)
                        <span class="badge bg-success">{{ ucfirst($offer->transfer_status) }}</span>
                    @else
                        <span class="badge bg-danger">{{ ucfirst(\App\Domain\Campaign\Enums\OfferEnum::Unpaid) }}</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ trans('labels.transfer_date') }}</th>
                <td>{{ $offer->transfer_date ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ trans('labels.updated_by') }}</th>
                <td>
                    @if(!empty($offer->financedBy))
                        <a href="{{ route('users.show', $offer->financedBy->id) }}" target="_blank">
                            {{ $offer->financedBy->name ?? '-' }}
                        </a>
                    @endif
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="col-md-6">
        <div class="col-md-12">
            <div class="card">
                @can(\App\Domain\User\Enums\PermissionEnum::FinanceOffer)
                    <div class="card-body">
                        <h5>{{ trans('labels.upload') }} {{ trans('labels.transfer_proof') }}</h5>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('offer.uploadTransferProof', $offer->id) }}" method="POST" enctype="multipart/form-data" class="form-inline">
                            @csrf
                            <div class="form-group">
                                <input class="form-control-file" type="file" name="images[]" multiple required>
                            </div>
                            <button type="submit" class="btn btn-primary ml-2">{{ trans('labels.upload') }}</button>
                        </form>
                    </div>
                @endcan
            </div>

            <div class="card-body">
                <div class="row">
                    @foreach($offer->getMedia('transferProof') as $mediaItem)
                        <div class="col-md-4 mb-4">
                            <div class="card position-relative">
                                <a href="{{ route('offer.previewCharProof', ['mediaId' => $mediaItem->id, 'filename' => $mediaItem->file_name]) }}" target="_blank">
                                    <img class="card-img-top img-fluid" src="{{ route('offer.previewCharProof', ['mediaId' => $mediaItem->id, 'filename' => $mediaItem->file_name]) }}" alt="">
                                </a>
                                @can(\App\Domain\User\Enums\PermissionEnum::FinanceOffer)
                                    <form action="{{ route('offer.deleteTransferProof', ['media' => $mediaItem, 'offer' => $offer]) }}" method="POST" class="position-absolute top-0 end-0 p-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
