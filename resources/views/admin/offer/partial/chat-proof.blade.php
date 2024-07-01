<div class="row justify-content-center">
    <div class="col-md-12">
        @can('updateOffer', $offer)
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('offer.uploadChatProof', $offer->id) }}" method="POST" enctype="multipart/form-data" class="form-inline">
                        @csrf
                        <div class="form-group">
                            <input class="form-control-file" type="file" name="images[]" multiple required>
                        </div>
                        <button type="submit" class="btn btn-primary ml-2">{{ trans('labels.upload') }}</button>
                    </form>
                </div>
            </div>
        @endcan

        <div class="card-body">
            <div class="row">
                @foreach($offer->getMedia('chatProof') as $mediaItem)
                    <div class="col-md-4 mb-4">
                        <div class="card position-relative">
                            <a href="{{ route('offer.previewCharProof', ['mediaId' => $mediaItem->id, 'filename' => $mediaItem->file_name]) }}" target="_blank">
                                <img class="card-img-top img-fluid" src="{{ route('offer.previewCharProof', ['mediaId' => $mediaItem->id, 'filename' => $mediaItem->file_name]) }}" alt="">
                            </a>
                            @can('updateOffer', $offer)
                                <form action="{{ route('offer.deleteChatProof', ['media' => $mediaItem, 'offer' => $offer]) }}" method="POST" class="position-absolute top-0 end-0 p-2">
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
