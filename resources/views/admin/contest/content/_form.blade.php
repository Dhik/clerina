@csrf

<div class="form-group row">
    <label for="url" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.link') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="link"
            type="text"
            class="form-control @error('link') is-invalid @enderror"
            name="link"
            value="{{ old('link', $edit ? $contestContent->link : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.link')]) }}"
            autocomplete="link"
            autofocus>

        @error('link')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="rate" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.rate_per_view') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="rate"
            type="text"
            class="form-control money @error('rate') is-invalid @enderror"
            name="rate"
            value="{{ old('rate', $edit ? $contestContent->rate : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.rate')]) }}"
            autocomplete="rate"
            autofocus>

        @error('rate')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<input type="hidden" value="{{ $edit ? $contestContent->contest_id : $contest->id }}" name="contest_id">

<div class="form-group row mb-0">
    <div class="col-md-6 offset-md-3">
        <button type="submit" class="btn btn-primary">
            {{ $edit ? trans('buttons.update') : trans('buttons.save') }}
        </button>
    </div>
</div>
