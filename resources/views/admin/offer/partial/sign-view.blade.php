<table class="table table-striped">
    <tbody>
    <tr>
        <th>{{ trans('labels.signed') }}</th>
        <td>{{ $offer->signed ? trans('labels.yes') : trans('labels.no') }}</td>
    </tr>
    @if($offer->signed)
        <tr>
            <th>{{ trans('labels.signed_at') }}</th>
            <td>{{ $offer->signed_at }}</td>
        </tr>
    @endif
    <tr>
        <th>{{ trans('labels.kol_sign') }}</th>
        <td>
            @if($offer->signed)
                <a href="{{ route('sign.preview', $offer->id) }}" target="_blank">
                    <img src="{{ route('sign.preview', $offer->id) }}" alt="">
                </a>
            @else
                <a href="{{ $offer->sign_url }}" class="btn btn-primary btn-sm" target="_blank" id="copyButton">
                    {{ trans('labels.copy_url') }}
                </a>
                <a href="{{ $offer->sign_url }}" class="btn btn-success btn-sm" target="_blank">
                    {{ trans('labels.open_page') }} {{ trans('labels.kol_sign') }}
                </a>
            @endif

        </td>
    </tr>
    </tbody>
</table>
