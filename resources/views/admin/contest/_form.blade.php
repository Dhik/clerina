@csrf

<div class="form-group row">
    <label for="title" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.title') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="title"
            type="text"
            class="form-control @error('title') is-invalid @enderror"
            name="title"
            value="{{ old('title', $edit ? $contest->title : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.title')]) }}"
            autocomplete="title"
            autofocus>

        @error('title')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="budget" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.budget') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="budget"
            type="text"
            class="form-control money @error('budget') is-invalid @enderror"
            name="budget"
            value="{{ old('budget', $edit ? $contest->budget : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.budget')]) }}"
            autocomplete="budget"
            autofocus>

        @error('budget')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row mb-0">
    <div class="col-md-6 offset-md-3">
        <button type="submit" class="btn btn-primary">
            {{ $edit ? trans('buttons.update') : trans('buttons.save') }}
        </button>
    </div>
</div>
