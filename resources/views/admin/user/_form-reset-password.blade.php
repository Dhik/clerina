@method('put')
@csrf

<div class="form-group row">
    <label for="password" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.password') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="password"
            type="password"
            class="form-control @error('password') is-invalid @enderror"
            name="password"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.password')]) }}"
            autocomplete="name"
            autofocus
        >

        @error('password')
        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="password_confirmation" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.password_confirmation') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="password_confirmation"
            type="password"
            class="form-control @error('password_confirmation') is-invalid @enderror"
            name="password_confirmation"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.password_confirmation')]) }}"
            autocomplete="name"
            autofocus
        >

        @error('password_confirmation')
        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
        @enderror
    </div>
</div>
<div class="form-group row mb-0">
    <div class="col-md-6 offset-md-3">
        <button type="submit" class="btn btn-primary">
            {{ trans('buttons.save') }}
        </button>
    </div>
</div>
