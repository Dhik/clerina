<!-- Simplified form with only the specified fields -->

<!-- Username -->
<div class="form-group row">
    <label for="username" class="col-md-4 col-form-label text-md-right">{{ trans('labels.username') }}</label>
    <div class="col-md-6">
        <input type="text" 
               class="form-control @error('username') is-invalid @enderror" 
               name="username" 
               id="username" 
               value="{{ old('username', $keyOpinionLeader->username ?? '') }}" 
               placeholder="{{ trans('placeholder.enter_username') }}"
               required>
        @error('username')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>

<!-- Phone Number -->
<div class="form-group row">
    <label for="phone_number" class="col-md-4 col-form-label text-md-right">{{ trans('labels.phone_number') }}</label>
    <div class="col-md-6">
        <input type="text" 
               class="form-control @error('phone_number') is-invalid @enderror" 
               name="phone_number" 
               id="phone_number" 
               value="{{ old('phone_number', $keyOpinionLeader->phone_number ?? '') }}" 
               placeholder="{{ trans('placeholder.enter_phone_number') }}">
        @error('phone_number')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>



<!-- Views Last 9 Posts -->
<div class="form-group row">
    <label class="col-md-4 col-form-label text-md-right">Views Last 10 Posts</label>
    <div class="col-md-6">
        <div class="form-check form-check-inline">
            <input class="form-check-input" 
                   type="radio" 
                   name="views_last_9_post" 
                   id="views_last_9_post_yes" 
                   value="1" 
                   {{ old('views_last_9_post', $keyOpinionLeader->views_last_9_post ?? '') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="views_last_9_post_yes">
                {{ trans('labels.yes') }}
            </label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" 
                   type="radio" 
                   name="views_last_9_post" 
                   id="views_last_9_post_no" 
                   value="0" 
                   {{ old('views_last_9_post', $keyOpinionLeader->views_last_9_post ?? '') == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="views_last_9_post_no">
                {{ trans('labels.no') }}
            </label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" 
                   type="radio" 
                   name="views_last_9_post" 
                   id="views_last_9_post_null" 
                   value="" 
                   {{ old('views_last_9_post', $keyOpinionLeader->views_last_9_post ?? '') === '' ? 'checked' : '' }}>
            <label class="form-check-label" for="views_last_9_post_null">
                Not Set
            </label>
        </div>
        @error('views_last_9_post')
        <span class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>

<!-- Activity Posting -->
<div class="form-group row">
    <label class="col-md-4 col-form-label text-md-right">Activity Posting</label>
    <div class="col-md-6">
        <div class="form-check form-check-inline">
            <input class="form-check-input" 
                   type="radio" 
                   name="activity_posting" 
                   id="activity_posting_active" 
                   value="1" 
                   {{ old('activity_posting', $keyOpinionLeader->activity_posting ?? '') == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="activity_posting_active">
                Active
            </label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" 
                   type="radio" 
                   name="activity_posting" 
                   id="activity_posting_inactive" 
                   value="0" 
                   {{ old('activity_posting', $keyOpinionLeader->activity_posting ?? '') == '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="activity_posting_inactive">
                Inactive
            </label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" 
                   type="radio" 
                   name="activity_posting" 
                   id="activity_posting_null" 
                   value="" 
                   {{ old('activity_posting', $keyOpinionLeader->activity_posting ?? '') === '' ? 'checked' : '' }}>
            <label class="form-check-label" for="activity_posting_null">
                Not Set
            </label>
        </div>
        @error('activity_posting')
        <span class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>


<!-- Add this section to _form.blade.php after the Phone Number field -->

<!-- Status Affiliate -->
<div class="form-group row">
    <label for="status_affiliate" class="col-md-4 col-form-label text-md-right">Affiliate Status</label>
    <div class="col-md-6">
        <select class="form-control @error('status_affiliate') is-invalid @enderror" 
                name="status_affiliate" 
                id="status_affiliate">
            <option value="" {{ old('status_affiliate', $keyOpinionLeader->status_affiliate ?? '') === '' ? 'selected' : '' }}>
                Not Set
            </option>
            <option value="Qualified" {{ old('status_affiliate', $keyOpinionLeader->status_affiliate ?? '') === 'Qualified' ? 'selected' : '' }}>
                Qualified
            </option>
            <option value="Waiting List" {{ old('status_affiliate', $keyOpinionLeader->status_affiliate ?? '') === 'Waiting List' ? 'selected' : '' }}>
                Waiting List
            </option>
            <option value="Not Qualified" {{ old('status_affiliate', $keyOpinionLeader->status_affiliate ?? '') === 'Not Qualified' ? 'selected' : '' }}>
                Not Qualified
            </option>
        </select>
        @error('status_affiliate')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
</div>



<!-- Submit Button -->
<div class="form-group row mb-0">
    <div class="col-md-6 offset-md-4">
        <button type="submit" class="btn btn-primary">
            {{ trans('labels.update') }}
        </button>
        <a href="{{ route('kol.index') }}" class="btn btn-secondary ml-2">
            Cancel
        </a>
    </div>
</div>