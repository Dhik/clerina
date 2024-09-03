@csrf
<div class="container">
    <div class="row">
        <div class="col-md-6">
            @foreach(['employee_id', 'full_name', 'email', 'barcode', 'job_level', 'organization', 'job_position', 'status_employee', 'age', 'birth_place', 'citizen_id_address', 'residential_address', 'ptkp_status', 'employee_tax_status', 'tax_config', 'bank_name', 'bank_account', 'bank_account_holder', 'bpjs_ketenagakerjaan', 'bpjs_kesehatan'] as $field)
                <div class="form-group row">
                    <label for="{{ $field }}" class="col-md-4 col-form-label text-md-right">
                        {{ trans("labels.$field") }}<span class="required">*</span>
                    </label>
                    <div class="col-md-8">
                        <input
                            id="{{ $field }}"
                            type="text"
                            class="form-control @error($field) is-invalid @enderror"
                            name="{{ $field }}"
                            value="{{ old($field, $edit ? $employee->$field : '') }}"
                            placeholder="{{ trans('placeholder.input', ['field' => trans("labels.$field")]) }}"
                            autocomplete="{{ $field }}"
                            autofocus>
                        @error($field)
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            @endforeach

            @foreach(['join_date', 'resign_date', 'end_date', 'sign_date', 'birth_date'] as $field)
                <div class="form-group row">
                    <label for="{{ $field }}" class="col-md-4 col-form-label text-md-right">
                        {{ trans("labels.$field") }}<span class="required">*</span>
                    </label>
                    <div class="col-md-8">
                        <input
                            id="{{ $field }}"
                            type="date"
                            class="form-control @error($field) is-invalid @enderror"
                            name="{{ $field }}"
                            value="{{ old($field, $edit && $employee->$field ? (new \DateTime($employee->$field))->format('Y-m-d') : '') }}"
                            placeholder="{{ trans('placeholder.input', ['field' => trans("labels.$field")]) }}"
                            autocomplete="{{ $field }}"
                            autofocus>
                        @error($field)
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="col-md-6">
            @foreach(['nik_npwp_16_digit', 'phone', 'branch_name', 'parent_branch_name', 'religion', 'gender', 'marital_status', 'blood_type', 'nationality_code', 'currency', 'length_of_service', 'payment_schedule', 'approval_line', 'manager', 'grade', 'class', 'cost_center', 'cost_center_category', 'sbu', 'npwp_16_digit', 'passport', 'passport_expiration_date'] as $field)
                <div class="form-group row">
                    <label for="{{ $field }}" class="col-md-4 col-form-label text-md-right">
                        {{ trans("labels.$field") }}<span class="required">*</span>
                    </label>
                    <div class="col-md-8">
                        <input
                            id="{{ $field }}"
                            type="text"
                            class="form-control @error($field) is-invalid @enderror"
                            name="{{ $field }}"
                            value="{{ old($field, $edit ? $employee->$field : '') }}"
                            placeholder="{{ trans('placeholder.input', ['field' => trans("labels.$field")]) }}"
                            autocomplete="{{ $field }}"
                            autofocus>
                        @error($field)
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            @endforeach

            <div class="form-group row">
                <label for="profile_picture" class="col-md-4 col-form-label text-md-right">
                    {{ trans("labels.profile_picture") }}<span class="required">*</span>
                </label>
                <div class="col-md-8">
                    <input
                        id="profile_picture"
                        type="file"
                        class="form-control @error('profile_picture') is-invalid @enderror"
                        name="profile_picture"
                        accept="image/*">
                    @error('profile_picture')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>


            <div class="form-group row">
                <label for="kk" class="col-md-4 col-form-label text-md-right">
                    {{ trans("labels.kk") }}<span class="required">*</span>
                </label>
                <div class="col-md-8">
                    <input
                        id="kk"
                        type="file"
                        class="form-control @error('kk') is-invalid @enderror"
                        name="kk"
                        accept="application/pdf, image/*">
                    @error('kk')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="ktp" class="col-md-4 col-form-label text-md-right">
                    {{ trans("labels.ktp") }}<span class="required">*</span>
                </label>
                <div class="col-md-8">
                    <input
                        id="ktp"
                        type="file"
                        class="form-control @error('ktp') is-invalid @enderror"
                        name="ktp"
                        accept="application/pdf, image/*">
                    @error('ktp')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="ijazah" class="col-md-4 col-form-label text-md-right">
                    {{ trans("labels.ijazah") }}<span class="required">*</span>
                </label>
                <div class="col-md-8">
                    <input
                        id="ijazah"
                        type="file"
                        class="form-control @error('ijazah') is-invalid @enderror"
                        name="ijazah"
                        accept="application/pdf, image/*">
                    @error('ijazah')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="cv" class="col-md-4 col-form-label text-md-right">
                    {{ trans("labels.cv") }}<span class="required">*</span>
                </label>
                <div class="col-md-8">
                    <input
                        id="cv"
                        type="file"
                        class="form-control @error('cv') is-invalid @enderror"
                        name="cv"
                        accept="application/pdf, image/*">
                    @error('cv')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

        </div>
    </div>
</div>

@if(!$edit)
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group row">
                    <label for="password" class="col-md-4 col-form-label text-md-right">
                        {{ trans('labels.password') }}<span class="required">*</span>
                    </label>
                    <div class="col-md-8">
                        <input
                            id="password"
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            name="password"
                            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.password')]) }}"
                            autocomplete="name"
                            autofocus>
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label for="password_confirmation" class="col-md-4 col-form-label text-md-right">
                        {{ trans('labels.password_confirmation') }}<span class="required">*</span>
                    </label>
                    <div class="col-md-8">
                        <input
                            id="password_confirmation"
                            type="password"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            name="password_confirmation"
                            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.password_confirmation')]) }}"
                            autocomplete="name"
                            autofocus>
                        @error('password_confirmation')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="form-group row mb-0">
    <div class="col-md-6 offset-md-3">
        <button type="submit" class="btn btn-primary">
            {{ $edit ? trans('buttons.update') : trans('buttons.save') }}
        </button>
    </div>
</div>
