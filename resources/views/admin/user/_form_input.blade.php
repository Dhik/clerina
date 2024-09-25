@csrf
<div class="form-group row">
    <label for="name" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.name') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="name"
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            name="name"
            value="{{ old('name', $edit ? $user->name : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.name')]) }}"
            autocomplete="name"
            autofocus
        >

        @error('name')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="email" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.email') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="email"
            type="email"
            class="form-control @error('email') is-invalid @enderror"
            name="email"
            value="{{ old('email', $edit ? $user->email : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.email')]) }}"
            autocomplete="email">

        @error('email')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="phone_number" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.phone_number') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="phone_number"
            type="text"
            class="form-control @error('phone_number') is-invalid @enderror"
            name="phone_number"
            value="{{ old('phone_number', $edit ? $user->phone_number : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.phone_number')]) }}"
            autocomplete="name"
            autofocus>

        @error('phone_number')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="position" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.position') }}<span class="required">*</span>
    </label>

    <div class="col-md-6">
        <input
            id="position"
            type="text"
            class="form-control @error('position') is-invalid @enderror"
            name="position"
            value="{{ old('position', $edit ? $user->position : '') }}"
            placeholder="{{ trans('placeholder.input', ['field' => trans('labels.position')]) }}"
            autocomplete="position"
            autofocus>

        @error('position')
        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

@if(!$edit)
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
@endif

<div class="form-group row">
    <label for="roles" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.roles') }}<span class="required">*</span>
    </label>
    <div class="col-md-6">
        <div class="input-group">
            <select
                id="roles"
                name="roles[]"
                multiple="multiple"
                class="form-control select2-multiple select2-hidden-accessible @error('roles') is-invalid @enderror"
            >
                @foreach($roles as $role)
                    <option
                        value="{{ $role['id'] }}" {{ in_array($role['id'], old('roles', $edit ? $user->roles->pluck('name')->toArray() : [])) ? 'selected' : '' }}
                    >
                        {{ $role['label'] }}
                    </option>
                @endforeach
            </select>

            @error('roles')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group row">
    <label for="tenants" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.tenant') }}
    </label>
    <div class="col-md-6">
        <div class="input-group">
            <select
                id="tenants"
                name="tenants[]"
                multiple="multiple"
                class="form-control select2-multiple select2-hidden-accessible @error('tenants') is-invalid @enderror"
            >
                @foreach($tenants as $tenant)
                    <option
                        value="{{ $tenant['id'] }}" {{ in_array($tenant['id'], old('roles', $edit ? $user->tenants()->pluck('id')->toArray() : [])) ? 'selected' : '' }}
                    >
                        {{ $tenant['name'] }}
                    </option>
                @endforeach
            </select>

            @error('roles')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
    </div>
</div>

<!-- Employee Related Fields -->
<div class="form-group row">
    <label for="employee_id" class="col-md-3 col-form-label text-md-right">Employee ID<span class="required">*</span></label>
    <div class="col-md-6">
        <input 
        id="employee_id" 
        type="text" 
        class="form-control" 
        name="employee_id"
        placeholder="{{ trans('placeholder.input', ['field' => trans('labels.employee_id')]) }}" 
        value="{{ old('employee_id') }}"
        required>
    </div>
</div>

<div class="form-group row">
    <label for="job_level" class="col-md-3 col-form-label text-md-right">Job Level</label>
    <div class="col-md-6">
        <select id="job_level" class="form-control" name="job_level">
            <option value="-">-</option>
            <option value="CEO">CEO</option>
            <option value="Leader">Leader</option>
            <option value="Staff">Staff</option>
            <option value="Manager">Manager</option>
            <option value="Supervisor">Supervisor</option>
            <option value="Senior Staff">Senior Staff</option>
        </select>
    </div>
</div>

<div class="form-group row">
    <label for="organization" class="col-md-3 col-form-label text-md-right">Organization</label>
    <div class="col-md-6">
        <select id="organization" class="form-control" name="organization">
            <option value="-">-</option>    
            <option value="BOD">BOD</option>
            <option value="Creative">Creative</option>
            <option value="Marketing">Marketing</option>
            <option value="Operational">Operational</option>
            <option value="Finance">Finance</option>
            <option value="HR">HR</option>
            <option value="Personal Assistant">Personal Assistant</option>
            <option value="Product Development">Product Development</option>
            <option value="HRGA">HRGA</option>
            <option value="Digital Sales">Digital Sales</option>
            <option value="Supervisor">Supervisor</option>
            <option value="IT">IT</option>
        </select>
    </div>
</div>

<div class="form-group row">
    <label for="join_date" class="col-md-3 col-form-label text-md-right">Join Date<span class="required">*</span></label>
    <div class="col-md-6">
        <input id="join_date" type="date" class="form-control" name="join_date" value="{{ old('join_date') }}" required>
    </div>
</div>

<div class="form-group row">
    <label for="status_employee" class="col-md-3 col-form-label text-md-right">Status Employee</label>
    <div class="col-md-6">
        <select id="status_employee" class="form-control" name="status_employee">
            <option value="-">-</option>
            <option value="Permanent">Permanent</option>
            <option value="Contract">Contract</option>
            <option value="PKHL">PKHL</option>
            <option value="Internship">Internship</option>
            <option value="Probation">Probation</option>
            <option value="Active">Active</option>
        </select>
    </div>
</div>

<div class="form-group row">
    <label for="birth_date" class="col-md-3 col-form-label text-md-right">Birth Date<span class="required">*</span></label>
    <div class="col-md-6">
        <input 
        id="birth_date" 
        type="date" 
        class="form-control" 
        name="birth_date"
        value="{{ old('birth_date') }}"
        required>
    </div>
</div>

<div class="form-group row">
    <label for="birth_place" class="col-md-3 col-form-label text-md-right">Birth Place<span class="required">*</span></label>
    <div class="col-md-6">
        <input 
        id="birth_place" 
        type="text" 
        class="form-control"
        placeholder="{{ trans('placeholder.input', ['field' => trans('labels.birth_place')]) }}" 
        name="birth_place"
        required>
    </div>
</div>

<div class="form-group row">
    <label for="age" class="col-md-3 col-form-label text-md-right">Age<span class="required">*</span></label>
    <div class="col-md-6">
        <input 
        id="age" 
        type="number" 
        class="form-control" 
        name="age"
        placeholder="{{ trans('placeholder.input', ['field' => trans('labels.age')]) }}" 
        value="{{ old('age') }}"
        required>
    </div>
</div>

<div class="form-group row">
    <label for="citizen_id_address" class="col-md-3 col-form-label text-md-right">Citizen ID Address<span class="required">*</span></label>
    <div class="col-md-6">
        <input 
        id="citizen_id_address" 
        type="text" 
        class="form-control" 
        name="citizen_id_address"
        placeholder="{{ trans('placeholder.input', ['field' => trans('labels.citizen_id_address')]) }}" 
        value="{{ old('citizen_id_address') }}"
        required>
    </div>
</div>

<div class="form-group row">
    <label for="residential_address" class="col-md-3 col-form-label text-md-right">Residential Address<span class="required">*</span></label>
    <div class="col-md-6">
        <input 
        id="residential_address" 
        type="text" 
        class="form-control"
        placeholder="{{ trans('placeholder.input', ['field' => trans('labels.residential_address')]) }}" 
        name="residential_address"
        value="{{ old('residential_address') }}"
        required>
    </div>
</div>
<div class="form-group row">
    <label for="shift_id" class="col-md-3 col-form-label text-md-right">
        {{ trans('labels.shift') }}
    </label>
    <div class="col-md-6">
        <select id="shift_id" name="shift_id" class="form-control @error('shift_id') is-invalid @enderror">
            <option value="">{{ trans('placeholder.select', ['field' => trans('labels.shift')]) }}</option>
            @foreach($shifts as $shift)
                <option value="{{ $shift->id }}" {{ old('shift_id', $edit ? $user->shift_id : '') == $shift->id ? 'selected' : '' }}>
                    {{ $shift->shift_name }} ({{ $shift->schedule_in }} : {{ $shift->schedule_out }})
                </option>
            @endforeach
        </select>
        @error('shift_id')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>
<!-- End Employee Related Fields -->

<div class="form-group row mb-0">
    <div class="col-md-6 offset-md-3">
        <button type="submit" class="btn btn-primary">
            {{ $edit ? trans('buttons.update') : trans('buttons.save') }}
        </button>
    </div>
</div>
