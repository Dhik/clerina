<div class="modal fade" id="addContentAdsModal" tabindex="-1" role="dialog" aria-labelledby="addContentAdsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addContentAdsModalLabel">Add New Content Ads</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addContentAdsForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_link_ref">Link Reference</label>
                                <input type="text" class="form-control" name="link_ref" id="add_link_ref">
                            </div>
                            <div class="form-group">
                                <label for="add_product">Product <span class="text-danger">*</span></label>
                                <select class="form-control" name="product" id="add_product" required>
                                    <option value="">Select Product</option>
                                    @foreach($productOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="add_platform">Platform <span class="text-danger">*</span></label>
                                <select class="form-control" name="platform" id="add_platform" required>
                                    <option value="">Select Platform</option>
                                    @foreach($platformOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_funneling">Funneling</label>
                                <select class="form-control" name="funneling" id="add_funneling">
                                    <option value="">Select Funneling</option>
                                    @foreach($funnelingOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="add_request_date">Request Date</label>
                                <input type="date" class="form-control" name="request_date" id="add_request_date">
                            </div>
                            <div class="form-group">
                                <label for="add_assignee_id">Assignee</label>
                                <select class="form-control" name="assignee_id" id="add_assignee_id">
                                    <option value="">Select Assignee</option>
                                    {{-- This would be populated with users --}}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_desc_request">Description Request</label>
                        <textarea class="form-control" name="desc_request" id="add_desc_request" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Content Ads</button>
                </div>
            </form>
        </div>
    </div>
</div>