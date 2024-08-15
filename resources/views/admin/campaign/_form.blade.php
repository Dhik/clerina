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
            value="{{ old('title', $edit ? $campaign->title : '') }}"
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
    <label for="id_budget" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.budget') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <select
            id="id_budget"
            class="form-control @error('id_budget') is-invalid @enderror"
            name="id_budget"
            autofocus>
            <option value="">{{ trans('placeholder.select', ['field' => trans('labels.budget')]) }}</option>
            @foreach($budgets as $budget)
                <option value="{{ $budget->id }}" {{ old('id_budget', $edit ? $campaign->id_budget : '') == $budget->id ? 'selected' : '' }}>
                    {{ $budget->nama_budget }} (Rp. {{ number_format($budget->budget, 0, ',', '.') }})
                </option>
            @endforeach
        </select>

        @error('id_budget')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>


<div class="form-group row">
    <label for="title" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.period') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="period"
            type="text"
            class="form-control rangeDateNoLimit @error('period') is-invalid @enderror"
            name="period"
            value="{{ old('period', $edit ? $campaign->period : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.period')]) }}"
            autocomplete="title"
            autofocus>

        @error('period')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="description" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.description') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <textarea
            class="form-control  @error('description') is-invalid @enderror"
            id="description"
            name="description"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.description')]) }}"
            autofocus>{{ old('description', $edit ? $campaign->description : '') }}</textarea>

        @error('description')
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


