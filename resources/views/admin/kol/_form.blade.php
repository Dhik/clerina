@csrf
<div class="form-group row">
    <label for="channel" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.channel') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <select
            id="channel"
            name="channel"
            class="form-control @error('channel') is-invalid @enderror"
        >
            @foreach($channels as $channel)
                <option
                    value="{{ $channel }}" {{ (old('channel', $edit ? $keyOpinionLeader->channel : '') === $channel) ? 'selected' : '' }}
                >
                    {{ ucfirst($channel) }}
                </option>
            @endforeach
        </select>

        @error('channel')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="username" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.username') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="username"
            type="text"
            class="form-control @error('username') is-invalid @enderror"
            name="username"
            value="{{ old('username', $edit ? $keyOpinionLeader->username : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.username')]) }}"
            autocomplete="username"
            autofocus>

        @error('username')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="niche" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.niche') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <select
            id="niche"
            name="niche"
            class="form-control @error('niche') is-invalid @enderror"
        >
            @foreach($niches as $niche)
                <option
                    value="{{ $niche }}" {{ (old('niche', $edit ? $keyOpinionLeader->niche : '') === $niche) ? 'selected' : '' }}
                >
                    {{ ucfirst($niche) }}
                </option>
            @endforeach
        </select>

        @error('niche')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="average_view" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.average_view') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="average_view"
            type="text"
            class="form-control money @error('average_view') is-invalid @enderror"
            name="average_view"
            value="{{ old('average_view', $edit ? $keyOpinionLeader->average_view : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.average_view')]) }}"
            autocomplete="average_view"
            autofocus>

        @error('average_view')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="skinType" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.skin_type') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <select
            id="skinType"
            name="skin_type"
            class="form-control @error('skin_type') is-invalid @enderror"
        >
            @foreach($skinTypes as $skinType)
                <option
                    value="{{ $skinType }}" {{ (old('skin_type', $edit ? $keyOpinionLeader->skin_type : '') === $skinType) ? 'selected' : '' }}
                >
                    {{ ucfirst($skinType) }}
                </option>
            @endforeach
        </select>

        @error('skin_type')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="skinConcern" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.skin_concern') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <select
            id="skinConcern"
            name="skin_concern"
            class="form-control @error('skin_concern') is-invalid @enderror"
        >
            @foreach($skinConcerns as $skinConcern)
                <option
                    value="{{ $skinConcern }}" {{ (old('skin_concern', $edit ? $keyOpinionLeader->skin_concern : '') === $skinConcern) ? 'selected' : '' }}
                >
                    {{ ucfirst($skinConcern) }}
                </option>
            @endforeach
        </select>

        @error('skin_concern')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="contentType" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.content_type') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <select
            id="contentType"
            name="content_type"
            class="form-control @error('content_type') is-invalid @enderror"
        >
            @foreach($contentTypes as $contentType)
                <option
                    value="{{ $contentType }}" {{ (old('content_type', $edit ? $keyOpinionLeader->content_type : '') === $contentType) ? 'selected' : '' }}
                >
                    {{ ucfirst($contentType) }}
                </option>
            @endforeach
        </select>

        @error('content_type')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="rate" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.slot_rate') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="rate"
            type="text"
            class="form-control money @error('rate') is-invalid @enderror"
            name="rate"
            value="{{ old('rate', $edit ? $keyOpinionLeader->rate : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.slot_rate')]) }}"
            autocomplete="rate"
            autofocus>

        @error('rate')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="picContact" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.pic_contact') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <select
            id="picContact"
            name="pic_contact"
            class="form-control @error('pic_contact') is-invalid @enderror"
        >
            @foreach($marketingUsers as $marketingUser)
                <option
                    value="{{ $marketingUser->id }}" {{ (old('pic_contact', $edit ? $keyOpinionLeader->pic_contact : '') === $marketingUser->id) ? 'selected' : '' }}
                >
                    {{ ucfirst($marketingUser->name) }}
                </option>
            @endforeach
        </select>

        @error('pic_contact')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<hr>

<div class="form-group row">
    <label for="name" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.name') }}
    </label>

    <div class="col-md-6">
        <input
            id="name"
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            name="name"
            value="{{ old('name', $edit ? $keyOpinionLeader->name : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.name')]) }}"
            autocomplete="name"
            autofocus>

        @error('name')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="address" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.address') }}
    </label>

    <div class="col-md-6">
        <textarea
            class="form-control  @error('address') is-invalid @enderror"
            id="address"
            name="address"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.address')]) }}"
            autofocus>{{ old('address', $edit ? $keyOpinionLeader->address : '') }}</textarea>

        @error('address')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="phoneNumber" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.phone_number') }}
    </label>

    <div class="col-md-6">
        <input
            id="phoneNumber"
            type="text"
            class="form-control @error('phone_number') is-invalid @enderror"
            name="phone_number"
            value="{{ old('phone_number', $edit ? $keyOpinionLeader->phone_number : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.phone_number')]) }}"
            autocomplete="phone_number"
            autofocus>

        @error('phone_number')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="bankName" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.bank_name') }}
    </label>

    <div class="col-md-6">
        <input
            id="bankName"
            type="text"
            class="form-control @error('bank_name') is-invalid @enderror"
            name="bank_name"
            value="{{ old('bank_name', $edit ? $keyOpinionLeader->bank_name : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.bank_name')]) }}"
            autocomplete="bank_name"
            autofocus>

        @error('bank_name')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="bankAccount" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.bank_account') }}
    </label>

    <div class="col-md-6">
        <input
            id="bankAccount"
            type="text"
            class="form-control @error('bank_account') is-invalid @enderror"
            name="bank_account"
            value="{{ old('bank_account', $edit ? $keyOpinionLeader->bank_account : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.bank_account')]) }}"
            autocomplete="bank_account"
            autofocus>

        @error('bank_account')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="bankAccountNumber" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.bank_account_name') }}
    </label>

    <div class="col-md-6">
        <input
            id="bankAccountNumber"
            type="text"
            class="form-control @error('bank_account_number') is-invalid @enderror"
            name="bank_account_name"
            value="{{ old('bank_account_name', $edit ? $keyOpinionLeader->bank_account_name : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.bank_account_name')]) }}"
            autocomplete="bank_account_name"
            autofocus>

        @error('bank_account_name')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="npwp" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.npwp') }}
    </label>

    <div class="col-md-6 icheck-primary d-inline">
        <input
            type="checkbox"
            name="npwp"
            id="npwp"
            {{ old('npwp', $edit ? ($keyOpinionLeader->npwp ? 'checked' : null) : null) ? 'checked' : null }}
        >
        <label for="npwp">
        </label>
    </div>
</div>

<div class="form-group row">
    <label for="npwpNumber" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.npwp_number') }}
    </label>

    <div class="col-md-6">
        <input
            id="npwpNumber"
            type="text"
            class="form-control @error('npwp_number') is-invalid @enderror"
            name="npwp_number"
            value="{{ old('npwp_number', $edit ? $keyOpinionLeader->npwp_number : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.npwp_number')]) }}"
            autocomplete="npwp_number"
            autofocus>

        @error('npwp_number')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="nik" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.nik') }}
    </label>

    <div class="col-md-6">
        <input
            id="nik"
            type="text"
            class="form-control @error('nik') is-invalid @enderror"
            name="nik"
            value="{{ old('nik', $edit ? $keyOpinionLeader->nik : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.nik')]) }}"
            autocomplete="nik"
            autofocus>

        @error('nik')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="notes" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.notes') }}
    </label>

    <div class="col-md-6">
        <textarea
            class="form-control  @error('notes') is-invalid @enderror"
            id="notes"
            name="notes"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.notes')]) }}"
            autofocus>{{ old('notes', $edit ? $keyOpinionLeader->notes : '') }}</textarea>

        @error('notes')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="product_delivery" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.product_delivery') }}
    </label>

    <div class="col-md-6 icheck-primary d-inline">
        <input
            type="checkbox"
            name="product_delivery"
            id="product_delivery"
            {{ old('product_delivery', $edit ? ($keyOpinionLeader->product_delivery ? 'checked' : null) : null) ? 'checked' : null }}
        >
        <label for="product_delivery">
        </label>
    </div>
</div>

<div class="form-group row">
    <label for="product" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.product') }}
    </label>

    <div class="col-md-6">
        <textarea
            class="form-control  @error('product') is-invalid @enderror"
            id="product"
            name="product"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.product')]) }}"
            autofocus>{{ old('product', $edit ? $keyOpinionLeader->product : '') }}</textarea>

        @error('product')
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
